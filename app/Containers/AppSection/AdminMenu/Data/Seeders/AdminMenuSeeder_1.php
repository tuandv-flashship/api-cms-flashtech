<?php

namespace App\Containers\AppSection\AdminMenu\Data\Seeders;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Models\AdminMenuItemTranslation;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;

final class AdminMenuSeeder_1 extends ParentSeeder
{
    public function run(): void
    {
        // Skip if items already exist.
        if (AdminMenuItem::query()->exists()) {
            return;
        }

        $menu = config('admin-menu', []);

        if (! is_array($menu) || $menu === []) {
            return;
        }

        $this->seedItems($menu, null);

        // Flush admin menu cache to reflect new DB data.
        app(AdminMenu::class)->flush();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function seedItems(array $items, ?int $parentId): void
    {
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                continue;
            }

            // Default language is English → main table stores English values from config.
            $record = AdminMenuItem::query()->create([
                'parent_id' => $parentId,
                'key' => $item['id'],
                'name' => $item['name'] ?? '',
                'icon' => $item['icon'] ?? null,
                'route' => $item['route'] ?? null,
                'permissions' => $item['permissions'] ?? null,
                'children_display' => $item['children_display'] ?? 'sidebar',
                'description' => $item['description'] ?? null,
                'priority' => $item['priority'] ?? 0,
                'is_active' => true,
            ]);

            // Non-default translations go into translations table.
            $itemId = $item['id'];
            if (isset(self::TRANSLATIONS[$itemId])) {
                foreach (self::TRANSLATIONS[$itemId] as $langCode => $fields) {
                    AdminMenuItemTranslation::query()->create([
                        'admin_menu_items_id' => $record->getKey(),
                        'lang_code' => $langCode,
                        'name' => $fields['name'] ?? null,
                        'description' => $fields['description'] ?? null,
                    ]);
                }
            }

            if (isset($item['children']) && is_array($item['children'])) {
                $this->seedItems($item['children'], (int) $record->getKey());
            }
        }
    }

    /**
     * Non-default language translations, keyed by menu item ID → lang_code → fields.
     * English (default) is stored directly in admin_menu_items from config.
     */
    private const TRANSLATIONS = [
        'cms-core-dashboard' => [
            'vi' => ['name' => 'Bảng điều khiển', 'description' => null],
        ],
        'cms-core-page' => [
            'vi' => ['name' => 'Trang', 'description' => null],
        ],
        'cms-plugins-blog' => [
            'vi' => ['name' => 'Blog', 'description' => null],
        ],
        'cms-plugins-blog-post' => [
            'vi' => ['name' => 'Bài viết', 'description' => null],
        ],
        'cms-plugins-blog-categories' => [
            'vi' => ['name' => 'Danh mục', 'description' => null],
        ],
        'cms-plugins-blog-tags' => [
            'vi' => ['name' => 'Thẻ', 'description' => null],
        ],
        'cms-plugins-blog-reports' => [
            'vi' => ['name' => 'Báo cáo', 'description' => null],
        ],
        'cms-plugins-gallery' => [
            'vi' => ['name' => 'Thư viện ảnh', 'description' => null],
        ],
        'cms-core-member' => [
            'vi' => ['name' => 'Thành viên', 'description' => null],
        ],
        'cms-plugins-custom-field' => [
            'vi' => ['name' => 'Trường tùy chỉnh', 'description' => null],
        ],
        'cms-core-media' => [
            'vi' => ['name' => 'Quản lý tập tin', 'description' => null],
        ],
        'cms-core-appearance' => [
            'vi' => ['name' => 'Hiển thị', 'description' => null],
        ],
        'cms-core-menu' => [
            'vi' => ['name' => 'Menu', 'description' => null],
        ],
        'cms-core-tools' => [
            'vi' => ['name' => 'Công cụ', 'description' => null],
        ],
        'cms-tools-data-synchronize' => [
            'vi' => ['name' => 'Đồng bộ dữ liệu', 'description' => null],
        ],
        'cms-core-settings' => [
            'vi' => ['name' => 'Cài đặt', 'description' => null],
        ],
        'cms-settings-languages' => [
            'vi' => ['name' => 'Ngôn ngữ', 'description' => 'Quản lý ngôn ngữ và bản địa hoá'],
        ],
        'cms-settings-translations' => [
            'vi' => ['name' => 'Bản dịch', 'description' => 'Quản lý chuỗi dịch cho tất cả ngôn ngữ'],
        ],
        'cms-core-system' => [
            'vi' => ['name' => 'Quản trị hệ thống', 'description' => null],
        ],
        'cms-system-users' => [
            'vi' => ['name' => 'Người dùng', 'description' => 'Quản lý người dùng quản trị và phân quyền'],
        ],
        'cms-system-roles' => [
            'vi' => ['name' => 'Vai trò & Quyền', 'description' => 'Quản lý vai trò và phân quyền'],
        ],
        'cms-system-audit-logs' => [
            'vi' => ['name' => 'Nhật ký hoạt động', 'description' => 'Theo dõi hoạt động và thay đổi của quản trị viên'],
        ],
        'cms-system-request-logs' => [
            'vi' => ['name' => 'Nhật ký yêu cầu', 'description' => 'Giám sát nhật ký và lỗi API'],
        ],
        'cms-system-info' => [
            'vi' => ['name' => 'Thông tin hệ thống', 'description' => 'Xem môi trường và cấu hình hệ thống'],
        ],
        'cms-system-cache' => [
            'vi' => ['name' => 'Quản lý bộ nhớ đệm', 'description' => 'Xóa và quản lý bộ nhớ đệm ứng dụng'],
        ],
    ];
}
