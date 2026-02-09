<?php

namespace App\Containers\AppSection\Media\Tests\Unit\Services;

use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Media\Models\MediaSetting;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Containers\AppSection\Media\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MediaService::class)]
final class MediaServiceTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function testGetSignedUrlForPrivateFile(): void
    {
        $user = User::factory()->superAdmin()->createOne();

        $file = MediaFile::query()->create([
            'user_id' => $user->getKey(),
            'name' => 'private-file',
            'mime_type' => 'text/plain',
            'size' => 10,
            'url' => 'private/test.txt',
            'visibility' => 'private',
            'access_mode' => 'signed',
        ]);

        $service = app(MediaService::class);
        $signedUrl = $service->getSignedUrl($file);

        $this->assertNotNull($signedUrl);
        $this->assertStringContainsString('signature=', $signedUrl);
    }

    public function testRecentAndFavoriteItemsUseCacheAndCanBeInvalidated(): void
    {
        $user = User::factory()->superAdmin()->createOne();

        $recent = MediaSetting::query()->create([
            'key' => 'recent_items',
            'user_id' => $user->getKey(),
            'value' => [['id' => 1, 'is_folder' => false]],
        ]);

        $favorites = MediaSetting::query()->create([
            'key' => 'favorites',
            'user_id' => $user->getKey(),
            'value' => [['id' => 10, 'is_folder' => true]],
        ]);

        $service = app(MediaService::class);

        $this->assertSame([['id' => 1, 'is_folder' => false]], $service->getRecentItems((int) $user->getKey()));
        $this->assertSame([['id' => 10, 'is_folder' => true]], $service->getFavoriteItems((int) $user->getKey()));

        $recent->update(['value' => [['id' => 2, 'is_folder' => false]]]);
        $favorites->update(['value' => [['id' => 20, 'is_folder' => false]]]);

        $this->assertSame([['id' => 1, 'is_folder' => false]], $service->getRecentItems((int) $user->getKey()));
        $this->assertSame([['id' => 10, 'is_folder' => true]], $service->getFavoriteItems((int) $user->getKey()));

        $service->forgetUserItemsCache((int) $user->getKey());

        $this->assertSame([['id' => 2, 'is_folder' => false]], $service->getRecentItems((int) $user->getKey()));
        $this->assertSame([['id' => 20, 'is_folder' => false]], $service->getFavoriteItems((int) $user->getKey()));
    }
}
