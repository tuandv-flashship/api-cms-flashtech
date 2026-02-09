<?php

namespace App\Containers\AppSection\Media\Services;

use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Media\Models\MediaFolder;
use App\Containers\AppSection\Media\Models\MediaSetting;
use App\Containers\AppSection\Media\Supports\MediaSettingsStore;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class MediaService
{
    private const USER_ITEMS_CACHE_PREFIX = 'media:user-items';

    public function __construct(
        private readonly MediaSettingsStore $settingsStore,
        private readonly ThumbnailService $thumbnailService,
    ) {
    }

    public function getDisk(): string
    {
        $settings = $this->settings();
        $driver = (string) $settings->get(
            'media_driver',
            config('media.driver', config('media.disk', 'public'))
        );

        $allowed = ['public', 'local', 's3', 'r2', 'do_spaces', 'wasabi', 'bunnycdn', 'backblaze'];
        if (! in_array($driver, $allowed, true)) {
            return (string) config('media.disk', 'public');
        }

        return $driver;
    }

    public function getPrivateDisk(): string
    {
        return (string) config('media.private_disk', 'local');
    }

    public function url(?string $path): string
    {
        if (! $path) {
            return '';
        }

        return Storage::disk($this->getDisk())->url($path);
    }

    public function getImageUrl(?string $path, ?string $size = null): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['data:image', 'http://', 'https://'])) {
            return $path;
        }

        if (! $size) {
            return $this->url($path);
        }

        $sizes = (array) config('media.sizes', []);
        if (! array_key_exists($size, $sizes)) {
            return $this->url($path);
        }

        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $thumbPath = str_replace(
            $fileName . '.' . $extension,
            $fileName . '-' . $sizes[$size] . '.' . $extension,
            $path
        );

        if (! Storage::disk($this->getDisk())->exists($thumbPath)) {
            return $this->url($path);
        }

        return $this->url($thumbPath);
    }

    public function getRealPath(string $path): string
    {
        return Storage::disk($this->getDisk())->path($path);
    }

    public function storeUploadedFile(
        UploadedFile $file,
        int $folderId,
        int $userId,
        ?string $visibility = null,
        ?string $accessMode = null
    ): MediaFile
    {
        $visibility = $visibility ?: 'public';
        $accessMode = $this->resolveAccessMode($visibility, $accessMode);
        $user = User::query()->find($userId);

        if (! $this->isAllowedFile($file, $user)) {
            throw new InvalidArgumentException('File type is not allowed.');
        }

        $folderPath = $this->getUploadFolderPath($folderId);
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin'));

        $name = MediaFile::createName($originalName, $folderId);
        $fileName = $this->createStorageFileName($name, $extension, $folderPath);

        $disk = $visibility === 'private' ? $this->getPrivateDisk() : $this->getDisk();
        $storagePath = $folderPath ? $folderPath . '/' . $fileName : $fileName;

        $mimeType = (string) ($file->getClientMimeType() ?: 'application/octet-stream');
        $size = (int) $file->getSize();

        $processed = $this->processImageUpload($file, $extension, $name, $folderPath);
        if ($processed) {
            $fileName = $processed['file_name'];
            $storagePath = $folderPath ? $folderPath . '/' . $fileName : $fileName;
            $mimeType = $processed['mime_type'];
            $size = $processed['size'];

            Storage::disk($disk)->put($storagePath, $processed['content'], [
                'visibility' => $visibility,
            ]);
        } else {
            Storage::disk($disk)->putFileAs($folderPath ?: '', $file, $fileName, [
                'visibility' => $visibility,
            ]);
        }

        $mediaFile = MediaFile::query()->create([
            'name' => $name,
            'mime_type' => $mimeType,
            'size' => $size,
            'url' => $storagePath,
            'options' => [
                'original_name' => $file->getClientOriginalName(),
            ],
            'folder_id' => $folderId,
            'user_id' => $userId,
            'visibility' => $visibility,
            'access_mode' => $accessMode,
        ]);

        $this->maybeApplyWatermark($mediaFile);
        $this->maybeGenerateThumbnails($mediaFile);

        return $mediaFile;
    }

    public function downloadFromUrl(
        string $url,
        int $folderId,
        int $userId,
        ?string $visibility = null,
        ?string $accessMode = null
    ): MediaFile
    {
        $visibility = $visibility ?: 'public';
        $accessMode = $this->resolveAccessMode($visibility, $accessMode);
        $response = Http::get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to download file.');
        }

        $basename = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_FILENAME);
        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
        $extension = $extension ? strtolower($extension) : 'bin';

        if (! in_array($extension, $this->getAllowedExtensions(), true)) {
            throw new InvalidArgumentException('File type is not allowed.');
        }

        $folderPath = $this->getUploadFolderPath($folderId);
        $name = MediaFile::createName($basename ?: 'file', $folderId);
        $fileName = $this->createStorageFileName($name, $extension, $folderPath);
        $storagePath = $folderPath ? $folderPath . '/' . $fileName : $fileName;

        $disk = $visibility === 'private' ? $this->getPrivateDisk() : $this->getDisk();
        $mimeType = $response->header('Content-Type') ?: 'application/octet-stream';
        $binary = $response->body();
        $size = strlen($binary);

        $processed = $this->processImageBinary($binary, $extension, $name, $folderPath, $mimeType);
        if ($processed) {
            $fileName = $processed['file_name'];
            $storagePath = $folderPath ? $folderPath . '/' . $fileName : $fileName;
            $mimeType = $processed['mime_type'];
            $size = $processed['size'];
            $binary = $processed['content'];
        }

        Storage::disk($disk)->put($storagePath, $binary, [
            'visibility' => $visibility,
        ]);

        $mediaFile = MediaFile::query()->create([
            'name' => $name,
            'mime_type' => $mimeType,
            'size' => $size,
            'url' => $storagePath,
            'options' => [
                'original_url' => $url,
            ],
            'folder_id' => $folderId,
            'user_id' => $userId,
            'visibility' => $visibility,
            'access_mode' => $accessMode,
        ]);

        $this->maybeApplyWatermark($mediaFile);
        $this->maybeGenerateThumbnails($mediaFile);

        return $mediaFile;
    }

    public function resolveAccessModeForFile(MediaFile $file): ?string
    {
        if ($file->visibility !== 'private') {
            return null;
        }

        return $this->normalizeAccessMode($file->access_mode) ?: $this->getDefaultPrivateAccessMode();
    }

    public function getSignedUrl(MediaFile $file): ?string
    {
        if ($file->visibility !== 'private') {
            return null;
        }

        if ($this->resolveAccessModeForFile($file) !== 'signed') {
            return null;
        }

        $ttlMinutes = $this->getSignedUrlTtlMinutes();
        $id = dechex((int) $file->getKey());
        $hash = sha1($id);

        return URL::temporarySignedRoute('media.indirect.url', now()->addMinutes($ttlMinutes), compact('hash', 'id'));
    }

    public function moveFile(MediaFile $file, int $newFolderId): MediaFile
    {
        $folderPath = $this->getUploadFolderPath($newFolderId);
        $newPath = $folderPath ? $folderPath . '/' . basename($file->url) : basename($file->url);

        $disk = $file->visibility === 'private' ? $this->getPrivateDisk() : $this->getDisk();
        Storage::disk($disk)->move($file->url, $newPath);
        $this->moveThumbnails($file->url, $newPath, $disk);

        $file->update([
            'folder_id' => $newFolderId,
            'url' => $newPath,
        ]);

        return $file;
    }

    public function renameFile(MediaFile $file, string $newName, bool $renameOnDisk = true): MediaFile
    {
        $file->name = MediaFile::createName($newName, $file->folder_id);

        if ($renameOnDisk) {
            $extension = pathinfo($file->url, PATHINFO_EXTENSION);
            $folderPath = $this->getUploadFolderPath($file->folder_id);
            $fileName = $this->createStorageFileName($file->name, $extension, $folderPath);
            $newPath = $folderPath ? $folderPath . '/' . $fileName : $fileName;

            $disk = $file->visibility === 'private' ? $this->getPrivateDisk() : $this->getDisk();
            Storage::disk($disk)->move($file->url, $newPath);
            $this->moveThumbnails($file->url, $newPath, $disk);
            $file->url = $newPath;
        }

        $file->save();

        return $file;
    }

    public function renameFolder(MediaFolder $folder, string $newName, bool $renameOnDisk = true): MediaFolder
    {
        $folder->name = MediaFolder::createName($newName, $folder->parent_id);

        if ($renameOnDisk) {
            $folderPath = $this->getFolderPath($folder->getKey());

            if ($folderPath && Storage::disk($this->getDisk())->directoryExists($folderPath)) {
                $newSlug = MediaFolder::createSlug($newName, $folder->parent_id);
                $newFolderPath = str_replace(basename($folderPath), $newSlug, $folderPath);

                Storage::disk($this->getDisk())->move($folderPath, $newFolderPath);

                $folder->slug = $newSlug;

                MediaFile::query()
                    ->where('url', 'like', $folderPath . '/%')
                    ->get()
                    ->each(function (MediaFile $file) use ($folderPath, $newFolderPath): void {
                        $file->url = str_replace($folderPath, $newFolderPath, $file->url);
                        $file->save();
                    });
            }
        }

        $folder->save();

        return $folder;
    }

    public function deleteFileFromStorage(MediaFile $file): void
    {
        $disk = $file->visibility === 'private' ? $this->getPrivateDisk() : $this->getDisk();
        Storage::disk($disk)->delete($file->url);
        $this->deleteThumbnails($file->url, $disk);
    }

    public function maybeGenerateThumbnails(MediaFile $file): void
    {
        if ($file->visibility !== 'public') {
            return;
        }

        if (! $file->canGenerateThumbnails()) {
            return;
        }

        $this->thumbnailService->generate($file);
    }

    private function maybeApplyWatermark(MediaFile $file): void
    {
        if (! $this->isGdEnabled()) {
            return;
        }

        $settings = $this->settings();

        if (! $settings->getBool('media_watermark_enabled', (bool) config('media.watermark.enabled', false))) {
            return;
        }

        if (! Str::startsWith($file->mime_type, 'image/')) {
            return;
        }

        $watermarkPath = (string) $settings->get('media_watermark_source', config('media.watermark.source'));
        if ($watermarkPath === '' || $watermarkPath === $file->url) {
            return;
        }

        $allowedFolders = $settings->getArray('media_folders_can_add_watermark', []);
        if ($allowedFolders !== [] && ! in_array($file->folder_id, $allowedFolders, true)) {
            return;
        }

        $disk = $file->visibility === 'private' ? $this->getPrivateDisk() : $this->getDisk();
        $imageContent = Storage::disk($disk)->exists($file->url)
            ? Storage::disk($disk)->get($file->url)
            : null;

        $watermarkContent = $this->resolveWatermarkContent($watermarkPath);

        if (! $imageContent || ! $watermarkContent) {
            return;
        }

        $image = $this->createImageFromString($imageContent);
        $watermark = $this->createImageFromString($watermarkContent);

        if (! $image || ! $watermark) {
            return;
        }

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        $sizePercent = $settings->getInt('media_watermark_size', (int) config('media.watermark.size', 10));
        $targetWidth = (int) round($imageWidth * ($sizePercent / 100));
        $targetWidth = max(1, min($targetWidth, $imageWidth));

        $watermark = $this->scaleImageToWidth($watermark, $targetWidth);
        if (! $watermark) {
            imagedestroy($image);
            return;
        }

        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        $position = (string) $settings->get('media_watermark_position', config('media.watermark.position', 'bottom-right'));
        $offsetX = $settings->getInt('media_watermark_position_x', (int) config('media.watermark.x', 10));
        $offsetY = $settings->getInt('media_watermark_position_y', (int) config('media.watermark.y', 10));
        $opacity = $settings->getInt('media_watermark_opacity', (int) config('media.watermark.opacity', 70));

        [$dstX, $dstY] = $this->resolveWatermarkPosition(
            $position,
            $imageWidth,
            $imageHeight,
            $watermarkWidth,
            $watermarkHeight,
            $offsetX,
            $offsetY
        );

        $this->mergeWatermark($image, $watermark, $dstX, $dstY, $opacity);

        $format = $this->normalizeImageFormat(pathinfo($file->url, PATHINFO_EXTENSION), $file->mime_type);
        $encoded = $this->encodeImage($image, $format, 90);
        imagedestroy($image);
        imagedestroy($watermark);

        if ($encoded === null) {
            return;
        }

        Storage::disk($disk)->put($file->url, $encoded, [
            'visibility' => $file->visibility,
        ]);

        $file->size = Storage::disk($disk)->size($file->url);
        $file->save();
    }

    public function cropImage(MediaFile $file, int $x, int $y, int $width, int $height): bool
    {
        $result = $this->thumbnailService->crop($file, $x, $y, $width, $height);

        if ($result) {
            $this->maybeGenerateThumbnails($file);
        }

        return $result;
    }

    public function getRecentItems(int $userId): array
    {
        return $this->getUserItemsFromCache('recent_items', $userId);
    }

    public function getFavoriteItems(int $userId): array
    {
        return $this->getUserItemsFromCache('favorites', $userId);
    }

    public function forgetUserItemsCache(int $userId): void
    {
        Cache::forget($this->getUserItemsCacheKey('recent_items', $userId));
        Cache::forget($this->getUserItemsCacheKey('favorites', $userId));
    }

    private function getUploadFolderPath(int $folderId): string
    {
        $base = trim((string) config('media.default_upload_folder', ''), '/');
        $folderPath = MediaFolder::getFullPath($folderId) ?: '';

        $path = trim($folderPath, '/');
        if ($base !== '') {
            $path = trim($base . '/' . $path, '/');
        }

        if ($this->isCloudDisk()) {
            $settings = $this->settings();
            $customPath = trim(
                (string) $settings->get('media_s3_path', config('media.custom_s3_path', '')),
                '/'
            );

            if ($customPath !== '') {
                $path = trim($customPath . '/' . $path, '/');
            }
        }

        return $path;
    }

    public function getFolderPath(int $folderId): string
    {
        return $this->getUploadFolderPath($folderId);
    }

    /**
     * @return array<int, string>
     */
    public function getThumbnailPaths(string $path): array
    {
        $sizes = (array) config('media.sizes', []);
        if ($sizes === []) {
            return [];
        }

        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $paths = [];
        foreach ($sizes as $size) {
            $paths[] = str_replace(
                $fileName . '.' . $extension,
                $fileName . '-' . $size . '.' . $extension,
                $path
            );
        }

        return $paths;
    }

    public function deleteThumbnails(string $path, ?string $disk = null): void
    {
        $disk = $disk ?: $this->getDisk();

        foreach ($this->getThumbnailPaths($path) as $thumb) {
            Storage::disk($disk)->delete($thumb);
        }
    }

    public function moveThumbnails(string $oldPath, string $newPath, ?string $disk = null): void
    {
        $disk = $disk ?: $this->getDisk();
        $oldThumbs = $this->getThumbnailPaths($oldPath);
        $newThumbs = $this->getThumbnailPaths($newPath);

        foreach ($oldThumbs as $index => $oldThumb) {
            $newThumb = $newThumbs[$index] ?? null;
            if (! $newThumb) {
                continue;
            }

            if (Storage::disk($disk)->exists($oldThumb)) {
                Storage::disk($disk)->move($oldThumb, $newThumb);
            }
        }
    }

    /**
     * @return array{content:string,file_name:string,mime_type:string,size:int}|null
     */
    private function processImageUpload(
        UploadedFile $file,
        string $extension,
        string $name,
        string $folderPath
    ): ?array {
        if (! $this->isGdEnabled()) {
            return null;
        }

        $mimeType = (string) ($file->getClientMimeType() ?: 'application/octet-stream');

        if (! Str::startsWith($mimeType, 'image/')) {
            return null;
        }

        $settings = $this->settings();
        $keepOriginal = $settings->getBool('media_keep_original_file_size_and_quality', false);
        $shouldConvertToWebp = $settings->getBool('media_convert_image_to_webp', false)
            && in_array($extension, ['jpg', 'jpeg', 'png'], true)
            && function_exists('imagewebp');

        $shouldResize = $settings->getBool('media_reduce_large_image_size', false)
            && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
            && ! $keepOriginal;

        if (! $shouldConvertToWebp && $keepOriginal && ! $shouldResize) {
            return null;
        }

        $binary = @file_get_contents($file->getRealPath());
        if (! is_string($binary)) {
            return null;
        }

        return $this->processImageBinary($binary, $extension, $name, $folderPath, $mimeType);
    }

    /**
     * @return array{content:string,file_name:string,mime_type:string,size:int}|null
     */
    private function processImageBinary(
        string $binary,
        string $extension,
        string $name,
        string $folderPath,
        string $mimeType
    ): ?array {
        if (! $this->isGdEnabled()) {
            return null;
        }

        $settings = $this->settings();
        $keepOriginal = $settings->getBool('media_keep_original_file_size_and_quality', false);
        $shouldConvertToWebp = $settings->getBool('media_convert_image_to_webp', false)
            && in_array($extension, ['jpg', 'jpeg', 'png'], true)
            && function_exists('imagewebp');

        $shouldResize = $settings->getBool('media_reduce_large_image_size', false)
            && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
            && ! $keepOriginal;

        if (! $shouldConvertToWebp && $keepOriginal && ! $shouldResize) {
            return null;
        }

        $image = $this->createImageFromString($binary);
        if (! $image) {
            return null;
        }

        if ($shouldResize) {
            $maxWidth = $settings->getInt('media_image_max_width', (int) config('media.image_max_width', 0));
            $maxHeight = $settings->getInt('media_image_max_height', (int) config('media.image_max_height', 0));
            $resized = $this->scaleDownImage($image, $maxWidth, $maxHeight);
            if ($resized !== $image) {
                imagedestroy($image);
                $image = $resized;
            }
        }

        $format = $this->normalizeImageFormat($extension, $mimeType);
        if ($shouldConvertToWebp) {
            $format = 'webp';
        }

        $quality = $keepOriginal ? 100 : 90;
        $content = $this->encodeImage($image, $format, $quality);
        imagedestroy($image);

        if ($content === null) {
            return null;
        }

        $newExtension = $format === 'jpeg' ? 'jpg' : $format;
        $fileName = $this->createStorageFileName($name, $newExtension, $folderPath);
        $newMimeType = match ($format) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return [
            'content' => $content,
            'file_name' => $fileName,
            'mime_type' => $newMimeType,
            'size' => strlen($content),
        ];
    }

    private function createImageFromString(string $binary): mixed
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        return @imagecreatefromstring($binary) ?: null;
    }

    private function scaleDownImage(mixed $image, int $maxWidth, int $maxHeight): mixed
    {
        if (! is_resource($image) && ! ($image instanceof \GdImage)) {
            return $image;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0) {
            return $image;
        }

        $maxWidth = $maxWidth > 0 ? $maxWidth : $width;
        $maxHeight = $maxHeight > 0 ? $maxHeight : $height;

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $canvas;
    }

    private function normalizeImageFormat(string $extension, string $mimeType): string
    {
        $extension = strtolower($extension);

        return match ($extension) {
            'jpg', 'jpeg' => 'jpeg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            default => Str::startsWith($mimeType, 'image/') ? 'jpeg' : 'jpeg',
        };
    }

    private function encodeImage(mixed $image, string $format, int $quality = 90): ?string
    {
        ob_start();

        $result = match ($format) {
            'jpeg' => imagejpeg($image, null, $quality),
            'png' => imagepng($image),
            'gif' => imagegif($image),
            'webp' => function_exists('imagewebp') ? imagewebp($image, null, $quality) : false,
            default => false,
        };

        $data = ob_get_clean();

        return $result && is_string($data) ? $data : null;
    }

    private function resolveWatermarkContent(string $path): ?string
    {
        $disk = $this->getDisk();

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->get($path);
        }

        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            return @file_get_contents($publicPath) ?: null;
        }

        $storagePath = storage_path('app/public/' . ltrim($path, '/'));
        if (file_exists($storagePath)) {
            return @file_get_contents($storagePath) ?: null;
        }

        return null;
    }

    private function scaleImageToWidth(mixed $image, int $targetWidth): mixed
    {
        if (! is_resource($image) && ! ($image instanceof \GdImage)) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0 || $targetWidth <= 0) {
            return $image;
        }

        if ($width === $targetWidth) {
            return $image;
        }

        $ratio = $targetWidth / $width;
        $newHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $newHeight, $transparent);

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $canvas;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolveWatermarkPosition(
        string $position,
        int $imageWidth,
        int $imageHeight,
        int $watermarkWidth,
        int $watermarkHeight,
        int $offsetX,
        int $offsetY
    ): array {
        $position = strtolower($position);

        $x = match ($position) {
            'top-right', 'bottom-right' => $imageWidth - $watermarkWidth - $offsetX,
            'center' => (int) round(($imageWidth - $watermarkWidth) / 2) + $offsetX,
            default => $offsetX,
        };

        $y = match ($position) {
            'bottom-left', 'bottom-right' => $imageHeight - $watermarkHeight - $offsetY,
            'center' => (int) round(($imageHeight - $watermarkHeight) / 2) + $offsetY,
            default => $offsetY,
        };

        return [max(0, $x), max(0, $y)];
    }

    private function mergeWatermark(mixed $image, mixed $watermark, int $x, int $y, int $opacity): void
    {
        $opacity = max(0, min($opacity, 100));
        $width = imagesx($watermark);
        $height = imagesy($watermark);

        if ($opacity >= 100) {
            imagecopy($image, $watermark, $x, $y, 0, 0, $width, $height);
            return;
        }

        $tmp = imagecreatetruecolor($width, $height);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
        imagefilledrectangle($tmp, 0, 0, $width, $height, $transparent);
        imagecopy($tmp, $watermark, 0, 0, 0, 0, $width, $height);

        imagecopymerge($image, $tmp, $x, $y, 0, 0, $width, $height, $opacity);
        imagedestroy($tmp);
    }

    private function isAllowedFile(UploadedFile $file, ?User $user): bool
    {
        if (! $file->isValid()) {
            return false;
        }

        if ($user && $user->isSuperAdmin() && config('media.allowed_admin_to_upload_any_file_types', false)) {
            return true;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = $this->getAllowedExtensions();

        if (! in_array($extension, $allowed, true)) {
            return false;
        }

        return $this->isAllowedMimeType($file);
    }

    private function getAllowedExtensions(): array
    {
        $allowed = config('media.allowed_mime_types', '');
        $allowed = array_filter(array_map('trim', explode(',', (string) $allowed)));

        return array_map('strtolower', $allowed);
    }

    private function createStorageFileName(string $name, string $extension, string $folderPath): string
    {
        $settings = $this->settings();

        if ($settings->getBool('media_convert_file_name_to_uuid', (bool) config('media.convert_file_name_to_uuid', false))) {
            return Str::uuid() . '.' . $extension;
        }

        $useOriginal = $settings->getBool(
            'media_use_original_name_for_file_path',
            (bool) config('media.use_original_name_for_file_path', false)
        );
        $turnOffLatin = $settings->getBool(
            'media_turn_off_automatic_url_translation_into_latin',
            (bool) config('media.turn_off_automatic_url_translation_into_latin', false)
        );

        $base = $useOriginal
            ? $name
            : Str::slug($name, '-', $turnOffLatin ? null : 'en');

        $slug = $base === '' ? (string) time() : $base;
        $index = 1;
        $candidate = $slug;

        $disk = $this->getDisk();
        while (Storage::disk($disk)->exists(trim($folderPath . '/' . $candidate . '.' . $extension, '/'))) {
            $candidate = $slug . '-' . $index++;
        }

        return $candidate . '.' . $extension;
    }

    private function isAllowedMimeType(UploadedFile $file): bool
    {
        $mimeType = (string) ($file->getMimeType() ?: $file->getClientMimeType() ?: '');
        $mimeType = strtolower($mimeType);

        if ($mimeType === '' || $mimeType === 'application/octet-stream') {
            return true;
        }

        $allowed = $this->getAllowedMimeTypes();

        return in_array($mimeType, $allowed, true);
    }

    /**
     * @return array<int, string>
     */
    private function getAllowedMimeTypes(): array
    {
        $allowed = [];
        foreach ((array) config('media.mime_types', []) as $types) {
            if (! is_array($types)) {
                continue;
            }

            foreach ($types as $type) {
                $allowed[] = strtolower((string) $type);
            }
        }

        $allowed[] = 'image/jpg';

        return array_values(array_unique($allowed));
    }

    private function isCloudDisk(?string $disk = null): bool
    {
        $disk = $disk ?: $this->getDisk();

        return in_array($disk, ['s3', 'r2', 'do_spaces', 'wasabi', 'bunnycdn', 'backblaze'], true);
    }

    private function settings(): MediaSettingsStore
    {
        return $this->settingsStore;
    }

    private function resolveAccessMode(?string $visibility, ?string $accessMode): ?string
    {
        if ($visibility !== 'private') {
            return null;
        }

        return $this->normalizeAccessMode($accessMode);
    }

    private function normalizeAccessMode(?string $accessMode): ?string
    {
        $accessMode = $accessMode ? strtolower($accessMode) : null;

        return in_array($accessMode, ['auth', 'signed'], true) ? $accessMode : null;
    }

    private function getDefaultPrivateAccessMode(): string
    {
        $configured = (string) config('media.private_access_mode', 'auth');
        $mode = $this->normalizeAccessMode($configured);

        return $mode ?: 'auth';
    }

    private function getSignedUrlTtlMinutes(): int
    {
        $ttl = (int) config('media.signed_url_ttl_minutes', 30);

        return $ttl > 0 ? $ttl : 30;
    }

    /**
     * @return array<int, mixed>
     */
    private function getUserItemsFromCache(string $key, int $userId): array
    {
        $cacheTtl = (int) config('media.cache.user_item_ttl_seconds', 300);
        $cacheKey = $this->getUserItemsCacheKey($key, $userId);

        $resolver = function () use ($key, $userId): array {
            $value = MediaSetting::query()
                ->where('key', $key)
                ->where('user_id', $userId)
                ->value('value');

            return is_array($value) ? $value : [];
        };

        if ($cacheTtl <= 0) {
            return Cache::rememberForever($cacheKey, $resolver);
        }

        return Cache::remember($cacheKey, now()->addSeconds($cacheTtl), $resolver);
    }

    private function getUserItemsCacheKey(string $key, int $userId): string
    {
        return sprintf('%s:%s:%d', self::USER_ITEMS_CACHE_PREFIX, $key, $userId);
    }

    private function isGdEnabled(): bool
    {
        $library = strtolower((string) $this->settings()->get(
            'media_image_processing_library',
            config('media.image_processing_library', 'gd')
        ));

        return $library === 'gd';
    }
}
