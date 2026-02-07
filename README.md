### Get Started

```bash
php artisan migrate
php artisan passport:keys
php artisan passport:client --password --name="Web Client"
php artisan passport:client --password --provider=members --name="Member Web Client"
php artisan passport:client --password --provider=members --name="Member Mobile Client"
```

```bash
FORCE_SETTINGS_SEED=true php artisan db:seed --class=App\\Containers\\AppSection\\Setting\\Data\\Seeders\\SettingsSeeder_1
```

### Container Docs

- Container index: `docs/containers-index.md`
- Container README template: `docs/container-readme-template.md`
- Regenerate index: `bash scripts/generate-containers-index.sh docs/containers-index.md auto-generated`
- Run docs checks: `make docs`
- Regenerate index quickly: `make docs-fix`
- Check container README schema only: `make docs-schema`
- AuditLog: `app/Containers/AppSection/AuditLog/README.md`
- Authentication: `app/Containers/AppSection/Authentication/README.md`
- Authorization: `app/Containers/AppSection/Authorization/README.md`
- Blog: `app/Containers/AppSection/Blog/README.md`
- CustomField: `app/Containers/AppSection/CustomField/README.md`
- Device: `app/Containers/AppSection/Device/README.md`
- Gallery: `app/Containers/AppSection/Gallery/README.md`
- Language: `app/Containers/AppSection/Language/README.md`
- LanguageAdvanced: `app/Containers/AppSection/LanguageAdvanced/README.md`
- Media: `app/Containers/AppSection/Media/README.md`
- Member: `app/Containers/AppSection/Member/README.md`
- MetaBox: `app/Containers/AppSection/MetaBox/README.md`
- Page: `app/Containers/AppSection/Page/README.md`
- RequestLog: `app/Containers/AppSection/RequestLog/README.md`
- Revision: `app/Containers/AppSection/Revision/README.md`
- Setting: `app/Containers/AppSection/Setting/README.md`
- Slug: `app/Containers/AppSection/Slug/README.md`
- System: `app/Containers/AppSection/System/README.md`
- Tools: `app/Containers/AppSection/Tools/README.md`
- Translation: `app/Containers/AppSection/Translation/README.md`
- User: `app/Containers/AppSection/User/README.md`

### Member Auth Notes

- Web login/refresh uses httpOnly `memberRefreshToken` cookie.
- Web refresh requires CSRF header. Server will return `x-csrf-token` header on login/refresh.
- Mobile requests should send header `x-client: mobile` to receive `refresh_token` in JSON.
- Social login callback returns JSON by default. If `MEMBER_SOCIAL_WEB_REDIRECT_URL` is set, web callback will 302 redirect to that URL and append token data in the URL fragment (`#access_token=...&expires_in=...&csrf_token=...`).

### Request Signature (Optional)

- Middleware alias: `request.signature` (not enforced by default).
- Enable verification with `DEVICE_SIGNATURE_ENABLED=true`.
- Require signature headers by setting `DEVICE_SIGNATURE_ENFORCE=true`.

### Device Endpoints

- `POST /v1/member/devices` register member device + public key.
- `GET /v1/member/devices` list member devices.
- `PATCH /v1/member/devices/{device_id}` update member device (push token, platform, etc).
- `DELETE /v1/member/devices/{device_id}` revoke member device.
- `GET /v1/member/devices/{device_id}/keys` list member device keys.
- `POST /v1/member/devices/{device_id}/keys/rotate` rotate member device key.
- `DELETE /v1/member/devices/{device_id}/keys/{key_id}` revoke a member device key.
- `POST /v1/users/devices` register admin user device + public key.
- `GET /v1/users/devices` list admin user devices.
- `PATCH /v1/users/devices/{device_id}` update admin user device (push token, platform, etc).
- `DELETE /v1/users/devices/{device_id}` revoke admin user device.
- `GET /v1/users/devices/{device_id}/keys` list admin user device keys.
- `POST /v1/users/devices/{device_id}/keys/rotate` rotate admin user device key.
- `DELETE /v1/users/devices/{device_id}/keys/{key_id}` revoke an admin user device key.

Notes:
- List endpoints support `limit` (Apiato default) and `page`.
- Default page size follows Apiato `PAGINATION_LIMIT_DEFAULT`.
- Max page size follows repository `maxPaginationLimit` (Device/DeviceKey repositories).
- Use `include_public_key=1` to include public keys in device key listing responses (admin only, `/v1/users/...`).
- Use `include=keys` on device list/register/update/revoke to embed device keys in the response.
- Pagination reads `page` from query params only (Apiato default).
