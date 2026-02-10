<?php

namespace App\Containers\AppSection\Blog\UI\API\Transporters;

use App\Ship\Parents\Transporters\Transporter;

/**
 * CreateCategoryTransporter - Data Transfer Object for creating a category
 * 
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
final class CreateCategoryTransporter extends Transporter
{
    /**
     * Get category data fields only (for CreateCategoryTask)
     */
    public function getCategoryData(): array
    {
        return $this->onlyAsArray([
            'name',
            'description',
            'status',
            'parent_id',
            'icon',
            'order',
            'is_featured',
            'is_default',
        ]);
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
