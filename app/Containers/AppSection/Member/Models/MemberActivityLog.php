<?php

namespace App\Containers\AppSection\Member\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberActivityLog extends ParentModel
{
    use MassPrunable;

    protected $fillable = [
        'member_id',
        'action',
        'user_agent',
        'reference_url',
        'reference_name',
        'ip_address',
    ];

    protected static function booted(): void
    {
        static::creating(function (MemberActivityLog $model): void {
            $request = request();

            $model->user_agent = $model->user_agent ?: ($request ? $request->userAgent() : null);
            $model->ip_address = $model->ip_address ?: ($request ? $request->ip() : null);
            $model->member_id = $model->member_id ?: auth('member')->id();

            if ($model->reference_url) {
                $baseUrl = rtrim((string) config('app.url'), '/');
                if ($baseUrl && str_starts_with($model->reference_url, $baseUrl)) {
                    $model->reference_url = substr($model->reference_url, strlen($baseUrl));
                }
            }
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function prunable(): Builder
    {
        $days = $this->getRetentionDays();

        if ($days === 0) {
            return $this->query()->where('id', '<', 0);
        }

        return $this->query()->where('created_at', '<', now()->subDays($days));
    }

    private function getRetentionDays(): int
    {
        $value = config('member.activity_log.retention_days', 30);

        if (is_numeric($value)) {
            return max(0, (int) $value);
        }

        return 30;
    }
}
