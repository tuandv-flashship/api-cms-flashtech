
### Get Started
``` 
php artisan migrate
php artisan passport:keys
php artisan passport:client --password --name="Web Client"
php artisan passport:client --password --provider=members --name="Member Web Client"
php artisan passport:client --password --provider=members --name="Member Mobile Client"

```

```
FORCE_SETTINGS_SEED=true php artisan db:seed --class=App\\Containers\\AppSection\\Setting\\Data\\Seeders\\SettingsSeeder_1

```

### Member Auth Notes
- Web login/refresh uses httpOnly `memberRefreshToken` cookie.
- Web refresh requires CSRF header. Server will return `x-csrf-token` header on login/refresh.
- Mobile requests should send header `x-client: mobile` to receive `refresh_token` in JSON.
- Social login callback returns JSON by default. If `MEMBER_SOCIAL_WEB_REDIRECT_URL` is set, web callback will 302 redirect to that URL and append token data in the URL fragment (`#access_token=...&expires_in=...&csrf_token=...`).
