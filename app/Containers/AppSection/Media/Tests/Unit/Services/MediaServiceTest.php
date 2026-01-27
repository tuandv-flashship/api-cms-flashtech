<?php

namespace App\Containers\AppSection\Media\Tests\Unit\Services;

use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Containers\AppSection\Media\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MediaService::class)]
final class MediaServiceTest extends UnitTestCase
{
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
}
