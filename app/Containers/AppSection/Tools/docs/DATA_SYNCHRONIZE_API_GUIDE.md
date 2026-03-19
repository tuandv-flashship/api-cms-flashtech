# Data Synchronize — FE Integration Guide

> **Base URL**: `{{API_URL}}/v1/tools/data-synchronize`
>
> **Auth**: `Authorization: Bearer {token}` — tất cả endpoints.
>
> **i18n**: Header `X-Locale: vi` hoặc `X-Locale: en` (optional).

---

## 1. URL Pattern

| Action           | Method | URL                               |
| ---------------- | ------ | --------------------------------- |
| List Types       | GET    | `/types`                          |
| Schema           | GET    | `/schema/{type}`                  |
| Export           | POST   | `/export/{type}`                  |
| Upload           | POST   | `/upload`                         |
| Validate         | POST   | `/import/{type}/validate`         |
| Import           | POST   | `/import/{type}`                  |
| Download Example | POST   | `/import/{type}/download-example` |

**`{type}`**: `posts` · `pages` · `post-translations` · `page-translations` · `other-translations`

---

## 2. List Types

```http
GET /v1/tools/data-synchronize/types
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

## 3. Schema

```http
GET /v1/tools/data-synchronize/schema/{type}
```

**Response `200`**:

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
        { "key": "status", "label": "Trạng thái" }
      ],
      "filters": [
        { "key": "status", "type": "select", "label": "Trạng thái", "options": [...] },
        { "key": "limit", "type": "number", "label": "Giới hạn" }
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
        "rows": [{ "name": "Mẫu", "slug": "mau", "status": "published" }]
      },
      "formats": ["csv", "xlsx"]
    }
  }
}
```

---

## 4. Export

```http
POST /v1/tools/data-synchronize/export/{type}
Content-Type: application/json

{
  "format": "csv",
  "columns": ["name", "description", "status"],
  "status": "published",
  "limit": 100
}
```

**Response**: Binary file download (CSV/XLSX).

```js
async function exportData(type, params) {
    const res = await fetch(`${BASE}/export/${type}`, {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
        },
        body: JSON.stringify(params),
    });
    const blob = await res.blob();
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = `export-${type}.${params.format || "csv"}`;
    a.click();
}
```

---

## 5. Import Flow (chi tiết từng bước)

```
Upload → Validate (loop chunk) → Import (loop chunk)
```

---

### Bước 0: Download file mẫu (optional)

```http
POST /v1/tools/data-synchronize/import/{type}/download-example
Content-Type: application/json

{ "format": "csv" }
```

**Response**: Binary file download.

---

### Bước 1: Upload file

```http
POST /v1/tools/data-synchronize/upload
Content-Type: multipart/form-data

file: (binary)
```

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

> ⚠️ Lưu `data.file_name` — dùng cho bước 2 và 3.

---

### Bước 2: Validate theo chunk

#### Request

```http
POST /v1/tools/data-synchronize/import/{type}/validate
Content-Type: application/json

{
  "file_name": "tmp_import_67d93a2b.csv",
  "offset": 0,
  "limit": 100,
  "total": null
}
```

| Field       | Type    | Required | Mô tả                                                   |
| ----------- | ------- | -------- | ------------------------------------------------------- |
| `file_name` | string  | **Yes**  | Tên file từ bước Upload                                 |
| `offset`    | integer | **Yes**  | Vị trí bắt đầu. **Lần đầu = `0`**                       |
| `limit`     | integer | **Yes**  | Số rows/chunk. **Khuyến nghị = `100`**                  |
| `total`     | integer | No       | Tổng rows. **Lần đầu = `null`**, sau đó lấy từ response |

#### Response `200`

```json
{
    "data": {
        "file_name": "tmp_import_67d93a2b.csv",
        "offset": 8,
        "count": 8,
        "total": 8,
        "errors": []
    },
    "message": "Đang xác thực từ 1 đến 8..."
}
```

