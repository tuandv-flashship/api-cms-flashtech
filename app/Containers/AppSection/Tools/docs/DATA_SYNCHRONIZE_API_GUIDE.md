# Data Synchronize — FE Integration Guide

> **Base URL**: `{{API_URL}}/v1/tools/data-synchronize`
>
> **Auth**: Tất cả endpoints yêu cầu `Authorization: Bearer {token}`.
>
> **i18n**: Gửi header `X-Locale: vi` hoặc `X-Locale: en` để nhận labels/descriptions theo ngôn ngữ tương ứng.

---

## Mục lục

1. [URL Pattern](#1-url-pattern)
2. [Discovery — List Types](#2-discovery--list-types)
3. [Schema — Metadata cho Form](#3-schema--metadata-cho-form)
4. [Export Flow](#4-export-flow)
5. [Import Flow (chi tiết)](#5-import-flow-chi-tiết)
   - [Bước 0: Download file mẫu](#bước-0-download-file-mẫu)
   - [Bước 1: Upload file](#bước-1-upload-file)
   - [Bước 2: Validate theo chunk](#bước-2-validate-theo-chunk)
   - [Bước 3: Import theo chunk](#bước-3-import-theo-chunk)
   - [Complete flow code](#complete-flow-code)
6. [Error Handling](#6-error-handling)
7. [Permissions](#7-permissions)

---

## 1. URL Pattern

Tất cả routes dùng `{type}` trực tiếp — không cần path mapping:

| Action | Method | URL |
|---|---|---|
| List Types | GET | `/types` |
| Schema | GET | `/schema/{type}` |
| Export | POST | `/export/{type}` |
| Upload | POST | `/upload` |
| Validate | POST | `/import/{type}/validate` |
| Import | POST | `/import/{type}` |
| Download Example | POST | `/import/{type}/download-example` |

**`{type}`**: `posts` · `pages` · `post-translations` · `page-translations` · `other-translations`

---

## 2. Discovery — List Types

```http
GET /v1/tools/data-synchronize/types
Authorization: Bearer {token}
X-Locale: vi
```

**Response `200`**:
```json
{
  "data": [
    {
      "type": "posts",
      "label": "Bài viết",
      "total": 22,
      "export_description": "Xuất bài viết sang tệp CSV/Excel.",
      "import_description": "Nhập bài viết từ tệp CSV/Excel."
    }
  ]
}
```

---

## 3. Schema — Metadata cho Form

```http
GET /v1/tools/data-synchronize/schema/{type}
Authorization: Bearer {token}
X-Locale: vi
```

**Response `200`** (ví dụ `posts`):
```json
{
  "data": {
    "type": "posts",
    "label": "Bài viết",
    "export": {
      "description": "Xuất bài viết sang tệp CSV/Excel.",
      "total": 22,
      "columns": [
        { "key": "name", "label": "Tên" },
        { "key": "description", "label": "Mô tả" },
        { "key": "status", "label": "Trạng thái" }
      ],
      "filters": [
        { "key": "status", "type": "select", "label": "Trạng thái", "options": [
          { "value": "published", "label": "Published" },
          { "value": "draft", "label": "Draft" }
        ]},
        { "key": "start_date", "type": "date", "label": "Từ ngày" },
        { "key": "limit", "type": "number", "label": "Giới hạn", "placeholder": "Để trống = tất cả" }
      ],
      "formats": ["csv", "xlsx"]
    },
    "import": {
      "description": "Nhập bài viết từ tệp CSV/Excel.",
      "chunk_size": 50,
      "columns": [
        { "key": "name", "label": "Tên", "required": true, "rule_description": "Bắt buộc, tối đa 255 ký tự" }
      ],
      "examples": {
        "headers": ["Name", "Slug", "Status"],
        "rows": [{ "name": "Bài viết mẫu", "slug": "bai-viet-mau", "status": "published" }]
      },
      "formats": ["csv", "xlsx"]
    }
  }
}
```

> **Lưu ý**: `import.chunk_size` là số rows khuyến nghị mỗi chunk (dùng cho `limit`).

---

## 4. Export Flow

```http
POST /v1/tools/data-synchronize/export/{type}
Authorization: Bearer {token}
Content-Type: application/json

{
  "format": "csv",
  "columns": ["name", "description", "status"],
  "status": "published",
  "limit": 100
}
```

**Response**: Binary file download.

```js
// FE download file
async function exportData(type, params) {
  const res = await fetch(`${BASE_URL}/export/${type}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(params),
  });

  const blob = await res.blob();
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `export-${type}.${params.format || 'csv'}`;
  a.click();
  URL.revokeObjectURL(url);
}
```

---

## 5. Import Flow (chi tiết)

Import gồm **4 bước tuần tự**:

```
┌─────────────────┐    ┌──────────────┐    ┌────────────────────┐    ┌──────────────────┐
│  0. Download     │    │  1. Upload   │    │  2. Validate       │    │  3. Import        │
│  file mẫu        │ →  │  file        │ →  │  theo chunk        │ →  │  theo chunk       │
│  (optional)      │    │  → file_name │    │  → kiểm tra lỗi    │    │  → xử lý data     │
└─────────────────┘    └──────────────┘    └────────────────────┘    └──────────────────┘
```

---

### Bước 0: Download file mẫu

> Optional — cho user tải file mẫu để biết format cần import.

```http
POST /v1/tools/data-synchronize/import/{type}/download-example
Authorization: Bearer {token}
Content-Type: application/json

{ "format": "csv" }
```

| Field | Type | Required | Mô tả |
|---|---|---|---|
| `format` | string | **Yes** | `csv` hoặc `xlsx` |

**Response**: Binary file download (CSV/XLSX có header + sample data).

---

### Bước 1: Upload file

> Upload file lên server. Server lưu tạm và trả về `file_name` dùng cho bước 2-3.

```http
POST /v1/tools/data-synchronize/upload
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: (chọn file CSV/XLSX)
```

| Field | Type | Required | Mô tả |
|---|---|---|---|
| `file` | file | **Yes** | File CSV hoặc XLSX |

**Response `200`**:
```json
{
  "data": {
    "file_name": "tmp_import_67d93a2b.csv",
    "original_name": "my-posts.csv",
    "size": 15234,
    "mime_type": "text/csv"
  }
}
```

| Response field | Type | Mô tả |
|---|---|---|
| `file_name` | string | **Tên file trên server** — dùng cho bước 2 và 3 |
| `original_name` | string | Tên file gốc user upload |
| `size` | integer | Kích thước file (bytes) |
| `mime_type` | string | MIME type |

> ⚠️ **QUAN TRỌNG**: Lưu lại `file_name` — đây là key để xác định file trong các bước tiếp theo.

---

### Bước 2: Validate theo chunk

> Validate file **trước khi import** để phát hiện lỗi. Gọi **lặp đi lặp lại** cho đến khi hết file.

#### 2.1. Request

```http
POST /v1/tools/data-synchronize/import/{type}/validate
Authorization: Bearer {token}
Content-Type: application/json

{
  "file_name": "tmp_import_67d93a2b.csv",
  "offset": 0,
  "limit": 100,
  "total": null
}
```

| Field | Type | Required | Mô tả |
|---|---|---|---|
| `file_name` | string | **Yes** | Tên file từ bước 1 (`data.file_name`) |
| `offset` | integer | **Yes** | Vị trí row bắt đầu (0-indexed). **Lần đầu gửi `0`** |
| `limit` | integer | **Yes** | Số rows validate mỗi lần. **Khuyến nghị: `100`** |
| `total` | integer | No | Tổng rows trong file. **Lần đầu gửi `null`**, các lần sau gửi giá trị từ response |

#### 2.2. Response `200`

**Trường hợp KHÔNG có lỗi:**
```json
{
  "data": {
    "file_name": "tmp_import_67d93a2b.csv",
    "offset": 100,
    "count": 100,
    "total": 250,
    "errors": []
  }
}
```

**Trường hợp CÓ lỗi:**
```json
{
  "data": {
    "file_name": "tmp_import_67d93a2b.csv",
    "offset": 100,
    "count": 100,
    "total": 250,
    "errors": [
      {
        "row": 5,
        "attribute": "name",
        "errors": ["Trường name là bắt buộc."]
      },
      {
        "row": 12,
        "attribute": "status",
        "errors": ["Giá trị status không hợp lệ."]
      }
    ]
  }
}
```

| Response field | Type | Mô tả |
|---|---|---|
| `file_name` | string | Tên file (gửi lại cho request tiếp) |
| `offset` | integer | **Offset cho chunk TIẾP THEO** — dùng làm `offset` cho request kế |
| `count` | integer | Số rows đã validate trong chunk này |
| `total` | integer | **Tổng số rows trong file** — lưu lại dùng cho các request sau |
| `errors` | array | Mảng lỗi. **Rỗng = chunk này hợp lệ** |

#### 2.3. Cách xác định khi nào DỪNG

```
Nếu response.offset >= response.total → ĐÃ HẾT FILE → dừng validate
Nếu response.offset < response.total  → CÒN DATA   → gọi tiếp với offset mới
```

#### 2.4. FE validate loop

```js
/**
 * Validate file import theo chunk.
 * @param {string} type       - Loại import: 'posts', 'pages', ...
 * @param {string} fileName   - Tên file từ bước Upload
 * @param {Function} onProgress - Callback cập nhật progress (0-100)
 * @returns {{ total: number, errors: Array }}
 */
async function validateImport(type, fileName, onProgress) {
  let offset = 0;
  const limit = 100;       // Số rows mỗi chunk
  let total = null;         // Null lần đầu, server sẽ trả về
  const allErrors = [];

  while (true) {
    const res = await api.post(
      `/v1/tools/data-synchronize/import/${type}/validate`,
      {
        file_name: fileName,
        offset: offset,
        limit: limit,
        total: total,       // Lần đầu null, sau đó gửi lại giá trị từ response
      }
    );

    const data = res.data.data;

    // Lưu total (chỉ cần set 1 lần, nhưng gửi lại mỗi request)
    total = data.total;

    // Thu thập lỗi
    if (data.errors.length > 0) {
      allErrors.push(...data.errors);
    }

    // Cập nhật progress bar
    const progress = Math.min(100, Math.round((data.offset / data.total) * 100));
    onProgress?.(progress);

    // Kiểm tra đã hết file chưa
    if (data.offset >= data.total) {
      break;  // ✅ Hết file
    }

    // Chuyển sang chunk tiếp theo
    offset = data.offset;
  }

  return { total, errors: allErrors };
}
```

#### 2.5. Ví dụ step-by-step (file 250 rows, chunk 100)

| Lần gọi | Request | Response | Trạng thái |
|---|---|---|---|
| 1 | `offset=0, limit=100, total=null` | `offset=100, count=100, total=250, errors=[]` | Đã validate rows 1-100 |
| 2 | `offset=100, limit=100, total=250` | `offset=200, count=100, total=250, errors=[{row:150,...}]` | Row 150 lỗi |
| 3 | `offset=200, limit=100, total=250` | `offset=250, count=50, total=250, errors=[]` | **offset(250) >= total(250) → DỪNG** |

**Kết quả**: `total=250`, `errors=[{row:150, attribute:"name", errors:["..."]}]`

---

### Bước 3: Import theo chunk

> Sau khi validate xong (không lỗi hoặc user chấp nhận), tiến hành import thực tế.

#### 3.1. Request

```http
POST /v1/tools/data-synchronize/import/{type}
Authorization: Bearer {token}
Content-Type: application/json

{
  "file_name": "tmp_import_67d93a2b.csv",
  "offset": 0,
  "limit": 100
}
```

| Field | Type | Required | Mô tả |
|---|---|---|---|
| `file_name` | string | **Yes** | Tên file từ bước Upload (cùng file đã validate) |
| `offset` | integer | **Yes** | Vị trí row bắt đầu. **Lần đầu gửi `0`** |
| `limit` | integer | **Yes** | Số rows import mỗi lần. **Khuyến nghị: `100`** |

> Lưu ý: Import **KHÔNG có** field `total` — chỉ Validate mới cần.

#### 3.2. Response `200`

```json
{
  "data": {
    "offset": 100,
    "count": 100,
    "imported": 95,
    "failures": 5
  }
}
```

| Response field | Type | Mô tả |
|---|---|---|
| `offset` | integer | **Offset cho chunk TIẾP THEO** |
| `count` | integer | Số rows đã xử lý trong chunk này |
| `imported` | integer | Số rows import **thành công** |
| `failures` | integer | Số rows import **thất bại** |

#### 3.3. Cách xác định khi nào DỪNG

```
Nếu response.count === 0         → KHÔNG CÒN DATA → dừng
Nếu response.offset >= total     → ĐÃ HẾT FILE   → dừng
Ngược lại                         → gọi tiếp với offset mới
```

> `total` ở đây dùng giá trị đã lưu từ bước Validate.

#### 3.4. FE import loop

```js
/**
 * Import data theo chunk.
 * @param {string} type         - Loại import
 * @param {string} fileName     - Tên file từ bước Upload
 * @param {number} total        - Tổng rows (lấy từ bước Validate)
 * @param {Function} onProgress - Callback progress (0-100)
 * @returns {{ imported: number, failures: number }}
 */
async function importData(type, fileName, total, onProgress) {
  let offset = 0;
  const limit = 100;
  let totalImported = 0;
  let totalFailures = 0;

  while (offset < total) {
    const res = await api.post(
      `/v1/tools/data-synchronize/import/${type}`,
      {
        file_name: fileName,
        offset: offset,
        limit: limit,
      }
    );

    const data = res.data.data;

    totalImported += data.imported;
    totalFailures += data.failures;

    // Cập nhật progress bar
    const progress = Math.min(100, Math.round((data.offset / total) * 100));
    onProgress?.(progress);

    // Kiểm tra dừng
    if (data.count === 0) {
      break;  // Không còn rows
    }

    offset = data.offset;
  }

  return { imported: totalImported, failures: totalFailures };
}
```

#### 3.5. Ví dụ step-by-step (file 250 rows, chunk 100)

| Lần gọi | Request | Response | Trạng thái |
|---|---|---|---|
| 1 | `offset=0, limit=100` | `offset=100, count=100, imported=98, failures=2` | 98 thành công, 2 lỗi |
| 2 | `offset=100, limit=100` | `offset=200, count=100, imported=100, failures=0` | 100 thành công |
| 3 | `offset=200, limit=100` | `offset=250, count=50, imported=50, failures=0` | **offset(250) >= total(250) → DỪNG** |

**Kết quả**: `imported=248`, `failures=2`

---

### Complete flow code

```js
/**
 * Complete import flow: Upload → Validate → Import
 * @param {string} type  - 'posts' | 'pages' | 'post-translations' | ...
 * @param {File}   file  - File object từ <input type="file">
 */
async function fullImportFlow(type, file) {
  const BASE = '/v1/tools/data-synchronize';

  // ═══════════════════════════════════════════
  //  BƯỚC 1: Upload file
  // ═══════════════════════════════════════════
  updateStatus('Đang tải file lên...');

  const formData = new FormData();
  formData.append('file', file);

  const uploadRes = await api.post(`${BASE}/upload`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });

  const fileName = uploadRes.data.data.file_name;
  // → "tmp_import_67d93a2b.csv"

  // ═══════════════════════════════════════════
  //  BƯỚC 2: Validate theo chunk
  // ═══════════════════════════════════════════
  updateStatus('Đang kiểm tra dữ liệu...');

  let offset = 0;
  let total = null;
  const allErrors = [];
  const limit = 100;

  while (true) {
    const res = await api.post(`${BASE}/import/${type}/validate`, {
      file_name: fileName,
      offset,
      limit,
      total,
    });

    const d = res.data.data;
    total = d.total;

    if (d.errors.length > 0) {
      allErrors.push(...d.errors);
    }

    updateProgress('validate', d.offset, d.total);

    if (d.offset >= d.total) break;
    offset = d.offset;
  }

  // Kiểm tra lỗi
  if (allErrors.length > 0) {
    showValidationErrors(allErrors);
    // Hiển thị bảng lỗi: row, attribute, errors
    // User có thể sửa file và upload lại
    return { success: false, errors: allErrors };
  }

  // ═══════════════════════════════════════════
  //  BƯỚC 3: Import theo chunk
  // ═══════════════════════════════════════════
  updateStatus('Đang import dữ liệu...');

  offset = 0;
  let imported = 0;
  let failures = 0;

  while (offset < total) {
    const res = await api.post(`${BASE}/import/${type}`, {
      file_name: fileName,
      offset,
      limit,
    });

    const d = res.data.data;
    imported += d.imported;
    failures += d.failures;

    updateProgress('import', d.offset, total);

    if (d.count === 0) break;
    offset = d.offset;
  }

  // ═══════════════════════════════════════════
  //  KẾT QUẢ
  // ═══════════════════════════════════════════
  showResult({
    total,
    imported,
    failures,
    message: `Import hoàn tất: ${imported}/${total} thành công, ${failures} thất bại`,
  });

  return { success: true, total, imported, failures };
}

// ── Helper functions ──

function updateStatus(message) {
  // Cập nhật UI status text
}

function updateProgress(phase, current, total) {
  const percent = Math.min(100, Math.round((current / total) * 100));
  // Cập nhật progress bar
  // phase = 'validate' | 'import'
}

function showValidationErrors(errors) {
  // Hiển thị bảng lỗi:
  // errors.forEach(e => console.log(`Row ${e.row}: ${e.attribute} - ${e.errors.join(', ')}`));
}

function showResult(result) {
  // Hiển thị kết quả import
}
```

---

## 6. Error Handling

| HTTP | Ý nghĩa | FE xử lý |
|---|---|---|
| `401` | Chưa đăng nhập | Redirect login |
| `403` | Không có quyền | Hiển thị "Bạn không có quyền" |
| `404` | Type không tồn tại | Hiển thị lỗi |
| `422` | Validation lỗi | Hiển thị `message` + `errors` |
| `500` | Server error | Hiển thị "Lỗi hệ thống, vui lòng thử lại" |

**422 response format:**
```json
{
  "message": "The file name field is required.",
  "errors": {
    "file_name": ["The file name field is required."],
    "offset": ["The offset field is required."]
  }
}
```

---

## 7. Permissions

| Type | Export | Import |
|---|---|---|
| `posts` | `posts.export` | `posts.import` |
| `pages` | `pages.export` | `pages.import` |
| `post-translations` | `post-translations.export` | `post-translations.import` |
| `page-translations` | `page-translations.export` | `page-translations.import` |
| `other-translations` | `other-translations.export` | `other-translations.import` |

> Schema/Types endpoints chỉ cần auth, không cần permission cụ thể.
