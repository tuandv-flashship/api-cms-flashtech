<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class BlogReportTransformer extends ParentTransformer
{
    /**
     * @param array<string, mixed>|\stdClass $report
     */
    public function transform($report): array
    {
        $data = (array) $report;

        return [
            'type' => 'BlogReport',
            'id' => 'blog-reports',
            'totals' => $data['totals'] ?? [],
            'statuses' => $data['statuses'] ?? [],
            'top_viewed_posts' => $data['top_viewed_posts'] ?? [],
            'recent_posts' => $data['recent_posts'] ?? [],
            'posts_per_category' => $data['posts_per_category'] ?? [],
        ];
    }
}