| Response field | Type    | Mô tả                                                   |
| -------------- | ------- | ------------------------------------------------------- |
| `offset`       | integer | **Offset cho chunk TIẾP THEO** — gửi lại cho request kế |
| `count`        | integer | Số rows đã validate trong chunk này                     |
| `total`        | integer | Tổng rows trong file                                    |
| `errors`       | array   | Mảng lỗi (rỗng = hợp lệ)                                |
| `message`      | string  | i18n progress message                                   |

#### Khi có lỗi validation

```json
{
    "data": {
        "offset": 8,
        "count": 8,
        "total": 8,
        "errors": [
            {
                "row": 1,
                "attribute": "name",
                "errors": ["Trường name là bắt buộc."]
            },
            {
                "row": 3,
                "attribute": "is_featured",
                "errors": ["Trường is_featured phải là true hoặc false."]
            }
        ]
    },
    "message": "Đang xác thực từ 1 đến 8..."
}
```

| Error field | Type    | Mô tả                                     |
| ----------- | ------- | ----------------------------------------- |
| `row`       | integer | Số dòng lỗi (1-indexed, tính từ đầu file) |
| `attribute` | string  | Tên cột bị lỗi                            |
| `errors`    | array   | Danh sách mô tả lỗi dạng string           |

#### Điều kiện dừng

```
response.offset >= response.total  →  ĐÃ HẾT FILE → dừng
response.offset <  response.total  →  CÒN DATA    → gọi tiếp
```

#### Ví dụ step-by-step (file 250 rows, chunk 100)

| Lần | Request `offset` | Response `offset` | Response `count` | `total` | Trạng thái           |
| --- | ---------------- | ----------------- | ---------------- | ------- | -------------------- |
| 1   | `0`              | `100`             | `100`            | `250`   | Tiếp tục (100 < 250) |
| 2   | `100`            | `200`             | `100`            | `250`   | Tiếp tục (200 < 250) |
| 3   | `200`            | `250`             | `50`             | `250`   | **Dừng** (250 ≥ 250) |

#### FE code

```js
async function validateImport(type, fileName, onProgress) {
    let offset = 0;
    const limit = 100;
    let total = null;
    const allErrors = [];

    while (true) {
        const res = await api.post(`/import/${type}/validate`, {
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

        // Progress: (offset_mới / total) * 100
        onProgress?.(Math.round((d.offset / d.total) * 100));

        if (d.offset >= d.total) break; // Hết file
        offset = d.offset; // Chunk tiếp theo
    }

    return { total, errors: allErrors };
}
```

---

### Bước 3: Import theo chunk

#### Request

```http
POST /v1/tools/data-synchronize/import/{type}
Content-Type: application/json

{
  "file_name": "tmp_import_67d93a2b.csv",
  "offset": 0,
  "limit": 100
}
```

| Field       | Type    | Required | Mô tả                             |
| ----------- | ------- | -------- | --------------------------------- |
| `file_name` | string  | **Yes**  | Tên file từ bước Upload           |
| `offset`    | integer | **Yes**  | Vị trí bắt đầu. **Lần đầu = `0`** |
| `limit`     | integer | **Yes**  | Số rows/chunk                     |

> **Lưu ý**: Import KHÔNG có field `total` — chỉ Validate mới cần.

#### Response `200`

```json
{
  "data": {
    "offset": 100,
    "count": 100,
    "imported": 95,
    "failures_count": 5,
    "failures": [
      {
        "row": 3,
        "attribute": "name",
        "errors": ["Trường name là bắt buộc."]
      },
      {
        "row": 7,
        "attribute": "status",
        "errors": ["Giá trị status không hợp lệ."]
      }
    ]
  },
  "message": "Đang nhập từ 1 đến 100..."
}
```

| Response field    | Type    | Mô tả                                             |
| ----------------- | ------- | -------------------------------------------------- |
| `offset`          | integer | **Offset cho chunk TIẾP THEO**                     |
| `count`           | integer | Số rows xử lý trong chunk                         |
| `imported`        | integer | Rows import **thành công**                         |
| `failures_count`  | integer | Số rows import **thất bại**                        |
| `failures`        | array   | Chi tiết từng row lỗi (rỗng nếu không có lỗi)     |
| `message`         | string  | i18n progress message                              |

