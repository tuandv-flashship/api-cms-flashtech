<?php

namespace App\Containers\AppSection\AdminMenu\Data\Seeders;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Models\AdminMenuItemTranslation;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;
use App\Ship\Parents\Seeders\Seeder as ParentSeeder;
use Illuminate\Support\Facades\DB;

final class AdminMenuSeeder_1 extends ParentSeeder
{
    public function run(): void
    {
        // Clear existing data before re-seeding.
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        AdminMenuItemTranslation::query()->truncate();
        AdminMenuItem::query()->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

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
                'section' => $item['section'] ?? null,
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
                        'section' => $fields['section'] ?? null,
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
        'cms-settings-general' => [
            'vi' => ['name' => 'Chung', 'description' => 'Xem và cập nhật cài đặt chung và kích hoạt giấy phép', 'section' => 'Chung'],
        ],
        'cms-settings-email-rules' => [
            'vi' => ['name' => 'Quy tắc email', 'description' => 'Cấu hình quy tắc email để kiểm tra', 'section' => 'Chung'],
        ],
        'cms-settings-phone-number' => [
            'vi' => ['name' => 'Số điện thoại', 'description' => 'Cấu hình cài đặt trường số điện thoại', 'section' => 'Chung'],
        ],
        'cms-settings-media' => [
            'vi' => ['name' => 'Phương tiện', 'description' => 'Xem và cập nhật cài đặt media', 'section' => 'Chung'],
        ],
        'cms-settings-languages' => [
            'vi' => ['name' => 'Ngôn ngữ', 'description' => 'Xem và cập nhật ngôn ngữ trang web của bạn', 'section' => 'Chung'],
        ],
        'cms-settings-admin-appearance' => [
            'vi' => ['name' => 'Giao Diện Quản Trị', 'description' => 'Xem và cập nhật logo, favicon, bố cục,...', 'section' => 'Chung'],
        ],
        'cms-settings-cache' => [
            'vi' => ['name' => 'Bộ nhớ cache', 'description' => 'Cấu hình bộ nhớ đệm để tối ưu tốc độ', 'section' => 'Chung'],
        ],
        'cms-settings-speed-optimization' => [
            'vi' => ['name' => 'Tối ưu tốc độ', 'description' => 'Nén HTML output, inline CSS, xóa chú thích...', 'section' => 'Chung'],
        ],
        'cms-settings-locales' => [
            'vi' => ['name' => 'Ngôn ngữ', 'description' => 'Xem, tải xuống và nhập ngôn ngữ', 'section' => 'Bản địa hóa'],
        ],
        'cms-settings-other-translations' => [
            'vi' => ['name' => 'Bản dịch khác', 'description' => 'Quản lý các bản dịch khác (admin, plugins, packages...)', 'section' => 'Bản địa hóa'],
        ],
        'cms-core-system' => [
            'vi' => ['name' => 'Quản trị hệ thống', 'description' => null],
        ],
        'cms-system-users' => [
            'vi' => ['name' => 'Người dùng', 'description' => 'Quản lý người dùng quản trị và phân quyền', 'section' => 'Người dùng & Quyền'],
        ],
        'cms-system-roles' => [
            'vi' => ['name' => 'Vai trò & Quyền', 'description' => 'Quản lý vai trò và phân quyền', 'section' => 'Người dùng & Quyền'],
        ],
        'cms-system-audit-logs' => [
            'vi' => ['name' => 'Nhật ký hoạt động', 'description' => 'Theo dõi hoạt động và thay đổi của quản trị viên', 'section' => 'Giám sát'],
        ],
        'cms-system-request-logs' => [
            'vi' => ['name' => 'Nhật ký yêu cầu', 'description' => 'Giám sát nhật ký và lỗi API', 'section' => 'Giám sát'],
        ],
        'cms-system-info' => [
            'vi' => ['name' => 'Thông tin hệ thống', 'description' => 'Xem môi trường và cấu hình hệ thống', 'section' => 'Hệ thống'],
        ],
        'cms-system-cache' => [
            'vi' => ['name' => 'Quản lý bộ nhớ đệm', 'description' => 'Xóa và quản lý bộ nhớ đệm ứng dụng', 'section' => 'Hệ thống'],
        ],
    ];
}
