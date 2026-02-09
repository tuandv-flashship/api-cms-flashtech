# Báo Cáo Phân Tích & Đề Xuất Refactoring Containers Theo Chuẩn Apiato Porto SAP

> Ngày tạo: 2026-02-09

---

## 1. Tổng Quan Dự Án

### 1.1 Containers Hiện Tại (21 containers trong AppSection)

| Container | Số files | Trạng thái | Mức độ tuân thủ |
|-----------|----------|------------|-----------------|
| AuditLog | 41 | ✅ Hoàn chỉnh | 90% |
| Authentication | 146 | ✅ Hoàn chỉnh | 95% |
| Authorization | 188 | ✅ Hoàn chỉnh | 95% |
| Blog | 152 | ✅ Hoàn chỉnh | 95% |
| CustomField | 47 | ✅ Hoàn chỉnh | 90% |
| Device | 95 | ✅ Hoàn chỉnh | 90% |
| Gallery | 41 | ✅ Hoàn chỉnh | 85% |
| Language | 55 | ✅ Hoàn chỉnh | 90% |
| LanguageAdvanced | 13 | ⚠️ Nhỏ | 85% |
| Media | 79 | ✅ Hoàn chỉnh | 90% |
| Member | 126 | ✅ Hoàn chỉnh | 95% |
| MetaBox | 9 | ⚠️ Nhỏ | 80% |
| Page | 47 | ✅ Hoàn chỉnh | 90% |
| RequestLog | 35 | ✅ Hoàn chỉnh | 90% |
| Revision | 15 | ✅ Hoàn chỉnh | 85% |
| Setting | 50 | ✅ Hoàn chỉnh | 90% |
| Slug | 15 | ✅ Hoàn chỉnh | 85% |
| System | 58 | ✅ Hoàn chỉnh | 85% |
| Tools | 75 | ✅ Hoàn chỉnh | 85% |
| Translation | 49 | ✅ Hoàn chỉnh | 90% |
| User | 79 | ✅ Hoàn chỉnh | 95% |

### 1.2 Đánh giá chung

**Điểm mạnh:**
- ✅ Cấu trúc thư mục đúng chuẩn Porto SAP
- ✅ Controllers gọi Actions, không gọi trực tiếp Tasks
- ✅ Routes tách riêng từng file theo naming convention
- ✅ Requests có validation rules và authorize()
- ✅ Tasks tuân thủ Single Responsibility
- ✅ Có Repositories layer cho database operations
- ✅ Có đầy đủ Parent classes trong Ship layer

**Điểm cần cải thiện:**
- ⚠️ Một số Container thiếu thư mục Data/{Seeders, Factories}
- ⚠️ Một số Tasks có thể được tái sử dụng tốt hơn
- ⚠️ Hash ID chưa được sử dụng đồng nhất ở tất cả responses
- ⚠️ Thiếu Transporters layer (optional nhưng recommended)
- ⚠️ Một số Controllers có logic xử lý thay vì chỉ delegate

---

## 2. Phân Tích Chi Tiết Từng Pattern

### 2.1 Route Pattern ✅ Tốt

**Hiện tại:**
```
UI/API/Routes/
├── CreatePost.v1.private.php
├── UpdatePost.v1.private.php
└── ListPosts.v1.private.php
```

**Đánh giá:** Đã đúng chuẩn `{UseCase}.v{N}.{visibility}.php`

### 2.2 Controller Pattern ⚠️ Cần cải thiện nhẹ

**Hiện tại:**
```php
public function __invoke(CreatePostRequest $request, CreatePostAction $action): JsonResponse
{
    $payload = $request->validated();
    $data = Arr::only($payload, [
        'name', 'description', 'content', 'status', 'is_featured', 'image', 'format_type',
    ]);

    $post = $action->run(
        $data,
        $payload['category_ids'] ?? null,
        // ... nhiều parameters
    );

    return Response::create($post, PostTransformer::class)->created();
}
```

**Vấn đề:**
1. Controller đang có logic extract data từ payload
2. Quá nhiều parameters truyền vào Action

**Đề xuất:**
```php
public function __invoke(CreatePostRequest $request, CreatePostAction $action): JsonResponse
{
    $post = $action->run($request->toTransporter());
    
    return Response::create($post, PostTransformer::class)->created();
}
```