**Failure item:**

| Field       | Type    | Mô tả                                |
| ----------- | ------- | ------------------------------------- |
| `row`       | integer | Số dòng lỗi (1-indexed, từ đầu file) |
| `attribute` | string  | Tên cột bị lỗi                       |
| `errors`    | array   | Danh sách mô tả lỗi                  |

#### Điều kiện dừng

```
response.count === 0            →  KHÔNG CÒN ROW → dừng
response.offset >= total        →  HẾT FILE      → dừng
```

#### Ví dụ step-by-step (file 250 rows, chunk 100)

| Lần | Request `offset` | Response                                          | Trạng thái           |
| --- | ---------------- | ------------------------------------------------- | -------------------- |
| 1   | `0`              | `offset=100, count=100, imported=98, failures=2`  | Tiếp (100 < 250)     |
| 2   | `100`            | `offset=200, count=100, imported=100, failures=0` | Tiếp (200 < 250)     |
| 3   | `200`            | `offset=250, count=50, imported=50, failures=0`   | **Dừng** (250 ≥ 250) |

**Kết quả**: `imported=248, failures=2`

#### FE code

```js
async function importData(type, fileName, total, onProgress) {
    let offset = 0;
    const limit = 100;
    let imported = 0;
    let failuresCount = 0;
    const allFailures = [];

    while (offset < total) {
        const res = await api.post(`/import/${type}`, {
            file_name: fileName,
            offset,
            limit,
        });

        const d = res.data.data;
        imported += d.imported;
        failuresCount += d.failures_count;

        if (d.failures.length > 0) {
            allFailures.push(...d.failures);
        }

        onProgress?.(Math.round((d.offset / total) * 100));

        if (d.count === 0) break;
        offset = d.offset;
    }

    return { imported, failuresCount, failures: allFailures };
}
```

---

### Complete Flow

```js
async function fullImportFlow(type, file) {
    const BASE = "/v1/tools/data-synchronize";

    // ══ Bước 1: Upload ══
    const formData = new FormData();
    formData.append("file", file);
    const upload = await api.post(`${BASE}/upload`, formData);
    const fileName = upload.data.data.file_name;

    // ══ Bước 2: Validate ══
    const { total, errors } = await validateImport(type, fileName, (p) => {
        updateProgress("validate", p); // 0-100
    });

    if (errors.length > 0) {
        showValidationErrors(errors); // Hiển thị bảng: row | attribute | errors
        return;
    }

    // ══ Bước 3: Import ══
    const result = await importData(type, fileName, total, (p) => {
        updateProgress("import", p);
    });

    showResult(
        `Hoàn tất: ${result.imported}/${total} thành công, ${result.failuresCount} thất bại`,
    );

    // Hiển thị bảng failures nếu có
    if (result.failures.length > 0) {
        showFailureTable(result.failures);
        // failures: [{ row: 3, attribute: "name", errors: ["..."] }]
    }
}
```

---

## 6. Error Handling

| HTTP  | Ý nghĩa                | FE xử lý             |
| ----- | ---------------------- | -------------------- |
| `401` | Chưa đăng nhập         | Redirect login       |
| `403` | Không có quyền         | "Bạn không có quyền" |
| `404` | Type không tồn tại     | Hiển thị lỗi         |
| `422` | Validation lỗi request | Hiển thị `errors`    |
| `500` | Server error           | "Lỗi hệ thống"       |

---

## 7. Permissions

| Type                 | Export                      | Import                      |
| -------------------- | --------------------------- | --------------------------- |
| `posts`              | `posts.export`              | `posts.import`              |
| `pages`              | `pages.export`              | `pages.import`              |
| `post-translations`  | `post-translations.export`  | `post-translations.import`  |
| `page-translations`  | `page-translations.export`  | `page-translations.import`  |
| `other-translations` | `other-translations.export` | `other-translations.import` |

> Schema/Types: chỉ cần auth, không cần permission.
