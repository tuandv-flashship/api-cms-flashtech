# Data Synchronize — FE Integration Guide

> **Base URL**: `{API_BASE}/v1/tools/data-synchronize`
>
> **Auth**: Tất cả endpoints yêu cầu `Authorization: Bearer {token}` header.

---

## Mục lục

1. [Tổng quan Integration Flow](#1-tổng-quan-integration-flow)
2. [Discovery: Danh sách Types](#2-discovery-danh-sách-types)
3. [Schema: Metadata cho Form](#3-schema-metadata-cho-form)
4. [Export: Xuất dữ liệu](#4-export-xuất-dữ-liệu)
5. [Import Flow: Nhập dữ liệu](#5-import-flow-nhập-dữ-liệu)
6. [Error Handling](#6-error-handling)
7. [Permissions Matrix](#7-permissions-matrix)

---

## 1. Tổng quan Integration Flow

### Export Flow
```
FE load schema → User chọn columns/filters → POST export → Download file
```

### Import Flow (3 bước)
```
1. Upload file     → nhận file_name
2. Validate file   → kiểm tra lỗi
3. Import data     → xử lý import theo chunk
```

```mermaid
flowchart LR
    A[GET /types] --> B[GET /schema/:type]
    B --> C{Export hay Import?}
    C -->|Export| D[POST /export/:type]
    C -->|Import| E[POST /upload]
    E --> F[POST /import/:path/validate]
    F -->|Lặp chunks| F
    F -->|Hết chunk| G[POST /import/:path]
    G -->|Lặp chunks| G
```

---

## 2. Discovery: Danh sách Types

Lấy tất cả types có sẵn để render navigation/sidebar.

### Request
```http
GET /v1/tools/data-synchronize/types
Authorization: Bearer {token}
```

### Response `200 OK`
```json
{
  "data": [
    {
      "type": "posts",
      "label": "Posts",
      "total": 22
    },
    {
      "type": "pages",
      "label": "Pages",
      "total": 8
    },
    {
      "type": "post-translations",
      "label": "Post Translations",
      "total": 22
    },
    {
      "type": "page-translations",
      "label": "Page Translations",
      "total": 8
    },
    {
      "type": "other-translations",
      "label": "Other Translations",
      "total": 0
    }
  ]
}
```

> **Lưu ý**: `total` cho other-translations trả về 0 vì dữ liệu từ file, không từ DB.

---

## 3. Schema: Metadata cho Form

Lấy chi tiết schema để render form export/import cho 1 type cụ thể.

### Request
```http
GET /v1/tools/data-synchronize/schema/{type}
Authorization: Bearer {token}
```

`{type}` = `posts` | `pages` | `post-translations` | `page-translations` | `other-translations`

### Response `200 OK` — Ví dụ `schema/posts`
```json
{
  "data": {
    "type": "posts",
    "label": "Posts",
    "export": {
      "total": 22,
      "columns": [
        { "key": "name", "label": "Name" },
        { "key": "description", "label": "Description" },
        { "key": "content", "label": "Content" },
        { "key": "is_featured", "label": "Is Featured" },
        { "key": "format_type", "label": "Format Type" },
        { "key": "image", "label": "Image" },
        { "key": "views", "label": "Views" },
        { "key": "slug", "label": "Slug" },
        { "key": "url", "label": "URL" },
        { "key": "status", "label": "Status" },
        { "key": "categories", "label": "Categories" },
        { "key": "tags", "label": "Tags" }
      ],
      "filters": [
        {
          "key": "limit",
          "type": "number",
          "label": "Limit",
          "placeholder": "Leave empty to export all"
        },
        {
          "key": "status",
          "type": "select",
          "label": "Status",
          "options": [
            { "value": "published", "label": "Published" },
            { "value": "draft", "label": "Draft" },
            { "value": "pending", "label": "Pending" }
          ]
        },
        {
          "key": "is_featured",
          "type": "select",
          "label": "Is Featured",
          "options": [
            { "value": "1", "label": "Yes" },
            { "value": "0", "label": "No" }
          ]
        },
        {
          "key": "category_id",
          "type": "select",
          "label": "Category",
          "options": [
            { "value": "hashed_id_abc", "label": "Tin tức" },
            { "value": "hashed_id_def", "label": "Sức khỏe" }
          ]
        },
        {
          "key": "start_date",
          "type": "date",
          "label": "Start Date"
        },
        {
          "key": "end_date",
          "type": "date",
          "label": "End Date"
        }
      ],
      "formats": ["csv", "xlsx"]
    },
    "import": {
      "chunk_size": 50,
      "columns": [
        {
          "key": "name",
          "label": "Name",
          "required": true,
          "rule_description": "The Name field is required and must be a string of up to 120 characters."
        },
        {
          "key": "slug",
          "label": "Slug",
          "required": false,
          "rule_description": "The Slug field must be a string of up to 250 characters; may be left blank."
        },
        {
          "key": "description",
          "label": "Description",
          "required": false,
          "rule_description": "The Description field must be a string of up to 400 characters; may be left blank."
        },
        {
          "key": "content",
          "label": "Content",
          "required": false,
          "rule_description": "The Content field must be a string of up to 300,000 characters; may be left blank."
        },
        {
          "key": "status",
          "label": "Status",
          "required": false,
          "rule_description": "The Status field must be one of the allowed values; may be left blank."
        }
      ],
      "examples": {
        "headers": ["Name", "Slug", "Description", "Content", "Image", "Template", "Status"],
        "rows": [
          {
            "name": "Homepage",
            "slug": "homepage",
            "description": null,
            "content": "<div>[featured-posts]</div>...",
            "image": null,
            "template": "no-sidebar",
            "status": "published"
          },
          {
            "name": "Blog",
            "slug": "blog",
            "description": "---",
            "content": null,
            "image": null,
            "template": null,
            "status": "published"
          }
        ]
      },
      "formats": ["csv", "xlsx"]
    }
  }
}
```

### Response `404` — Type không tồn tại
```json
{ "message": "Unknown type: invalid-type" }
```

### Cách render Export form từ schema

#### Columns → Checkbox group
```jsx
// Render checkbox cho mỗi column
schema.export.columns.map(col => (
  <Checkbox key={col.key} label={col.label} defaultChecked />
))
```

#### Filters → Dynamic form fields
```jsx
schema.export.filters.map(filter => {
  switch (filter.type) {
    case 'select':
      return <Select label={filter.label} options={filter.options} />;
    case 'number':
      return <Input type="number" label={filter.label} placeholder={filter.placeholder} />;
    case 'date':
      return <DatePicker label={filter.label} />;
  }
})
```

#### Formats → Radio group
```jsx
schema.export.formats.map(fmt => (
  <Radio key={fmt} label={fmt.toUpperCase()} value={fmt} />
))
```

### Cách render Import form từ schema

#### Chunk Size → Input (có giá trị mặc định)
```jsx
<Input
  type="number"
  label="Kích thước đoạn"
  defaultValue={schema.import.chunk_size}
  helperText="Số lượng hàng được nhập tại một thời điểm."
/>
```

#### Bảng "Ví dụ" → Table
```jsx
<Table>
  <thead>
    <tr>{schema.import.examples.headers.map(h => <th>{h}</th>)}</tr>
  </thead>
  <tbody>
    {schema.import.examples.rows.map((row, i) => (
      <tr key={i}>
        {schema.import.columns.map(col => <td>{row[col.key] ?? ''}</td>)}
      </tr>
    ))}
  </tbody>
</Table>
```

#### Bảng "Quy tắc" → Table
```jsx
<Table>
  <thead><tr><th>Cột</th><th>Quy tắc</th></tr></thead>
  <tbody>
    {schema.import.columns.map(col => (
      <tr key={col.key}>
        <td>{col.label}</td>
        <td>{col.rule_description}</td>
      </tr>
    ))}
  </tbody>
</Table>
```

---

## 4. Export: Xuất dữ liệu

### Mapping URL cho mỗi type

| Type | Export URL |
|---|---|
| `posts` | `POST /export/posts` |
| `pages` | `POST /export/pages` |
| `post-translations` | `POST /export/translations/model` |
| `page-translations` | `POST /export/translations/page` |
| `other-translations` | `POST /export/other-translations` |

### Request
```http
POST /v1/tools/data-synchronize/export/posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "format": "csv",
  "columns": ["name", "description", "status", "slug"],
  "status": "published",
  "is_featured": 1,
  "category_id": "hashed_id_abc",
  "limit": 100,
  "start_date": "2026-01-01",
  "end_date": "2026-03-18"
}
```

| Field | Type | Required | Description |
|---|---|---|---|
| `format` | string | No | `csv` (default) hoặc `xlsx` |
| `columns` | string[] | No | Mảng column keys muốn export. Bỏ qua = export tất cả |
| Các filter fields | varies | No | Từ `schema.export.filters` |

### Response `200 OK`
- **Content-Type**: `text/csv` hoặc `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- **Content-Disposition**: `attachment; filename="posts-2026-03-18-10-30-00.csv"`
- Body là **binary file** — FE cần download trực tiếp.

### FE Download Example
```js
async function exportData(type, params) {
  const response = await fetch(`${API_BASE}/v1/tools/data-synchronize/export/${getExportPath(type)}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(params),
  });

  // Download file
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = response.headers.get('Content-Disposition')
    ?.split('filename=')[1]?.replace(/"/g, '') || `export.${params.format || 'csv'}`;
  a.click();
  window.URL.revokeObjectURL(url);
}

// Mapping type → export path
function getExportPath(type) {
  const map = {
    'posts': 'posts',
    'pages': 'pages',
    'post-translations': 'translations/model',
    'page-translations': 'translations/page',
    'other-translations': 'other-translations',
  };
  return map[type];
}
```

### Export Pages (tương tự)
```http
POST /v1/tools/data-synchronize/export/pages
Content-Type: application/json

{
  "format": "xlsx",
  "columns": ["name", "content", "template", "status"],
  "status": "published",
  "template": "default",
  "limit": 50,
  "start_date": "2026-01-01"
}
```

### Export Translations (không có filters)
```http
POST /v1/tools/data-synchronize/export/translations/model
Content-Type: application/json

{
  "format": "csv",
  "columns": ["id", "name", "name_vi", "description_vi", "content_vi"]
}
```

---

## 5. Import Flow: Nhập dữ liệu

Import gồm 3 bước tuần tự: **Upload → Validate → Import**

### Mapping URL cho mỗi type

| Type | Import URL | Validate URL | Example URL |
|---|---|---|---|
| `posts` | `/import/posts` | `/import/posts/validate` | `/import/posts/download-example` |
| `pages` | `/import/pages` | `/import/pages/validate` | `/import/pages/download-example` |
| `post-translations` | `/import/translations/model` | `/import/translations/model/validate` | `/import/translations/model/download-example` |
| `page-translations` | `/import/translations/page` | `/import/translations/page/validate` | `/import/translations/page/download-example` |
| `other-translations` | `/import/other-translations` | `/import/other-translations/validate` | `/import/other-translations/download-example` |

---

### Bước 0: Download file mẫu (optional)

```http
POST /v1/tools/data-synchronize/import/posts/download-example
Authorization: Bearer {token}
Content-Type: application/json

{ "format": "csv" }
```

**Response**: Binary file download (CSV/XLSX mẫu có header + sample data).

---

### Bước 1: Upload file

```http
POST /v1/tools/data-synchronize/upload
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: (binary CSV/XLSX file)
```

**Response `200 OK`**:
```json
{
  "data": {
    "file_name": "tmp_import_abc123.csv",
    "original_name": "my-posts.csv",
    "size": 15234,
    "mime_type": "text/csv"
  }
}
```

> **Quan trọng**: Lưu `file_name` — dùng cho bước 2 và 3.

---

### Bước 2: Validate file (theo chunks)

Validate file **trước khi import** để FE hiển thị lỗi cho user.

```http
POST /v1/tools/data-synchronize/import/posts/validate
Authorization: Bearer {token}
Content-Type: application/json

{
  "file_name": "tmp_import_abc123.csv",
  "offset": 0,
  "limit": 100,
  "total": null
}
```

| Field | Type | Required | Description |
|---|---|---|---|
| `file_name` | string | Yes | Tên file từ bước 1 |
| `offset` | integer | Yes | Vị trí bắt đầu (row index). Bắt đầu từ `0` |
| `limit` | integer | Yes | Số rows validate mỗi lần (chunk size) |
| `total` | integer | No | Tổng số rows. Gửi `null` lần đầu, server sẽ trả về |

**Response `200 OK`**:
```json
{
  "data": {
    "file_name": "tmp_import_abc123.csv",
    "offset": 100,
    "count": 100,
    "total": 250,
    "errors": []
  }
}
```

| Field | Type | Description |
|---|---|---|
| `file_name` | string | Tên file (dùng lại cho request tiếp) |
| `offset` | integer | Offset cho chunk tiếp theo |
| `count` | integer | Số rows đã validate trong chunk này |
| `total` | integer | Tổng số rows trong file |
| `errors` | array | Mảng lỗi validation. Rỗng = hợp lệ |

**Nếu có lỗi**:
```json
{
  "data": {
    "file_name": "tmp_import_abc123.csv",
    "offset": 100,
    "count": 100,
    "total": 250,
    "errors": [
      { "row": 5, "attribute": "name", "errors": ["The name field is required."] },
      { "row": 12, "attribute": "status", "errors": ["The selected status is invalid."] }
    ]
  }
}
```

#### Validate loop (FE logic)
```js
async function validateImport(type, fileName) {
  let offset = 0;
  const limit = 100;
  let total = null;
  const allErrors = [];

  while (true) {
    const res = await api.post(`/import/${getImportPath(type)}/validate`, {
      file_name: fileName,
      offset,
      limit,
      total,
    });

    const data = res.data.data;
    total = data.total;
    allErrors.push(...data.errors);

    // Cập nhật progress bar: (data.offset / data.total) * 100
    updateProgress(data.offset, data.total);

    if (data.offset >= data.total) break; // Hết file
    offset = data.offset; // Chunk tiếp theo
  }

  return { total, errors: allErrors };
}
```

---

### Bước 3: Import data (theo chunks)

Sau khi validate xong (không lỗi hoặc user chấp nhận), tiến hành import.

```http
POST /v1/tools/data-synchronize/import/posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "file_name": "tmp_import_abc123.csv",
  "offset": 0,
  "limit": 100
}
```

**Response `200 OK`**:
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

| Field | Type | Description |
|---|---|---|
| `offset` | integer | Offset cho chunk tiếp |
| `count` | integer | Số rows đã xử lý trong chunk |
| `imported` | integer | Số rows import thành công |
| `failures` | integer | Số rows bị lỗi |

#### Import loop (FE logic)
```js
async function importData(type, fileName, total) {
  let offset = 0;
  const limit = 100;
  let totalImported = 0;
  let totalFailures = 0;

  while (offset < total) {
    const res = await api.post(`/import/${getImportPath(type)}`, {
      file_name: fileName,
      offset,
      limit,
    });

    const data = res.data.data;
    totalImported += data.imported;
    totalFailures += data.failures;

    updateProgress(data.offset, total);

    if (data.count === 0) break; // Không còn rows
    offset = data.offset;
  }

  return { totalImported, totalFailures };
}
```

---

### Complete Import Flow (kết hợp tất cả)
```js
async function fullImportFlow(type, file) {
  // Bước 1: Upload
  const formData = new FormData();
  formData.append('file', file);
  const uploadRes = await api.post('/upload', formData);
  const fileName = uploadRes.data.data.file_name;

  // Bước 2: Validate
  const { total, errors } = await validateImport(type, fileName);
  if (errors.length > 0) {
    showErrors(errors); // Hiển thị lỗi cho user
    return;             // Hoặc hỏi user có muốn tiếp tục?
  }

  // Bước 3: Import
  const result = await importData(type, fileName, total);
  showSuccess(`Imported ${result.totalImported}, Failures: ${result.totalFailures}`);
}
```

---

## 6. Error Handling

| HTTP Status | Ý nghĩa | FE xử lý |
|---|---|---|
| `401` | Chưa đăng nhập | Redirect login |
| `403` | Không có quyền | Hiển thị "Bạn không có quyền" |
| `404` | Type không tồn tại | Hiển thị lỗi |
| `422` | Validation error (file thiếu, sai format) | Hiển thị lỗi cụ thể từ `message` |
| `500` | Server error | Hiển thị "Lỗi hệ thống" |

### Validation Error Response (422)
```json
{
  "message": "The file field is required.",
  "errors": {
    "file": ["The file field is required."]
  }
}
```

---

## 7. Permissions Matrix

FE cần kiểm tra quyền user trước khi hiển thị nút Export/Import.

| Type | Export Permission | Import Permission |
|---|---|---|
| `posts` | `posts.export` | `posts.import` |
| `pages` | `pages.export` | `pages.import` |
| `post-translations` | `post-translations.export` | `post-translations.import` |
| `page-translations` | `page-translations.export` | `page-translations.import` |
| `other-translations` | `other-translations.export` | `other-translations.import` |

> Schema/types endpoints chỉ cần auth, không cần permission cụ thể.

---

## Path Mapping Helper

```js
// type → export path
const EXPORT_PATH_MAP = {
  'posts': 'posts',
  'pages': 'pages',
  'post-translations': 'translations/model',
  'page-translations': 'translations/page',
  'other-translations': 'other-translations',
};

// type → import path
const IMPORT_PATH_MAP = {
  'posts': 'posts',
  'pages': 'pages',
  'post-translations': 'translations/model',
  'page-translations': 'translations/page',
  'other-translations': 'other-translations',
};
```
