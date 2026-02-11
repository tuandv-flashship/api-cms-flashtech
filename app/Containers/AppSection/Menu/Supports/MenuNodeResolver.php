<?php

namespace App\Containers\AppSection\Menu\Supports;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

final class MenuNodeResolver
{
    private const EMPTY_REFERENCE_KEY = '__empty_reference__';

    /**
     * @return array{reference_type: string|null, title: string|null, url: string|null}
     */
    public function resolve(?string $referenceType, ?int $referenceId): array
    {
        $key = $this->buildKey($referenceType, $referenceId);
        if ($key === self::EMPTY_REFERENCE_KEY) {
            return [
                'reference_type' => null,
                'title' => null,
                'url' => null,
            ];
        }

        $resolved = $this->resolveMany([
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ],
        ]);

        return $resolved[$key] ?? [
            'reference_type' => $this->normalizeReferenceType($referenceType),
            'title' => null,
            'url' => null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $references
     * @return array<string, array{reference_type: string|null, title: string|null, url: string|null}>
     */
    public function resolveMany(array $references): array
    {
        $resolved = [];
        $groupedIdsByType = [];

        foreach ($references as $reference) {
            $type = isset($reference['reference_type']) && is_string($reference['reference_type'])
                ? $reference['reference_type']
                : null;
            $id = isset($reference['reference_id']) ? (int) $reference['reference_id'] : null;

            $normalizedType = $this->normalizeReferenceType($type);
            $normalizedId = $normalizedType !== null && $id !== null && $id > 0 ? $id : null;

            $key = $this->buildNormalizedKey($normalizedType, $normalizedId);
            if ($key === self::EMPTY_REFERENCE_KEY) {
                $resolved[$key] = [
                    'reference_type' => null,
                    'title' => null,
                    'url' => null,
                ];
                continue;
            }

            $groupedIdsByType[$normalizedType][] = $normalizedId;
            $resolved[$key] = [
                'reference_type' => $normalizedType,
                'title' => null,
                'url' => null,
            ];
        }

        foreach ($groupedIdsByType as $className => $ids) {
            if (! is_string($className) || ! class_exists($className)) {
                continue;
            }

            $idList = array_values(array_unique(array_map(static fn (mixed $id): int => (int) $id, $ids)));
            if ($idList === []) {
                continue;
            }

            $models = $this->findReferenceModels($className, $idList)->keyBy(static fn (Model $model): int => (int) $model->getKey());

            foreach ($idList as $id) {
                $key = $this->buildNormalizedKey($className, $id);
                /** @var Model|null $model */
                $model = $models->get($id);

                if ($model === null) {
                    continue;
                }

                $resolved[$key] = [
                    'reference_type' => $className,
                    'title' => $this->extractTitle($model),
                    'url' => $this->extractUrl($model),
                ];
            }
        }

        return $resolved;
    }

    public function normalizeReferenceType(?string $referenceType): ?string
    {
        if ($referenceType === null || $referenceType === '') {
            return null;
        }

        $types = (array) config('menu.reference_types', []);

        if (isset($types[$referenceType]) && is_string($types[$referenceType])) {
            return $types[$referenceType];
        }

        return in_array($referenceType, $types, true) ? $referenceType : null;
    }

    public function buildKey(?string $referenceType, ?int $referenceId): string
    {
        $normalizedType = $this->normalizeReferenceType($referenceType);
        $normalizedId = $normalizedType !== null && $referenceId !== null && $referenceId > 0
            ? $referenceId
            : null;

        return $this->buildNormalizedKey($normalizedType, $normalizedId);
    }

    /**
     * @param array<int, int> $ids
     * @return EloquentCollection<int, Model>
     */
    private function findReferenceModels(string $className, array $ids): EloquentCollection
    {
        if (! class_exists($className)) {
            return new EloquentCollection();
        }

        $query = $className::query();
        $query = LanguageAdvancedManager::applyTranslationsToQuery($query, $className);

        if (method_exists($className, 'slugable')) {
            $query->with('slugable');
        }

        return $query
            ->whereIn('id', $ids)
            ->get();
    }

    private function extractTitle(Model $model): ?string
    {
        $title = $model->getAttribute('name') ?? $model->getAttribute('title');

        if ($title === null) {
            return null;
        }

        $title = trim((string) $title);

        return $title !== '' ? $title : null;
    }

    private function extractUrl(Model $model): ?string
    {
        $url = $model->getAttribute('url');
        if (is_string($url)) {
            $url = trim($url);
            if ($url !== '') {
                return $url;
            }
        }

        $slug = $model->getAttribute('slug');
        if (is_string($slug)) {
            $slug = trim($slug);
            if ($slug !== '') {
                return '/' . ltrim($slug, '/');
            }
        }

        return null;
    }

    private function buildNormalizedKey(?string $normalizedType, ?int $normalizedId): string
    {
        if ($normalizedType === null || $normalizedId === null) {
            return self::EMPTY_REFERENCE_KEY;
        }

        return $normalizedType . '#' . $normalizedId;
    }
}
