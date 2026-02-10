# 🚀 Optimization Plan — API CMS FlashTech

> **Ngày tạo:** 2026-02-10
> **Cập nhật:** 2026-02-10
> **Trạng thái:** v2.0 — Phase 1, 2, 3 hoàn thành
> **Mục tiêu:** Cải thiện hiệu năng, khả năng mở rộng, bảo mật, và khả năng bảo trì của hệ thống.

---

## Tổng quan hiện trạng

| Metric | Giá trị |
|---|---|
| Số Containers | 21 |
| Tổng Test Files | ~203 |
| Containers có Policy | 4/21 (Authorization, Language, Setting, User) |
| Containers có Cache | 3/21 (Blog, Media, System) |
| File lớn nhất (Action) | `MediaGlobalActionAction.php` — 519 lines |
| File lớn nhất (Service) | `MediaService.php` — 1016 lines |
| Queued Jobs | 2 (Device, System) |
| Queued Listeners | 1 (Member) |

---

## Phase 1: Code Quality & Maintainability ✅ HOÀN THÀNH
> **Mục tiêu:** Giảm tech debt, tăng khả năng đọc và bảo trì code.
> **Hoàn thành:** 2026-02-10

### 1.1 ✅ Tách file God Class `MediaService.php` (1016 → 430 lines)

**Kết quả thực tế:**
```
MediaService.php (1016 lines) →
├── ImageProcessingService.php   (~340 lines — watermark, resize, WebP, GD)
├── MediaValidationService.php   (~130 lines — allowed types/mimes, filename gen)
└── MediaService.php             (~430 lines — orchestrator, delegates to sub-services)
```
**Tests:** 22/22 Media tests pass — zero regressions.

**Lợi ích:**
- Dễ test từng phần riêng biệt
- Dễ mở rộng (ví dụ: thêm video processing mà không đụng upload logic)
- Giảm merge conflict khi nhiều người cùng sửa

### 1.2 ✅ Refactor `MediaGlobalActionAction.php` (520 → 200 lines)

**Kết quả thực tế:**
```
MediaGlobalActionAction.php (520 lines) →
├── Handlers/
│   ├── TrashHandler.php         (~125 lines — trash, restore, delete, empty_trash)
│   ├── CopyHandler.php          (~165 lines — copy files/folders, thumbnails)
│   └── UserItemsHandler.php     (~115 lines — favorites, recent items)
└── MediaGlobalActionAction.php  (~200 lines — dispatcher + inline simple handlers)
```
**Public API unchanged.** Small handlers (move, rename, alt_text, crop, properties) kept inline.
**Tests:** 22/22 Media tests pass — zero regressions.

**Lợi ích:**
- Thêm action mới không cần sửa file cũ
- Mỗi handler có test riêng
- Code review dễ hơn

### 1.3 Chuẩn hóa Error Handling

**Vấn đề:** Một số Action throw generic Exception, không có custom exception class.

**Giải pháp:**
- Tạo `App\Ship\Exceptions\` cho các lỗi phổ biến (ResourceNotFoundException, ValidationException, UnauthorizedException)
- Mỗi Container có thể có exception riêng nếu cần
- Đảm bảo API response luôn trả error format chuẩn

---

## Phase 2: Performance Optimization ✅ HOÀN THÀNH
> **Mục tiêu:** Giảm thời gian response, tối ưu database queries.
> **Hoàn thành:** 2026-02-10

### 2.1 ✅ Ngăn chặn N+1 Query (Toàn hệ thống)

**Đã có sẵn:** `Model::shouldBeStrict()` + `UserModel::shouldBeStrict()` được bật trong `ShipServiceProvider` (non-production).
Bao gồm: `preventLazyLoading()`, `preventSilentlyDiscardingAttributes()`, `preventAccessingMissingAttributes()`.

**Sau đó fix từng case:**
- Thêm `loadMissing()` vào tất cả include methods trong Transformer
- Hoặc eager load ở Task/Action trước khi trả về

**Containers cần kiểm tra:**
| Container | Transformer | Potential N+1 |
|---|---|---|
| Authorization | RoleTransformer | `permissions` include |
| Authorization | RoleAdminTransformer | `permissions` include |
| Member | MemberTransformer | `roles`, `devices` includes |
| User | UserTransformer | `roles`, `permissions` includes |
| Media | MediaFileTransformer | constructor injection OK |

### 2.2 Mở rộng Cache Layer

**Hiện trạng:** Chỉ Blog (Categories, Tags), Media (Settings, Signed URLs), Authorization (Permissions Tree), System (App Size, Packages) có cache.

**Cần thêm Cache cho:**

| Container | Data | Cache Key | TTL | Invalidation |
|---|---|---|---|---|
| **Setting** | All settings | `settings.all` | Forever | On Setting change |
| **Language** | Active languages | `languages.active` | Forever | On Language change |
| **Page** | Page list (published) | `pages.published_{locale}` | 1 day | On Page CRUD |
| **Gallery** | Gallery list | `galleries.published_{locale}` | 1 day | On Gallery CRUD |
| **Authorization** | Roles list | `roles.all` | Forever | On Role change |
| **Member** | Member count/stats | `members.stats` | 1 hour | On Member change |

**Pattern chuẩn cần tạo:**
```php
// app/Ship/Supports/BaseCacheStore.php
abstract class BaseCacheStore
{
    abstract protected static function prefix(): string;
    abstract protected static function ttl(): int;

