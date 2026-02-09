### Device Container

Container path: `app/Containers/AppSection/Device`

### Scope

- Register/list/update/revoke devices for `member` and `user`.
- Manage device keys (rotate/revoke/list).
- Verify request signature for signed mutation endpoints.
- Async update for device/key activity (`last_seen_at`, `last_used_at`).

### API Routes

Member routes:
- `POST /v1/member/devices`
- `GET /v1/member/devices`
- `PATCH /v1/member/devices/{device_id}`
- `DELETE /v1/member/devices/{device_id}`
- `GET /v1/member/devices/{device_id}/keys`
- `POST /v1/member/devices/{device_id}/keys/rotate`
- `DELETE /v1/member/devices/{device_id}/keys/{key_id}`

User/admin routes:
- `POST /v1/users/devices`
- `GET /v1/users/devices`
- `PATCH /v1/users/devices/{device_id}`
- `DELETE /v1/users/devices/{device_id}`
- `GET /v1/users/devices/{device_id}/keys`
- `POST /v1/users/devices/{device_id}/keys/rotate`
- `DELETE /v1/users/devices/{device_id}/keys/{key_id}`

Route files:
- `app/Containers/AppSection/Device/UI/API/Routes`

### Request Signature

Middleware alias: `request.signature`

Signed endpoints (mutate only):
- `POST /v1/member/devices`
- `PATCH /v1/member/devices/{device_id}`
- `DELETE /v1/member/devices/{device_id}`
- `POST /v1/member/devices/{device_id}/keys/rotate`
- `DELETE /v1/member/devices/{device_id}/keys/{key_id}`
- `POST /v1/users/devices`
- `PATCH /v1/users/devices/{device_id}`
- `DELETE /v1/users/devices/{device_id}`
- `POST /v1/users/devices/{device_id}/keys/rotate`
- `DELETE /v1/users/devices/{device_id}/keys/{key_id}`

Required headers:
- `x-key-id`: device key id (Base64URL).
- `x-signature`: Ed25519 detached signature (Base64URL).
- `x-timestamp`: Unix seconds.
- `x-nonce`: random Base64URL, single-use.

Canonical payload (joined by `\n`):

```text
{HTTP_METHOD_UPPERCASE}
{PATH_ONLY}
{QUERY_STRING_RFC3986_SORTED}
{SHA256_HEX_OF_RAW_BODY}
{TIMESTAMP}
{NONCE}
```

### Validation/Error Codes

- `signature_headers_missing`
- `signature_timestamp_invalid`
- `signature_timestamp_expired`
- `signature_nonce_reused`
- `signature_key_invalid`
- `signature_owner_mismatch`
- `signature_invalid`
- `signature_failure_throttled`

### Main Config

Container config:
- `app/Containers/AppSection/Device/Configs/device.php`

Main toggles:
- `DEVICE_SIGNATURE_ENABLED`, `DEVICE_SIGNATURE_ENFORCE`
- `DEVICE_SIGNATURE_REQUIRE_AUTHENTICATED_OWNER`
- `DEVICE_SIGNATURE_CACHE_KEY_PREFIX`, `DEVICE_SIGNATURE_CACHE_STORE`
- `DEVICE_SIGNATURE_NONCE_TTL`, `DEVICE_SIGNATURE_TIMESTAMP_TTL`
- `DEVICE_SIGNATURE_KEY_CONTEXT_CACHE_ENABLED`, `DEVICE_SIGNATURE_KEY_CONTEXT_CACHE_TTL`
- `DEVICE_SIGNATURE_KEY_CONTEXT_CONSISTENCY_CHECK`
- `DEVICE_SIGNATURE_ACTIVITY_TOUCH_DEBOUNCE_SECONDS`
- `DEVICE_SIGNATURE_ACTIVITY_QUEUE_CONNECTION`, `DEVICE_SIGNATURE_ACTIVITY_QUEUE`
- `DEVICE_SIGNATURE_ACTIVITY_JOB_*`
- `DEVICE_SIGNATURE_FAILURE_LOG_*`
- `DEVICE_SIGNATURE_FAILURE_THROTTLE_*`
- `DEVICE_SIGNATURE_METRICS_*`

### Production Baseline

```env
DEVICE_SIGNATURE_ENABLED=true
DEVICE_SIGNATURE_ENFORCE=true
DEVICE_SIGNATURE_REQUIRE_AUTHENTICATED_OWNER=true
DEVICE_SIGNATURE_CACHE_KEY_PREFIX=sig
DEVICE_SIGNATURE_CACHE_STORE=redis
DEVICE_SIGNATURE_NONCE_TTL=300
DEVICE_SIGNATURE_TIMESTAMP_TTL=300
DEVICE_SIGNATURE_KEY_CONTEXT_CACHE_ENABLED=true
DEVICE_SIGNATURE_KEY_CONTEXT_CACHE_TTL=60
DEVICE_SIGNATURE_KEY_CONTEXT_CONSISTENCY_CHECK=false
DEVICE_SIGNATURE_ACTIVITY_TOUCH_DEBOUNCE_SECONDS=60
DEVICE_SIGNATURE_ACTIVITY_QUEUE_CONNECTION=redis
DEVICE_SIGNATURE_ACTIVITY_QUEUE=security
DEVICE_SIGNATURE_ACTIVITY_JOB_TRIES=3
DEVICE_SIGNATURE_ACTIVITY_JOB_BACKOFF=5,30,120
DEVICE_SIGNATURE_ACTIVITY_JOB_TIMEOUT=15
DEVICE_SIGNATURE_ACTIVITY_JOB_MAX_EXCEPTIONS=1
DEVICE_SIGNATURE_ACTIVITY_JOB_FAIL_ON_TIMEOUT=true
DEVICE_SIGNATURE_ACTIVITY_JOB_LOG_ENABLED=true
DEVICE_SIGNATURE_FAILURE_LOG_ENABLED=true
DEVICE_SIGNATURE_FAILURE_THROTTLE_ENABLED=true
DEVICE_SIGNATURE_FAILURE_THROTTLE_MAX_ATTEMPTS=20
DEVICE_SIGNATURE_FAILURE_THROTTLE_DECAY_SECONDS=60
DEVICE_SIGNATURE_FAILURE_THROTTLE_STORE=redis
DEVICE_SIGNATURE_FAILURE_THROTTLE_ERROR_CODES=signature_invalid,signature_nonce_reused
DEVICE_SIGNATURE_METRICS_ENABLED=true
DEVICE_SIGNATURE_METRICS_STORE=redis
DEVICE_SIGNATURE_METRICS_NAMESPACE=device_signature
DEVICE_SIGNATURE_METRICS_TTL_SECONDS=7200
```

### Operational Notes

- List endpoints support `limit` and `page` (Apiato default).
- Default page size: `PAGINATION_LIMIT_DEFAULT`.
- Max page size: repository `maxPaginationLimit`.
- `include_public_key=1` only exposes `public_key` on admin route (`/v1/users/...`).
- `include=keys` can embed device keys in device list responses.
- Signature activity update uses both debounce cache and unique queued jobs to limit duplicate writes.

### Tests

Functional + unit tests:
- `app/Containers/AppSection/Device/Tests`

Run:

```bash
php artisan test app/Containers/AppSection/Device/Tests
```

### Change Log

- `2026-02-07`: Added dedicated Device container documentation and production baseline.