### 2.3 Request Pattern ✅ Đã cập nhật theo Apiato 13.x

> **Apiato 13.x Breaking Changes:**
> - `$access` property đã bị **XÓA**
> - `$urlParameters` property đã bị **XÓA**
> - `hasAccess()` method đã bị **XÓA**
> - Sử dụng `$request->route('param')` để truy cập route parameters

**Cấu trúc Request chuẩn Apiato 13.x:**
```php
final class CreatePostRequest extends ParentRequest
{
    protected array $decode = [
        'category_ids.*',
        'tag_ids.*',
    ];

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        // Sử dụng can() để hỗ trợ Super-Admin via Gate::before()
        return $this->user()?->can('posts.create') ?? false;
    }
}
```

**Truy cập Route Parameters (Apiato 13.x):**
```php
// Thay vì sử dụng $urlParameters
$id = $this->route('id');  // Trả về decoded integer (nếu có trong $decode)
```
```

### 2.4 Action Pattern ✅ Tốt (có thể tối ưu)

**Hiện tại:** Actions đang hoạt động đúng - orchestrate các Tasks

**Đề xuất tách một số logic phức tạp:**
- `CreatePostAction` có quá nhiều responsibilities
- Có thể tách thành SubActions cho Gallery, SEO Meta, Custom Fields

### 2.5 Task Pattern ✅ Tốt

**Hiện tại:** Tasks đã tuân thủ Single Responsibility rất tốt

### 2.6 Transformer Pattern ⚠️ Cần kiểm tra Hash ID

Cần đảm bảo tất cả responses sử dụng `getHashedKey()` cho ID fields

---

## 3. Kế Hoạch Refactoring

### Phase 1: Chuẩn hóa Requests theo Apiato 13.x ✅ HOÀN THÀNH

**Mục tiêu:** Xóa deprecated properties (`$access`, `$urlParameters`) theo Apiato 13.x

**Đã hoàn thành:**
- [x] Blog/Requests/* (28 files)
- [x] Page/Requests/* 
- [x] Media/Requests/*
- [x] Setting/Requests/*
- [x] Authorization/Requests/*
- [x] Authentication/Requests/*
- [x] Member/Requests/*
- [x] Device/Requests/*
- [x] ... (tất cả 168+ Request files)

**Script đã tạo:**
```bash
php scripts/refactor-requests-apiato13.php
```

### Phase 2: Thêm Transporters Layer ✅ HOÀN THÀNH (Pragmatic Approach)

**Mục tiêu:** Clean up Controllers và Actions bằng Transporters cho các Action phức tạp.

**Chi tiết thực hiện:**
- [x] Tạo Base Class `App\Ship\Parents\Transporters\Transporter` (extends `Fluent`)
- [x] Refactor Container **Blog**:
    - `CreatePost`, `UpdatePost`
    - `CreateCategory`, `UpdateCategory`
    - `CreateTag`, `UpdateTag`
- [x] Refactor Container **Member**:
    - `UpdateMemberProfile` (xử lý logic phức tạp với username, password, email verification)
- [ ] Các Action đơn giản (Find/Delete/List) **GIỮ NGUYÊN** để tránh over-engineering.

### Phase 3: Refactor Controllers ✅ HOÀN THÀNH (Integrated)

**Mục tiêu:** Controllers chỉ delegate, không có logic data extraction.

**Chi tiết thực hiện:**
- Đã thực hiện song song với Phase 2.
- Các Controllers đã refactor hiện chỉ gọi `Transporter::fromRequest($request)` và truyền vào Action.

### Phase 4: Kiểm tra & Chuẩn hóa Hash IDs ✅ HOÀN THÀNH

**Mục tiêu:** Đảm bảo tất cả API responses sử dụng hashed IDs và loại bỏ code duplicate.

**Chi tiết thực hiện:**
- [x] Thêm method `hashId(int|string|null $id)` vào Base Transformer `App\Ship\Parents\Transformers\Transformer`
- [x] Cập nhật logic `hashId` để handle cả numeric string và non-numeric ID an toàn.
- [x] Xóa code duplicate (private method custom `hashId`) trong 8 Transformers con:
    - `PageTransformer`, `FieldGroupTransformer`, `CustomFieldBoxTransformer`
    - `GalleryTransformer`, `PostTransformer`, `CategoryTransformer`
    - `RevisionTransformer`, `MediaFileTransformer`
- [x] Verify bằng Unit Tests (Blog, Page, Member, User).

### Phase 5: Thêm thiếu Seeders/Factories (Ưu tiên thấp) ⚠️ Đang thực hiện

**Mục tiêu:** Bổ sung cho testing và seeding.

**Kết quả:**
- [x] Đã phát hiện `Gallery` và `CustomField` thiếu Tests Folder.
- [x] Đã tạo `Tests/Unit/UI/API/Transformers` cho 2 Container này.
- [x] Đã tạo Factories: `GalleryFactory`, `FieldGroupFactory`, `FieldItemFactory`.
- [x] Đã verify logic Hash ID với Unit Tests mới.

---

## 4. Các File Template Chuẩn

### 4.1 Request Template (Apiato 13.x)
```php
<?php