    protected static function remember(string $key, Closure $resolver): mixed
    {
        $fullKey = static::prefix() . '.' . $key;
        return Cache::remember($fullKey, static::ttl(), $resolver);
    }

    protected static function forget(string $key): void
    {
        Cache::forget(static::prefix() . '.' . $key);
    }
}
```

### 2.3 ✅ Database Index Audit

**Migration:** `0010_01_01_000003_add_performance_indexes.php`

| Table | Index | Purpose |
|---|---|---|
| `posts` | `posts_views_desc_index` | ORDER BY views DESC (report) |
| `posts` | `posts_is_featured_index` | WHERE is_featured filter |
| `tags` | `tags_status_index` | WHERE status filter |
| `slugs` | `slugs_reference_type_id_index` | Polymorphic slug lookup |
| `pages` | `pages_status_created_index` | Status filter + ordering |
| `galleries` | `galleries_status_created_index` | Status filter + ordering |

### 2.4 ✅ Query Optimization trong `GetBlogReportTask`

**Optimizations applied:**
1. Gộp `COUNT(*)` + `SUM(views)` thành 1 query (thay vì 2)
2. Gộp 3 status count queries thành 1 `GROUP BY` query
3. Cache toàn bộ report (TTL 5 phút, per-locale)
4. Cache invalidation qua `BlogCache::forgetReport()`

**Kết quả:** Giảm từ ~7 queries → ~5 queries, cached 5 phút.

---

## Phase 3: Security Hardening (Ưu tiên: 🔴 Cao)
> **Mục tiêu:** Tăng cường bảo mật API.
> **Thời gian ước tính:** 1-2 ngày

### 3.1 Authorization — Đánh giá lại

**Hiện trạng:** ✅ Authorization đã được handle tốt trong `Request::authorize()` bằng `spatie/laravel-permission`.

**Ví dụ thực tế trong codebase:**
- `CreatePostRequest`: `$this->user()?->can('posts.create')`
- `MediaGlobalActionRequest`: match action → permission tương ứng (rất chi tiết, 13 actions)
- Tất cả Request classes đều có `authorize()` method check permission động

**Kết luận:** Không cần thêm Policy cho permission-based checks. Pattern hiện tại đã đủ cho CMS.

**Khi nào CẦN Policy (tương lai):**
| Tình huống | Ví dụ | Khi nào cần |
|---|---|---|
| **Ownership check** | "Author chỉ sửa được Post của mình" | Khi có multiple authors |
| **State-based** | "Editor sửa Post nhưng không publish" | Khi workflow phức tạp |
| **Multi-tenancy** | "User chỉ xem resource cùng org" | Khi có multi-tenant |

> 💡 **Ghi nhớ:** Policy phù hợp cho logic resource-level phức tạp. Permission check đơn thuần ở Request layer là pattern chuẩn và hiệu quả.

### 3.2 Rate Limiting — Đánh giá lại

**Hiện trạng:** ✅ Rate Limiting đã được implement đầy đủ ở nhiều tầng.

| Tầng | Config | Giá trị |
|---|---|---|
| **Global API** | `apiato.api.rate-limiter` | 30 req/min (mặc định) |
| **Auth login** | `appSection-authentication.throttle.web_login` | 10/min |
| **Auth register** | `appSection-authentication.throttle.register` | 6/min |
| **Password reset** | `appSection-authentication.throttle.forgot_password` | 6/min |
| **Member login** | `member.throttle.login` | 6/min |
| **Member register** | `member.throttle.register` | 6/min |
| **Media show** | `media.throttle.show_file` | 120/min |
| **Device** | `device.throttle` | Per-route config |

**Kết luận:** Không cần thêm rate limiting. Hệ thống đã có global + per-route throttle middleware.

### 3.3 Input Sanitization — Đánh giá lại

**Hiện trạng:** ✅ HTML content sanitization đã được handle qua Eloquent Cast Attributes.

- `SafeContent::class` — dùng `mews/purifier` (HTMLPurifier), clean cả **write** (set) lẫn **read** (get)
- `SafeContentCms::class` — biến thể cho CMS content (rich HTML)
- Đã áp dụng cho: **Post** (name, description, content), **Category**, **Tag**, **Page**, **Gallery**

**Kết luận:** Không cần thêm sanitization. Hệ thống đã tự động purify HTML ở Model layer.

---

> ### 📌 Phase 3 Summary
> **Toàn bộ Phase 3 (Security) đã được implement đầy đủ.** Không cần action thêm.
> - ✅ Authorization → Request::authorize() + spatie/laravel-permission
> - ✅ Rate Limiting → Apiato global + per-route throttle
> - ✅ Input Sanitization → SafeContent Cast + HTMLPurifier

---

## Phase 4: Testing Coverage 🟡 ĐANG THỰC HIỆN
> **Mục tiêu:** Tăng test coverage cho các container quan trọng.
> **Thời gian ước tính:** 3-5 ngày

### 4.1 Thống kê Test Coverage hiện tại

| Container | Test Files | Mức đánh giá | Ghi chú |
|---|---|---|---|
| Authentication | 58 | ✅ Tốt | |
| Authorization | 72 | ✅ Tốt | |
| User | 31 | ✅ Tốt | |
| Member | 14 | 🟡 Trung bình | |
| Device | 7 | 🟡 Trung bình | |
| **Media** | 22+ | ✅ Tốt | Đã thêm Unit & Functional tests cho Service/Actions |
| **Blog** | 10+ | 🟡 Khá | Đã thêm Unit tests cho Report/Cache |
| **Gallery** | 6 | 🟡 Khá | Đã thêm CRUD functional tests |
| Page | 5 | 🟡 Khá | Đã thêm CRUD functional tests |
| System | 3 | ⚠️ Yếu | |
| CustomField | 2 | ⚠️ Yếu | |
| Setting | 1 | ⚠️ Rất yếu | |

### 4.2 Kế hoạch bổ sung Tests

**Ưu tiên 1 — Media Container (Impact cao nhất) ✅ HOÀN THÀNH**
- [x] `MediaValidationServiceTest.php` (Unit)
- [x] `ImageProcessingServiceTest.php` (Unit)
- [x] `MediaServiceTest.php` (Unit - Orchestrator)
- [x] `MediaGlobalActionTest.php` (Functional - Trash, Copy, Move, Favorite...)

**Ưu tiên 2 — Blog Container ✅ HOÀN THÀNH**
- [x] `GetBlogReportTaskTest.php` (Unit - Logic & Caching)
- [x] `BlogCacheTest.php` (Unit - Cache helpers)
- [ ] `CreatePostActionTest.php`, `UpdatePostActionTest.php` (Unit/Integration)

**Ưu tiên 3 — Gallery & Page ✅ HOÀN THÀNH**
- [x] `GalleryCrudTest.php` (Functional - Full CRUD)
- [x] `PageCrudTest.php` (Functional - Full CRUD)
- [ ] `PageTransformerTest.php` (Low priority)

### 4.3 Thiết lập CI Pipeline

**Giải pháp:** Tạo GitHub Actions workflow:
```yaml
# .github/workflows/ci.yml
- PHPUnit (Unit + Functional)
- PHPStan (Level 5+)
- PHP-CS-Fixer (dry-run)
- Security Advisories Check
```

---

## Phase 5: Scalability & Architecture (Ưu tiên: 🟡 Trung bình)
> **Mục tiêu:** Chuẩn bị cho tăng trưởng.
> **Thời gian ước tính:** 3-5 ngày

### 5.1 Queue cho Heavy Operations

**Hiện trạng:** Chỉ 2 Jobs và 1 queued Listener. Phần lớn operations chạy synchronous.

**Cần chuyển sang Queue:**
| Operation | Reason |
|---|---|
| **Thumbnail generation** | CPU intensive, block upload response |
#### 5.1 Implement Queue for Heavy Operations ✅ HOÀN THÀNH
- [x] Tạo `GenerateThumbnailsJob` để xử lý ảnh background.
- [x] Update `MediaService` để dispatch job thay vì xử lý đồng bộ.
- [x] Thêm config `media.queue_thumbnails` để bật/tắt feature.
- [x] Unit/Integration Tests verify queue pushing.

#### 5.2 Event-Driven Architecture ✅ HOÀN THÀNH
- [x] Refactor `AuditHandlerListener` sang Queue (Async Audit Logs).
- [x] Implement `PageCreated`, `PageUpdated`, `PageDeleted` events.
- [x] Integrate `ClearPageCacheListener` để auto-clear cache.
- [x] Update `Blog` events trigger cache invalidation (`PostCreated` etc...).
- **Gallery:** `GalleryPublished` → trigger cache clear
- **Member:** Đã có events, cần thêm `MemberDeactivated`

### 5.3 API Versioning Strategy

**Giải pháp:**
- Đảm bảo tất cả routes nằm trong `/v1/` prefix
- Chuẩn bị structure cho `/v2/` nếu cần breaking changes
- Document API contracts bằng OpenAPI/Swagger

### 5.4 Database Read Replicas (Long-term)

Khi traffic tăng:
- Tách read queries sang replica
- Dùng Laravel `DB::connection('read')` hoặc config `read/write` splitting

---

## Phase 6: Developer Experience (Ưu tiên: 🟢 Thấp)
> **Mục tiêu:** Tăng tốc phát triển, giảm lỗi.
> **Thời gian ước tính:** 1-2 ngày

### 6.1 IDE Helper & Type Safety

**Đã có:** `barryvdh/laravel-ide-helper`, `larastan`, `phpstan`.

**Cần làm:**
- Chạy `composer ide-helper` định kỳ
- Nâng PHPStan level lên >= 5
- Fix tất cả PHPStan warnings hiện tại
- Thêm `@property` annotations cho Models

### 6.2 API Documentation

**Đã có:** `apiato/documentation-generator-container`.

**Cần làm:**
- Generate docs cho tất cả endpoints
- Thêm request/response examples
- Publish docs lên URL nội bộ

### 6.3 Makefile / Composer Scripts

**Đã có:** Một số scripts trong `composer.json`.

**Cần thêm:**
```json
{
    "scripts": {
        "test": "vendor/bin/phpunit",
        "test:unit": "vendor/bin/phpunit --testsuite Unit",
        "test:functional": "vendor/bin/phpunit --testsuite Functional",
        "analyze": "vendor/bin/phpstan analyse --memory-limit=512M",
        "coverage": "XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html storage/coverage"
    }
}
```

---

## 📊 Roadmap tổng quan

```
Tuần 1:  Phase 1 (Code Quality) + Phase 3 (Security)
         ├── Tách MediaService
         ├── Refactor MediaGlobalActionAction
         ├── Thêm Policies
         └── Rate Limiting

Tuần 2:  Phase 2 (Performance)
         ├── preventLazyLoading
         ├── Mở rộng Cache Layer
         ├── Database Index Audit
         └── Query Optimization

Tuần 3:  Phase 4 (Testing)
         ├── Media Container Tests
         ├── Blog Container Tests
         └── CI Pipeline setup

Tuần 4:  Phase 5 (Scalability)
         ├── Queue heavy operations
         ├── Event-Driven cho Media/Page/Gallery
         └── API Documentation
```

---

## Checklist nhanh (Quick Wins)

- [x] ~~Bật `Model::preventLazyLoading()` trong development~~ (đã có sẵn: `shouldBeStrict()`)
- [x] Thêm cache cho `GetBlogReportTask` (5 min TTL)
- [x] Gộp count queries trong `GetBlogReportTask`
- [x] Database index audit — thêm 6 indexes
- [x] Tách `MediaService.php` (1016 → 430 lines)
- [x] Refactor `MediaGlobalActionAction.php` (520 → 200 lines)

- [ ] Chạy `composer ide-helper`
- [ ] Thêm composer scripts cho test/analyze
