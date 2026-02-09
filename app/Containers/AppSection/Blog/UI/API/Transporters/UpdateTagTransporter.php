<?php

namespace App\Containers\AppSection\Blog\UI\API\Transporters;

use App\Ship\Parents\Transporters\Transporter;

/**
 * UpdateTagTransporter - Data Transfer Object for updating a tag
 * 
 * @property int $tag_id
 * @property string $name
 * @property string|null $description
 * @property string|null $status
 * @property string|null $slug
 * @property array|null $seo_meta
 */
final class UpdateTagTransporter extends Transporter
{
    /**
     * Get tag ID
     */
    public function getTagId(): int
    {
        return $this->tag_id;
    }

    /**
     * Get tag data fields only (for UpdateTagTask)
     */
    public function getTagData(): array
    {
        return array_filter($this->onlyAsArray([
            'name',
            'description',
            'status',
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
}
