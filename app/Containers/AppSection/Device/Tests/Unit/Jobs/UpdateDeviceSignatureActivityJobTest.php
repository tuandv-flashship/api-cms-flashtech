<?php

namespace App\Containers\AppSection\Device\Tests\Unit\Jobs;

use App\Containers\AppSection\Device\Contracts\TouchDeviceSignatureActivity;
use App\Containers\AppSection\Device\Jobs\UpdateDeviceSignatureActivityJob;
use App\Containers\AppSection\Device\Tests\UnitTestCase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(UpdateDeviceSignatureActivityJob::class)]
final class UpdateDeviceSignatureActivityJobTest extends UnitTestCase
{
    public function testHandleRethrowsExceptionAndLogsRetryEvent(): void
    {
        config([
            'device.signature.activity_job_log_enabled' => true,
            'device.signature.activity_job_log_channel' => '',
        ]);
        Log::spy();

        $job = new UpdateDeviceSignatureActivityJob(
            deviceKeyId: 11,
            deviceId: 22,
            occurredAtUnix: time(),
        );

        $task = $this->mock(TouchDeviceSignatureActivity::class, function ($mock): void {
            $mock->shouldReceive('run')->once()->andThrow(new RuntimeException('boom'));
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('boom');

        try {
            $job->handle($task);
        } finally {
            Log::shouldHaveReceived('info')
                ->once()
                ->withArgs(function (string $message, array $context): bool {
                    return $message === 'device.signature.activity_job.failed_retry'
                        && ($context['device_key_id'] ?? null) === 11
                        && ($context['device_id'] ?? null) === 22
                        && ($context['exception'] ?? null) === RuntimeException::class;
                });
        }
    }

    public function testFailedLogsFinalEventWhenEnabled(): void
    {
        config([
            'device.signature.activity_job_log_enabled' => true,
            'device.signature.activity_job_log_channel' => '',
        ]);
        Log::spy();

        $job = new UpdateDeviceSignatureActivityJob(
            deviceKeyId: 33,
            deviceId: 44,
            occurredAtUnix: time(),
        );

        $job->failed(new RuntimeException('final'));

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'device.signature.activity_job.failed_final'
                    && ($context['device_key_id'] ?? null) === 33
                    && ($context['device_id'] ?? null) === 44
                    && ($context['exception'] ?? null) === RuntimeException::class;
            });
    }

    public function testBackoffParsesConfiguredString(): void
    {
        config([
            'device.signature.activity_job_backoff' => '0,2, ,10,-1',
        ]);

        $job = new UpdateDeviceSignatureActivityJob(
            deviceKeyId: 55,
            deviceId: 66,
            occurredAtUnix: time(),
        );

        $this->assertSame([2, 10], $job->backoff());
    }

    public function testUniqueIdAndUniqueForFollowDebounceConfig(): void
    {
        config([
            'device.signature.activity_touch_debounce_seconds' => 90,
        ]);

        $job = new UpdateDeviceSignatureActivityJob(
            deviceKeyId: 77,
            deviceId: 88,
            occurredAtUnix: time(),
        );

        $this->assertSame('device.signature.activity:77:88', $job->uniqueId());
        $this->assertSame(90, $job->uniqueFor);
    }
}
