<?php

namespace App\Containers\AppSection\Media\Actions;

use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Media\Models\MediaFolder;
use App\Containers\AppSection\Media\Models\MediaSetting;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class MediaGlobalActionAction extends ParentAction
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function run(string $action, array $payload, int $userId): array
    {
        return match ($action) {
            'trash' => $this->handleTrash($payload),
            'restore' => $this->handleRestore($payload),
            'move' => $this->handleMove($payload),
            'make_copy' => $this->handleCopy($payload, $userId),
            'delete' => $this->handleDelete($payload),
            'favorite' => $this->handleFavorite($payload, $userId),
            'remove_favorite' => $this->handleRemoveFavorite($payload, $userId),
            'add_recent' => $this->handleAddRecent($payload, $userId),
            'crop' => $this->handleCrop($payload),
            'rename' => $this->handleRename($payload),
            'alt_text' => $this->handleAltText($payload),
            'empty_trash' => $this->handleEmptyTrash(),
            'properties' => $this->handleProperties($payload),
            default => [
                'message' => 'Invalid action.',
            ],
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleTrash(array $payload): array
    {
        $skipTrash = (bool) ($payload['skip_trash'] ?? false);

        foreach ((array) ($payload['selected'] ?? []) as $item) {
            $id = (int) ($item['id'] ?? 0);
            if (! $id) {
                continue;
            }

            if (! filter_var($item['is_folder'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $file = MediaFile::withTrashed()->find($id);
                if (! $file) {
                    continue;
                }

                $skipTrash ? $file->forceDelete() : $file->delete();
                continue;
            }

            $this->deleteFolderRecursive($id, $skipTrash);
        }

        return ['message' => 'Moved to trash successfully.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleRestore(array $payload): array
    {
        foreach ((array) ($payload['selected'] ?? []) as $item) {
            $id = (int) ($item['id'] ?? 0);
            if (! $id) {
                continue;
            }

            if (! filter_var($item['is_folder'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                MediaFile::onlyTrashed()->where('id', $id)->restore();
                continue;
            }

            $this->restoreFolderRecursive($id);
        }

        return ['message' => 'Restored successfully.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleMove(array $payload): array
    {
        $destination = (int) ($payload['destination'] ?? 0);

        foreach ((array) ($payload['selected'] ?? []) as $item) {
            $id = (int) ($item['id'] ?? 0);
            if (! $id) {
                continue;
            }

            if (! filter_var($item['is_folder'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $file = MediaFile::query()->find($id);
                if ($file) {
                    $this->mediaService->moveFile($file, $destination);
                }

                continue;
            }

            MediaFolder::query()->where('id', $id)->update(['parent_id' => $destination]);
        }

        return ['message' => 'Moved successfully.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleCopy(array $payload, int $userId): array
    {
        foreach ((array) ($payload['selected'] ?? []) as $item) {
            $id = (int) ($item['id'] ?? 0);
            if (! $id) {
                continue;
            }

            if (! filter_var($item['is_folder'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $file = MediaFile::query()->find($id);
                if ($file) {
                    $this->copyFile($file, null, $userId);
                }

                continue;
            }

            $folder = MediaFolder::query()->find($id);
            if ($folder) {
                $this->copyFolder($folder, (int) $folder->parent_id, $userId);
            }
        }

        return ['message' => 'Copied successfully.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleDelete(array $payload): array
    {
        foreach ((array) ($payload['selected'] ?? []) as $item) {
            $id = (int) ($item['id'] ?? 0);
            if (! $id) {
                continue;
            }

            if (! filter_var($item['is_folder'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                MediaFile::withTrashed()->where('id', $id)->forceDelete();
                continue;
            }

            $this->deleteFolderRecursive($id, true);
        }

        return ['message' => 'Deleted successfully.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleFavorite(array $payload, int $userId): array
    {
        $meta = MediaSetting::query()->firstOrCreate([
            'key' => 'favorites',
            'user_id' => $userId,
        ]);

        $current = is_array($meta->value) ? $meta->value : [];
        $selected = (array) ($payload['selected'] ?? []);

        $meta->value = array_values(array_merge($current, $selected));
        $meta->save();
        $this->mediaService->forgetUserItemsCache($userId);

        return ['message' => 'Added to favorites.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleRemoveFavorite(array $payload, int $userId): array
    {
        $meta = MediaSetting::query()->firstOrCreate([
            'key' => 'favorites',
            'user_id' => $userId,
        ]);

        $value = is_array($meta->value) ? $meta->value : [];
        $selected = (array) ($payload['selected'] ?? []);

        $meta->value = array_values(array_filter($value, function ($item) use ($selected) {
            foreach ($selected as $selectedItem) {
                if (
                    Arr::get($item, 'is_folder') == Arr::get($selectedItem, 'is_folder') &&
                    Arr::get($item, 'id') == Arr::get($selectedItem, 'id')
                ) {
                    return false;
                }
            }

            return true;
        }));

        $meta->save();
        $this->mediaService->forgetUserItemsCache($userId);

        return ['message' => 'Removed from favorites.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleAddRecent(array $payload, int $userId): array
    {
        $item = (array) ($payload['item'] ?? []);
        $itemId = (int) ($item['id'] ?? 0);

        if (! $itemId) {
            return ['message' => 'Invalid item.'];
        }

        $meta = MediaSetting::query()->firstOrCreate([
            'key' => 'recent_items',
            'user_id' => $userId,
        ]);

        $value = is_array($meta->value) ? $meta->value : [];
        $recentItem = [
            'id' => $itemId,
            'is_folder' => filter_var($item['is_folder'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        $value = array_values(array_filter($value, function ($existing) use ($recentItem) {
            return ! (
                Arr::get($existing, 'id') == $recentItem['id'] &&
                Arr::get($existing, 'is_folder') == $recentItem['is_folder']
            );
        }));

        array_unshift($value, $recentItem);
        $value = array_slice($value, 0, 20);

        $meta->value = $value;
        $meta->save();
        $this->mediaService->forgetUserItemsCache($userId);

        return ['message' => 'Added to recent.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleRename(array $payload): array
    {
        foreach ((array) ($payload['selected'] ?? []) as $item) {
            $id = (int) ($item['id'] ?? 0);
            $name = (string) ($item['name'] ?? '');
            if (! $id || $name === '') {
                continue;
            }

            $renameOnDisk = (bool) ($item['rename_physical_file'] ?? false);

            if (! filter_var($item['is_folder'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $file = MediaFile::query()->find($id);
                if ($file) {
                    $this->mediaService->renameFile($file, $name, $renameOnDisk);
                }

                continue;
            }

            $folder = MediaFolder::query()->find($id);
            if ($folder) {
                $this->mediaService->renameFolder($folder, $name, $renameOnDisk);
            }
        }

        return ['message' => 'Renamed successfully.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleAltText(array $payload): array
    {
        foreach ((array) ($payload['selected'] ?? []) as $item) {
            $id = (int) ($item['id'] ?? 0);
            if (! $id) {
                continue;
            }

            MediaFile::query()->where('id', $id)->update([
                'alt' => $item['alt'] ?? null,
            ]);
        }

        return ['message' => 'Alt text updated.'];
    }

    private function handleEmptyTrash(): array
    {
        MediaFile::onlyTrashed()->get()->each(fn (MediaFile $file) => $file->forceDelete());
        MediaFolder::onlyTrashed()->get()->each(fn (MediaFolder $folder) => $folder->forceDelete());

        return ['message' => 'Trash emptied.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleProperties(array $payload): array
    {
        $color = (string) ($payload['color'] ?? '');
        $selected = (array) ($payload['selected'] ?? []);

        if ($color === '') {
            return ['message' => 'Color is required.'];
        }

        $ids = array_map(static fn ($item) => (int) ($item['id'] ?? 0), $selected);
        $ids = array_filter($ids);

        MediaFolder::query()->whereIn('id', $ids)->update(['color' => $color]);

        return ['message' => 'Properties updated.'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleCrop(array $payload): array
    {
        $imageId = (int) ($payload['imageId'] ?? 0);
        if (! $imageId) {
            return ['message' => 'Invalid image.'];
        }

        $cropData = $payload['cropData'] ?? null;
        if (is_string($cropData)) {
            $cropData = json_decode($cropData, true);
        }

        if (! is_array($cropData)) {
            return ['message' => 'Invalid crop data.'];
        }

        $x = (int) ($cropData['x'] ?? 0);
        $y = (int) ($cropData['y'] ?? 0);
        $width = (int) ($cropData['width'] ?? 0);
        $height = (int) ($cropData['height'] ?? 0);

        if ($width <= 0 || $height <= 0) {
            return ['message' => 'Invalid crop dimensions.'];
        }

        $file = MediaFile::query()->find($imageId);
        if (! $file) {
            return ['message' => 'Image not found.'];
        }

        $result = $this->mediaService->cropImage($file, $x, $y, $width, $height);

        return $result
            ? ['message' => 'Cropped successfully.']
            : ['message' => 'Failed to crop image.'];
    }

    private function deleteFolderRecursive(int $folderId, bool $force): void
    {
        $children = MediaFolder::withTrashed()->where('parent_id', $folderId)->get();
        foreach ($children as $child) {
            $this->deleteFolderRecursive($child->getKey(), $force);
        }

        $folder = MediaFolder::withTrashed()->find($folderId);
        if (! $folder) {
            return;
        }

        $force ? $folder->forceDelete() : $folder->delete();
    }

    private function restoreFolderRecursive(int $folderId): void
    {
        $children = MediaFolder::withTrashed()->where('parent_id', $folderId)->get();
        foreach ($children as $child) {
            $this->restoreFolderRecursive($child->getKey());
        }

        MediaFolder::withTrashed()->where('id', $folderId)->restore();
    }

    private function copyFile(MediaFile $file, ?int $newFolderId, int $userId): MediaFile
    {
        $copy = $file->replicate();
        $copy->user_id = $userId;

        $disk = $file->visibility === 'private' ? $this->mediaService->getPrivateDisk() : $this->mediaService->getDisk();

        $targetFolderId = $newFolderId ?? (int) $file->folder_id;
        $copy->name = MediaFile::createName($file->name . '-(copy)', $targetFolderId);

        if ($newFolderId === null) {
            $newPath = $this->generateCopyPath($file->url, $disk);

            Storage::disk($disk)->copy($file->url, $newPath);
            $copy->url = $newPath;
            $this->copyThumbnails($file->url, $newPath, $disk);
        } else {
            $folderPath = $this->mediaService->getFolderPath($newFolderId);
            $fileName = basename($file->url);
            $newPath = $folderPath ? $folderPath . '/' . $fileName : $fileName;

            $newPath = $this->ensureUniquePath($newPath, $disk);
            if ($folderPath !== '' && ! Storage::disk($disk)->directoryExists($folderPath)) {
                Storage::disk($disk)->makeDirectory($folderPath);
            }
            Storage::disk($disk)->copy($file->url, $newPath);
            $copy->url = $newPath;
            $copy->folder_id = $newFolderId;
            $this->copyThumbnails($file->url, $newPath, $disk);
        }

        $copy->save();

        return $copy;
    }

    private function copyFolder(MediaFolder $folder, int $parentId, int $userId): MediaFolder
    {
        $newName = $folder->name . '-(copy)';
        $newFolder = MediaFolder::query()->create([
            'name' => MediaFolder::createName($newName, $parentId),
            'slug' => MediaFolder::createSlug($newName, $parentId),
            'parent_id' => $parentId,
            'user_id' => $userId,
            'color' => $folder->color,
        ]);

        $folder->files()->get()->each(function (MediaFile $file) use ($newFolder, $userId): void {
            $this->copyFile($file, $newFolder->getKey(), $userId);
        });

        MediaFolder::query()
            ->where('parent_id', $folder->getKey())
            ->get()
            ->each(function (MediaFolder $child) use ($newFolder, $userId): void {
                $this->copyFolder($child, $newFolder->getKey(), $userId);
            });

        return $newFolder;
    }

    private function copyThumbnails(string $oldPath, string $newPath, string $disk): void
    {
        $oldThumbs = $this->mediaService->getThumbnailPaths($oldPath);
        $newThumbs = $this->mediaService->getThumbnailPaths($newPath);

        foreach ($oldThumbs as $index => $oldThumb) {
            $newThumb = $newThumbs[$index] ?? null;
            if (! $newThumb) {
                continue;
            }

            if (Storage::disk($disk)->exists($oldThumb)) {
                Storage::disk($disk)->copy($oldThumb, $newThumb);
            }
        }
    }

    private function generateCopyPath(string $path, string $disk): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $folderPath = trim(dirname($path), '.');
        $baseName = pathinfo($path, PATHINFO_FILENAME);
        $candidate = $baseName . '-copy';
        $fileName = $candidate . '.' . $extension;
        $target = $folderPath ? $folderPath . '/' . $fileName : $fileName;

        return $this->ensureUniquePath($target, $disk);
    }

    private function ensureUniquePath(string $path, string $disk): string
    {
        if (! Storage::disk($disk)->exists($path)) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $folderPath = trim(dirname($path), '.');
        $baseName = pathinfo($path, PATHINFO_FILENAME);
        $index = 1;

        do {
            $candidate = $baseName . '-' . $index++;
            $fileName = $candidate . '.' . $extension;
            $newPath = $folderPath ? $folderPath . '/' . $fileName : $fileName;
        } while (Storage::disk($disk)->exists($newPath));

        return $newPath;
    }
}
