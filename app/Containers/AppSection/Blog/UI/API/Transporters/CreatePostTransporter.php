<?php

namespace App\Containers\AppSection\Blog\UI\API\Transporters;

use App\Ship\Parents\Transporters\Transporter;

/**
 * CreatePostTransporter - Data Transfer Object for creating a post
 * 
 * @property string $name
 * @property string|null $description
 * @property string|null $content
 * @property string|null $status
 * @property bool|null $is_featured
 * @property string|null $image
 * @property string|null $banner_image
 * @property string|null $format_type
 * @property array<int>|null $category_ids
 * @property array<int>|null $tag_ids
 * @property array<string>|null $tag_names
 * @property string|null $slug
 * @property array|string|null $gallery
 * @property array|null $seo_meta
 * @property array|string|null $custom_fields
 */
final class CreatePostTransporter extends Transporter
{
    /**
     * Get post data fields only (for CreatePostTask)
     */
    public function getPostData(): array
    {
        return $this->onlyAsArray([
            'name',
            'description',
            'content',
            'status',
            'is_featured',
            'image',
            'format_type',
        ]);
    }

    /**
     * Get category IDs
     */
    public function getCategoryIds(): ?array
    {
        return $this->category_ids;
    }

    /**
     * Get tag IDs
     */
    public function getTagIds(): ?array
    {
        return $this->tag_ids;
    }

    /**
     * Get tag names (for creating new tags)
     */
    public function getTagNames(): ?array
    {
        return $this->tag_names;
    }

    /**
     * Get slug
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Get gallery data
     */
    public function getGallery(): array|string|null
    {
        return $this->gallery;
    }

    /**
     * Get SEO meta data
     */
    public function getSeoMeta(): ?array
    {
        return $this->seo_meta;
    }

    /**
     * Get custom fields data
     */
    public function getCustomFields(): array|string|null
    {
        return $this->custom_fields;
    }

    /**
     * Get banner image path
     */
    public function getBannerImage(): ?string
    {
        return $this->banner_image;
    }
}
