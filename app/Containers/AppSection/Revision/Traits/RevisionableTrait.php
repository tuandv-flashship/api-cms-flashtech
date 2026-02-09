<?php

namespace App\Containers\AppSection\Revision\Traits;

use App\Containers\AppSection\Revision\Models\Revision;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin \Illuminate\Database\Eloquent\Model|SoftDeletes
 */
trait RevisionableTrait
{
    protected array $originalData = [];
    protected array $updatedData = [];
    protected bool $updating = false;
    protected array $dontKeep = [];
    protected array $doKeep = [];
    protected array $dirtyData = [];
    protected array $revisionFormattedFields = [];
    protected array $revisionFormattedFieldNames = [];

    public static function bootRevisionableTrait(): void
    {
        static::saving(function ($model): void {
            $model->preSave();
        });

        static::saved(function ($model): void {
            $model->postSave();
        });

        static::created(function ($model): void {
            $model->postCreate();
        });

        static::deleted(function ($model): void {
            $model->preSave();
            $model->postDelete();
        });
    }

    public static function classRevisionHistory(int $limit = 100, string $order = 'desc')
    {
        return Revision::query()
            ->where('revisionable_type', get_called_class())
            ->orderBy('updated_at', $order)
            ->limit($limit)
            ->get();
    }

    public function preSave(): bool
    {
        if (! isset($this->revisionEnabled) || $this->revisionEnabled) {
            $this->originalData = $this->original;
            $this->updatedData = $this->attributes;

            foreach ($this->updatedData as $key => $value) {
                if (gettype($value) === 'object' && ! method_exists($value, '__toString')) {
                    unset($this->originalData[$key], $this->updatedData[$key]);
                    $this->dontKeep[] = $key;
                }
            }

            $this->dontKeep = isset($this->dontKeepRevisionOf)
                ? array_merge($this->dontKeepRevisionOf, $this->dontKeep)
                : $this->dontKeep;

            $this->doKeep = isset($this->keepRevisionOf)
                ? array_merge($this->keepRevisionOf, $this->doKeep)
                : $this->doKeep;

            unset($this->attributes['dontKeepRevisionOf'], $this->attributes['keepRevisionOf']);

            $this->dirtyData = $this->getDirty();
            $this->updating = $this->exists;
        }

        return true;
    }

    public function postSave(): void
    {
        $limitReached = isset($this->historyLimit) && $this->revisionHistory()->count() >= $this->historyLimit;
        $revisionCleanup = $this->revisionCleanup ?? false;

        if (((! isset($this->revisionEnabled) || $this->revisionEnabled) && $this->updating) && (! $limitReached || $revisionCleanup)) {
            $changesToRecord = $this->changedRevisionableFields();
            $revisions = [];

            foreach ($changesToRecord as $key => $change) {
                $revisions[] = [
                    'revisionable_type' => $this->getMorphClass(),
                    'revisionable_id' => $this->getKey(),
                    'key' => $key,
                    'old_value' => Arr::get($this->originalData, $key),
                    'new_value' => $this->updatedData[$key],
                    'user_id' => $this->getSystemUserId(),
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime(),
                ];
            }

            if ($revisions !== []) {
                if ($limitReached && $revisionCleanup) {
                    $toDelete = $this->revisionHistory()->orderBy('id')->limit(count($revisions))->get();
                    foreach ($toDelete as $delete) {
                        $delete->delete();
                    }
                }

                Revision::query()->insert($revisions);
                event('revisionable.saved', ['model' => $this, 'revisions' => $revisions]);
            }
        }
    }

    public function revisionHistory(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    protected function changedRevisionableFields(): array
    {
        $changesToRecord = [];

        foreach ($this->dirtyData as $key => $value) {
            if ($this->isRevisionable($key) && ! is_array($value)) {
                if (! isset($this->originalData[$key]) || $this->originalData[$key] != $this->updatedData[$key]) {
                    $changesToRecord[$key] = $value;
                }
            } else {
                unset($this->updatedData[$key], $this->originalData[$key]);
            }
        }

        return $changesToRecord;
    }

    protected function isRevisionable(string $key): bool
    {
        if (isset($this->doKeep) && in_array($key, $this->doKeep, true)) {
            return true;
        }

        if (isset($this->dontKeep) && in_array($key, $this->dontKeep, true)) {
            return false;
        }

        return empty($this->doKeep);
    }

    public function getSystemUserId(): int|string|null
    {
        try {
            if (Auth::guard()->check()) {
                return Auth::guard()->id();
            }

            if (Auth::guard('api')->check()) {
                return Auth::guard('api')->id();
            }
        } catch (Exception) {
            return null;
        }

        return null;
    }

    public function postCreate(): bool
    {
        if (empty($this->revisionCreationsEnabled)) {
            return false;
        }

        if (! isset($this->revisionEnabled) || $this->revisionEnabled) {
            $revisions[] = [
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id' => $this->getKey(),
                'key' => self::CREATED_AT,
                'old_value' => null,
                'new_value' => $this->{self::CREATED_AT},
                'user_id' => $this->getSystemUserId(),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ];

            Revision::query()->insert($revisions);
            event('revisionable.created', ['model' => $this, 'revisions' => $revisions]);
        }

        return false;
    }

    public function postDelete(): void
    {
        if (
            (! isset($this->revisionEnabled) || $this->revisionEnabled) &&
            $this->isSoftDelete() &&
            method_exists($this, 'getDeletedAtColumn') &&
            $this->isRevisionable($this->getDeletedAtColumn())
        ) {
            $revisions[] = [
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id' => $this->getKey(),
                'key' => $this->getDeletedAtColumn(),
                'old_value' => null,
                'new_value' => $this->{$this->getDeletedAtColumn()},
                'user_id' => $this->getSystemUserId(),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ];

            Revision::query()->insert($revisions);
            event('revisionable.deleted', ['model' => $this, 'revisions' => $revisions]);
        }
    }

    protected function isSoftDelete(): bool
    {
        if (isset($this->forceDeleting)) {
            return ! $this->forceDeleting;
        }

        if (isset($this->softDelete)) {
            return $this->softDelete;
        }

        return false;
    }

    public function getRevisionFormattedFields(): ?array
    {
        return $this->revisionFormattedFields;
    }

    public function getRevisionFormattedFieldNames(): ?array
    {
        return $this->revisionFormattedFieldNames;
    }

    public function identifiableName(): string
    {
        return (string) $this->getKey();
    }

    public function getRevisionNullString(): string
    {
        return $this->revisionNullString ?? 'nothing';
    }

    public function getRevisionUnknownString(): string
    {
        return $this->revisionUnknownString ?? 'unknown';
    }

    public function disableRevisionField($field): void
    {
        if (! isset($this->dontKeepRevisionOf)) {
            $this->dontKeepRevisionOf = [];
        }

        if (is_array($field)) {
            foreach ($field as $oneField) {
                $this->disableRevisionField($oneField);
            }

            return;
        }

        $dont = $this->dontKeepRevisionOf;
        $dont[] = $field;
        $this->dontKeepRevisionOf = $dont;
        unset($dont);
    }
}
