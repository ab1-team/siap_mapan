# Catatan Deployment

File ini di-push untuk trigger deployment hook di hosting.

## Status per 28 September 2026

- File `public/assets/css/custom.css` di GitHub sudah **bersih** (tidak ada styling hijau sidebar).
- Commit terbaru: `d8ebb03`.
- Jika hosting online masih menampilkan sidebar berwarna hijau, kemungkinan:
  1. Cache browser (`Ctrl+Shift+R`)
  2. Cache Laravel di hosting (`php artisan view:clear && php artisan cache:clear`)
  3. CDN/proxy cache di depan hosting

## Cara pull manual ke hosting

```bash
cd /path/to/app
git pull origin master
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

Atau upload manual `public/assets/css/custom.css` via FTP dari commit `d8ebb03`.
