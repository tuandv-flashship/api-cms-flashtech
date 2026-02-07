### Member Container

Container path: `app/Containers/AppSection/Member`

### Scope

- Member registration, login, logout, refresh token.
- Member profile and password flows.
- Email verification and password reset.
- Social login redirect/callback.
- Admin CRUD for members.

### API Routes

Public/member routes are defined in:
- `app/Containers/AppSection/Member/UI/API/Routes/RegisterMember.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/LoginMember.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/RefreshMemberToken.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/LogoutMember.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/GetMemberProfile.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/UpdateMemberProfile.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/ChangePassword.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/ForgotPassword.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/ResetPassword.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/VerifyEmail.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/RedirectToSocialProvider.v1.public.php`
- `app/Containers/AppSection/Member/UI/API/Routes/HandleSocialProviderCallback.v1.public.php`

Admin routes are defined in:
- `app/Containers/AppSection/Member/UI/API/Routes/ListMembers.v1.private.php`
- `app/Containers/AppSection/Member/UI/API/Routes/FindMemberById.v1.private.php`
- `app/Containers/AppSection/Member/UI/API/Routes/CreateMember.v1.private.php`
- `app/Containers/AppSection/Member/UI/API/Routes/UpdateMember.v1.private.php`
- `app/Containers/AppSection/Member/UI/API/Routes/DeleteMember.v1.private.php`

### Operational Notes

- Web login/refresh uses httpOnly cookie `memberRefreshToken`.
- Web refresh requires CSRF header. Server returns `x-csrf-token` in login/refresh response header.
- Mobile client can use header `x-client: mobile` to receive `refresh_token` in JSON.
- Social callback returns JSON by default.
- If `MEMBER_SOCIAL_WEB_REDIRECT_URL` is configured, callback returns `302` to that URL and appends token data in URL fragment.

### Main Config

Member auth/runtime settings:
- `app/Containers/AppSection/Member/Configs/member.php`

Related env keys (common):
- `CLIENT_MEMBER_ID`, `CLIENT_MEMBER_SECRET`
- `CLIENT_MOBILE_ID`, `CLIENT_MOBILE_SECRET`
- `MEMBER_REFRESH_THROTTLE`, `MEMBER_LOGOUT_THROTTLE`
- `MEMBER_PROFILE_READ_THROTTLE`, `MEMBER_PROFILE_UPDATE_THROTTLE`
- `MEMBER_CHANGE_PASSWORD_THROTTLE`
- `MEMBER_VERIFY_EMAIL_THROTTLE`
- `MEMBER_SOCIAL_REDIRECT_THROTTLE`, `MEMBER_SOCIAL_CALLBACK_THROTTLE`
- `MEMBER_SOCIAL_TOKEN_TTL`, `MEMBER_SOCIAL_WEB_REDIRECT_URL`
- `MEMBER_CSRF_ENABLED`, `MEMBER_CSRF_COOKIE_NAME`, `MEMBER_CSRF_HEADER_NAME`

### Tests

Member functional tests:
- `app/Containers/AppSection/Member/Tests/Functional/API`

Run:

```bash
php artisan test app/Containers/AppSection/Member/Tests/Functional/API
```

### Change Log

- `2026-02-07`: Added dedicated Member container documentation.
