<?php

namespace App\Containers\AppSection\Blog\UI\API\Transporters;

use App\Ship\Parents\Transporters\Transporter;

/**
 * UpdateCategoryTransporter - Data Transfer Object for updating a category
 * 
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property string|null $status
 * @property int|null $parent_id
 * @property string|null $icon
 * @property int|null $order
 * @property bool|null $is_featured
 * @property bool|null $is_default
 * @property string|null $slug
 * @property array|null $seo_meta
 * @property array|string|null $custom_fields
 */
final class UpdateCategoryTransporter extends Transporter
{
    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    /**
     * Get category data fields only (for UpdateCategoryTask)
     */
    public function getCategoryData(): array
    {
        return array_filter($this->onlyAsArray([
            'name',
            'description',
            'status',
            'parent_id',
            'icon',
            'order',
            'is_featured',
            'is_default',
        ]), fn ($value) => $value !== null);
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getSeoMeta(): ?array
    {
        return $this->seo_meta;
    }

    public function getCustomFields(): array|string|null
    {
        return $this->custom_fields;
    }
}