namespace App\Containers\AppSection\{Container}\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class {Action}Request extends ParentRequest
{
    // Chỉ khai báo $decode khi cần decode Hashid
    protected array $decode = ['id'];

    public function rules(): array
    {
        return [
            // Validation rules
        ];
    }

    public function authorize(): bool
    {
        // Sử dụng can() để hỗ trợ Super-Admin via Gate::before()
        return $this->user()?->can('{container}.{action}') ?? false;
    }
}
```

### 4.2 Controller Template (With Transporter)
```php
<?php

namespace App\Containers\AppSection\{Container}\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\{Container}\Actions\{Action}Action;
use App\Containers\AppSection\{Container}\UI\API\Requests\{Action}Request;
use App\Containers\AppSection\{Container}\UI\API\Transformers\{Model}Transformer;
use App\Containers\AppSection\{Container}\UI\API\Transporters\{Action}Transporter;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class {Action}Controller extends ApiController
{
    public function __invoke({Action}Request $request, {Action}Action $action): JsonResponse
    {
        $transporter = {Action}Transporter::fromRequest($request);
        $result = $action->run($transporter);
        
        return Response::create($result, {Model}Transformer::class)->ok();
    }
}
```

---

## 5. Tiến Độ Thực Hiện

### ✅ Đã Hoàn Thành

| Phase | Mô tả | Trạng thái |
|-------|-------|------------|
| Phase 1 | Chuẩn hóa Requests theo Apiato 13.x | ✅ 168+ files |
| - | Xóa `$access` property (deprecated) | ✅ Done |
| - | Xóa `$urlParameters` property (deprecated) | ✅ Done |
| - | Chuẩn hóa `authorize()` với `can()` | ✅ Done |
| Phase 2 | Thêm Transporters Layer (Pragmatic) | ✅ Done (Blog, Member) |
| Phase 3 | Refactor Controllers | ✅ Done |
| Phase 4 | Chuẩn hóa Hash IDs (Base Transformer) | ✅ Done |

### 🔄 Đang Thực Hiện / Chờ Xử Lý

| Phase | Mô tả | Trạng thái |
|-------|-------|------------|
| Phase 5 | Thêm Seeders/Factories/Tests | ⏳ Pending (Gallery, CustomField) |

---

## 6. Kết Luận

Dự án **api-cms-flashtech** đã được cập nhật lớn về kiến trúc để tuân thủ **Apiato 13.x** và **Clean Code**:

### ✅ Đã Hoàn Thành:
1. **Request Classes**: Refactor toàn bộ 168+ files (chuẩn Apiato 13.x Authorization & Routing).
2. **Transporter Pattern**: Áp dụng thành công cho các module phức tạp (Blog, Member), giúp tách biệt IO và Business Logic.
3. **Controller Cleanup**: Loại bỏ logic xử lý mảng trong Controller, chuyển sang DTO (Transporter).
4. **Security & Standard**: Chuẩn hóa Hash ID output trong Transformers, loại bỏ duplicate code, đảm bảo an toàn ID enumeration.

### 📝 Script Refactor:
```bash
php scripts/refactor-requests-apiato13.php
```

### ⚠️ Lưu ý tồn đọng:
- Cần bổ sung Unit Tests cho `Gallery` và `CustomField` container ở Phase tiếp theo.

**Cập nhật lần cuối:** 2026-02-09

