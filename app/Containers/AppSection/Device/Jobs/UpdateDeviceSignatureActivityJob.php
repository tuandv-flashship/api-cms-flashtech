<?php

namespace App\Containers\AppSection\Device\Jobs;

use App\Containers\AppSection\Device\Contracts\TouchDeviceSignatureActivity;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class UpdateDeviceSignatureActivityJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;
    public int $maxExceptions = 1;
    public bool $failOnTimeout = true;
    public int $uniqueFor = 60;

    public function __construct(
        public readonly int $deviceKeyId,
        public readonly int $deviceId,
        public readonly int $occurredAtUnix,
    ) {
        $this->afterCommit();
        $this->tries = max(1, (int) config('device.signature.activity_job_tries', 3));
        $this->timeout = max(1, (int) config('device.signature.activity_job_timeout', 15));
        $this->maxExceptions = max(1, (int) config('device.signature.activity_job_max_exceptions', 1));
        $this->failOnTimeout = (bool) config('device.signature.activity_job_fail_on_timeout', true);
        $this->uniqueFor = max(1, (int) config('device.signature.activity_touch_debounce_seconds', 60));

        $connection = (string) config('device.signature.activity_queue_connection', '');
        if ($connection !== '') {
            $this->onConnection($connection);
        }

        $queue = (string) config('device.signature.activity_queue', 'security');
        if ($queue !== '') {
            $this->onQueue($queue);
        }
    }

    public function uniqueId(): string
    {
        return sprintf('device.signature.activity:%d:%d', $this->deviceKeyId, $this->deviceId);
    }

    public function handle(TouchDeviceSignatureActivity $touchDeviceSignatureActivityTask): void
    {
        try {
            $touchDeviceSignatureActivityTask->run(
                deviceKeyId: $this->deviceKeyId,
                deviceId: $this->deviceId,
                occurredAt: CarbonImmutable::createFromTimestamp($this->occurredAtUnix),
            );

            $this->log('processed');
        } catch (Throwable $throwable) {
            $this->log('failed_retry', [
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        $configured = config('device.signature.activity_job_backoff', '5,30,120');

        if (is_string($configured)) {
            $parts = array_map('trim', explode(',', $configured));
            $values = array_map('intval', $parts);
        } elseif (is_array($configured)) {
            $values = array_map('intval', $configured);
        } else {
            $values = [5, 30, 120];
        }

        $backoff = array_values(array_filter($values, static fn (int $seconds): bool => $seconds > 0));

        return $backoff !== [] ? $backoff : [5, 30, 120];
    }

    public function failed(Throwable $exception): void
    {
        $this->log('failed_final', [
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function log(string $event, array $context = []): void
    {
        if (!(bool) config('device.signature.activity_job_log_enabled', false)) {
            return;
        }

        $payload = [
            'device_key_id' => $this->deviceKeyId,
            'device_id' => $this->deviceId,
            'occurred_at_unix' => $this->occurredAtUnix,
            'attempt' => method_exists($this, 'attempts') ? $this->attempts() : 1,
            ...$context,
        ];

        $message = sprintf('device.signature.activity_job.%s', $event);
        $channel = (string) config('device.signature.activity_job_log_channel', '');

        if ($channel !== '') {
            Log::channel($channel)->info($message, $payload);

            return;
        }

        Log::info($message, $payload);
    }
}
