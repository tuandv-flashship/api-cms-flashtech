### Media Container

Container path: `app/Containers/AppSection/Media`

### Scope

- Upload media files (regular and chunk upload flows).
- List media and folder trees/lists.
- Create media folders.
- Build signed/authorized download URLs.
- Resolve/show media files by hashed route params.
- Run global media actions.

### API Routes

Route files:
- `app/Containers/AppSection/Media/UI/API/Routes/ListMedia.v1.private.php`
- `app/Containers/AppSection/Media/UI/API/Routes/ListMediaFolderTree.v1.private.php`
- `app/Containers/AppSection/Media/UI/API/Routes/ListMediaFolderList.v1.private.php`
- `app/Containers/AppSection/Media/UI/API/Routes/CreateMediaFolder.v1.private.php`
- `app/Containers/AppSection/Media/UI/API/Routes/UploadMediaFile.v1.private.php`
- `app/Containers/AppSection/Media/UI/API/Routes/ShowMediaFile.v1.private.php`
- `app/Containers/AppSection/Media/UI/API/Routes/DownloadMediaFile.v1.private.php`
- `app/Containers/AppSection/Media/UI/API/Routes/MediaGlobalAction.v1.private.php`

All Media API endpoints currently use `auth:api`.

### Main Config

- `app/Containers/AppSection/Media/Configs/media.php`
- `app/Containers/AppSection/Media/Configs/permissions.php`

Common env keys:
- `MEDIA_DISK`, `MEDIA_DRIVER`, `MEDIA_PRIVATE_DISK`, `MEDIA_PRIVATE_ACCESS_MODE`
- `MEDIA_SIGNED_URL_TTL_MINUTES`
- `MEDIA_CHUNK_ENABLED`, `MEDIA_CHUNK_SIZE`, `MEDIA_MAX_FILE_SIZE`
- `MEDIA_THROTTLE_UPLOAD`, `MEDIA_THROTTLE_DOWNLOAD_URL`
- `MEDIA_ALLOWED_MIME_TYPES`
- `MEDIA_USE_STORAGE_SYMLINK`

### Operational Notes

- Upload route throttle is configured by `media.throttle.upload`.
- Download-url route throttle is configured by `media.throttle.download_url`.
- Chunk cleanup scheduling config is under `media.chunk.clear`.

### Tests

Available tests:
- `app/Containers/AppSection/Media/Tests/Functional/API`
- `app/Containers/AppSection/Media/Tests/Unit`

Run:

```bash
php artisan test app/Containers/AppSection/Media/Tests
```

### Change Log

- `2026-02-07`: Added dedicated Media container documentation.
