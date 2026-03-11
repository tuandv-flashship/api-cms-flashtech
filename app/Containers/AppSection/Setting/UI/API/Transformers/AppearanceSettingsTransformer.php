<?php

namespace App\Containers\AppSection\Setting\UI\API\Transformers;

use App\Containers\AppSection\Media\Services\MediaService;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class AppearanceSettingsTransformer extends ParentTransformer
{
    public function __construct(
        private readonly MediaService $mediaService,
    ) {
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function transform(mixed $settings): array
    {
        if (is_object($settings)) {
            $settings = get_object_vars($settings);
        }

        if (!is_array($settings)) {
            $settings = [];
        }

        return [
            'type' => 'AppearanceSettings',
            'id' => 'appearance',
            'admin_logo' => $this->toFullUrl($settings['admin_logo'] ?? null),
            'admin_logo_max_height' => isset($settings['admin_logo_max_height'])
                ? (int) $settings['admin_logo_max_height']
                : null,
            'admin_favicon' => $this->toFullUrl($settings['admin_favicon'] ?? null),
            'admin_favicon_type' => $settings['admin_favicon_type'] ?? null,
            'login_screen_backgrounds' => $this->resolveBackgrounds($settings['login_screen_backgrounds'] ?? []),
            'admin_title' => $settings['admin_title'] ?? null,
            'admin_primary_font' => $settings['admin_primary_font'] ?? null,
            'admin_primary_color' => $settings['admin_primary_color'] ?? null,
            'admin_secondary_color' => $settings['admin_secondary_color'] ?? null,
            'admin_heading_color' => $settings['admin_heading_color'] ?? null,
            'admin_text_color' => $settings['admin_text_color'] ?? null,
            'admin_link_color' => $settings['admin_link_color'] ?? null,
            'admin_link_hover_color' => $settings['admin_link_hover_color'] ?? null,
            'admin_appearance_locale' => $settings['admin_appearance_locale'] ?? null,
            'admin_appearance_locale_direction' => $settings['admin_appearance_locale_direction'] ?? 'ltr',
            'rich_editor' => $settings['rich_editor'] ?? null,
            'enable_page_visual_builder' => (bool) ($settings['enable_page_visual_builder'] ?? false),
            'admin_appearance_layout' => $settings['admin_appearance_layout'] ?? null,
            'admin_appearance_container_width' => $settings['admin_appearance_container_width'] ?? null,
            'admin_appearance_show_menu_item_icon' => (bool) ($settings['admin_appearance_show_menu_item_icon'] ?? false),
            'show_admin_bar' => (bool) ($settings['show_admin_bar'] ?? false),
            'show_theme_guideline_link' => (bool) ($settings['show_theme_guideline_link'] ?? false),
            'admin_appearance_custom_css' => $settings['admin_appearance_custom_css'] ?? null,
            'admin_appearance_custom_header_js' => $settings['admin_appearance_custom_header_js'] ?? null,
            'admin_appearance_custom_body_js' => $settings['admin_appearance_custom_body_js'] ?? null,
            'admin_appearance_custom_footer_js' => $settings['admin_appearance_custom_footer_js'] ?? null,
        ];
    }

    private function toFullUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return $this->mediaService->getImageUrl($path);
    }

    /**
     * @param mixed $backgrounds
     * @return array<int, string>
     */
    private function resolveBackgrounds(mixed $backgrounds): array
    {
        if (is_string($backgrounds)) {
            $decoded = json_decode($backgrounds, true);
            $backgrounds = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($backgrounds)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($bg) => $this->toFullUrl(is_string($bg) ? $bg : null),
            $backgrounds,
        )));
    }
}
