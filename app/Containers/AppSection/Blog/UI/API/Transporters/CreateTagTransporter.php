<?php

namespace App\Containers\AppSection\Blog\UI\API\Transporters;

use App\Ship\Parents\Transporters\Transporter;

/**
 * CreateTagTransporter - Data Transfer Object for creating a tag
 * 
 * @property string $name
 * @property string|null $description
 * @property string|null $status
 * @property string|null $slug
 * @property array|null $seo_meta
 */
final class CreateTagTransporter extends Transporter
{
    /**
     * Get tag data fields only (for CreateTagTask)
     */
    public function getTagData(): array
    {
        return $this->onlyAsArray([
            'name',
            'description',
            'status',
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
}
