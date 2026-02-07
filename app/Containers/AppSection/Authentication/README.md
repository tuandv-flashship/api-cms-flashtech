### Authentication Container

Container path: `app/Containers/AppSection/Authentication`

### Scope

- User/admin authentication flows (web + API).
- OAuth2 token issue/refresh/revoke via Laravel Passport.
- Email verification and password reset.
- Web login/logout endpoints.

### API Routes

API route files:
- `app/Containers/AppSection/Authentication/UI/API/Routes/RegisterUser.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/RevokeToken.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/Passport.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/WebClient/IssueToken.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/WebClient/RefreshToken.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/PasswordReset/ForgotPassword.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/PasswordReset/ResetPassword.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/EmailVerification/Send.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/EmailVerification/Verify.v1.private.php`
- `app/Containers/AppSection/Authentication/UI/API/Routes/Welcome.v1.private.php`

Web route files:
- `app/Containers/AppSection/Authentication/UI/WEB/Routes/Login.php`
- `app/Containers/AppSection/Authentication/UI/WEB/Routes/Logout.php`
- `app/Containers/AppSection/Authentication/UI/WEB/Routes/Home.php`

Auth notes:
- Guard is Passport-based and defaults to `api`.
- Token issuance uses configured OAuth clients (`web`, `member`, `mobile`).

### Main Config

- `app/Containers/AppSection/Authentication/Configs/appSection-authentication.php`
- `app/Containers/AppSection/Authentication/Configs/passport.php`

Common env keys:
- `CLIENT_WEB_ID`, `CLIENT_WEB_SECRET`
- `CLIENT_MEMBER_ID`, `CLIENT_MEMBER_SECRET`
- `CLIENT_MOBILE_ID`, `CLIENT_MOBILE_SECRET`
- `API_TOKEN_EXPIRES`, `API_REFRESH_TOKEN_EXPIRES`
- `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY`, `PASSPORT_CONNECTION`

### Operational Notes

- OAuth token TTL and refresh TTL are controlled centrally in auth config.
- Passport keys must exist before issuing tokens.
- Use separate OAuth client IDs/secrets per channel to keep revocation/rotation isolated.

### Tests

Available tests:
- `app/Containers/AppSection/Authentication/Tests`

Run:

```bash
php artisan test app/Containers/AppSection/Authentication/Tests
```

### Change Log

- `2026-02-07`: Added dedicated Authentication container documentation.
