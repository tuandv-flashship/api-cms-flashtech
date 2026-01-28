<?php

namespace App\Containers\AppSection\Revision\Models;

use App\Containers\AppSection\Revision\Supports\FieldFormatter;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Throwable;

final class Revision extends ParentModel
{
    protected $table = 'revisions';

    protected $fillable = [
        'revisionable_type',
        'revisionable_id',
        'user_id',
        'key',
        'old_value',
        'new_value',
    ];

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fieldName(): string
    {
        if ($formatted = $this->formatFieldName($this->key)) {
            return $formatted;
        }

        if (str_contains($this->key, '_id')) {
            return str_replace('_id', '', $this->key);
        }

        return $this->key;
    }

    public function oldValue(): ?string
    {
        return $this->getValue('old');
    }

    public function newValue(): ?string
    {
        return $this->getValue('new');
    }

    public function userResponsible(): ?Model
    {
        if (! $this->user_id) {
            return null;
        }

        if ($this->relationLoaded('user') && $this->user) {
            return $this->user;
        }

        $userModel = config('auth.model') ?: config('auth.providers.users.model');
        if (! $userModel || ! class_exists($userModel)) {
            return null;
        }

        return $userModel::find($this->user_id);
    }

    public function historyOf(): Model|false
    {
        if (class_exists($this->revisionable_type)) {
            return $this->revisionable_type::find($this->revisionable_id);
        }

        return false;
    }

    protected function formatFieldName(string $key): bool|string
    {
        if (! class_exists($this->revisionable_type)) {
            return false;
        }

        $relatedModel = new $this->revisionable_type();
        if (! method_exists($relatedModel, 'getRevisionFormattedFieldNames')) {
            return false;
        }

        $formatted = $relatedModel->getRevisionFormattedFieldNames();

        return $formatted[$key] ?? false;
    }

    protected function getValue(string $which = 'new'): ?string
    {
        $valueKey = $which . '_value';
        $mainModelClass = $this->revisionable_type;

        if (! class_exists($mainModelClass)) {
            return $this->format($this->key, $this->{$valueKey});
        }

        $mainModel = new $mainModelClass();

        try {
            if ($this->isRelated()) {
                $relatedModel = $this->getRelatedModel();
                if (! method_exists($mainModel, $relatedModel)) {
                    $relatedModel = Str::camel($relatedModel);
                }

                if (method_exists($mainModel, $relatedModel)) {
                    $relatedClass = $mainModel->{$relatedModel}()->getRelated();
                    $item = $relatedClass::find($this->{$valueKey});

                    if ($this->{$valueKey} === '') {
                        $emptyModel = $relatedClass::make();
                        $fallback = method_exists($emptyModel, 'getRevisionNullString')
                            ? $emptyModel->getRevisionNullString()
                            : 'nothing';

                        return $this->format($this->key, $fallback);
                    }

                    if (! $item) {
                        $emptyModel = $relatedClass::make();
                        $fallback = method_exists($emptyModel, 'getRevisionUnknownString')
                            ? $emptyModel->getRevisionUnknownString()
                            : 'unknown';

                        return $this->format($this->key, $fallback);
                    }

                    if (method_exists($item, 'identifiableName')) {
                        $mutator = 'get' . Str::studly($this->key) . 'Attribute';
                        if (method_exists($item, $mutator)) {
                            return $this->format($item->{$mutator}($this->key), $item->identifiableName());
                        }

                        return $this->format($this->key, $item->identifiableName());
                    }
                }
            }
        } catch (Throwable) {
            // Ignore formatting errors to avoid blocking the request.
        }

        $mutator = 'get' . Str::studly($this->key) . 'Attribute';
        if (method_exists($mainModel, $mutator)) {
            return $this->format($this->key, $mainModel->{$mutator}($this->{$valueKey}));
        }

        return $this->format($this->key, $this->{$valueKey});
    }

    protected function isRelated(): bool
    {
        $suffix = '_id';
        $pos = strrpos($this->key, $suffix);

        return $pos !== false && strlen($this->key) - strlen($suffix) === $pos;
    }

    protected function getRelatedModel(): string
    {
        $suffix = '_id';

        return substr($this->key, 0, strlen($this->key) - strlen($suffix));
    }

    public function format(string $key, ?string $value): ?string
    {
        if (! class_exists($this->revisionable_type)) {
            return $value;
        }

        $relatedModel = new $this->revisionable_type();
        if (! method_exists($relatedModel, 'getRevisionFormattedFields')) {
            return $value;
        }

        $formats = $relatedModel->getRevisionFormattedFields();

        if (is_array($formats) && isset($formats[$key])) {
            return FieldFormatter::format($key, $value, $formats);
        }

        return $value;
    }
}
