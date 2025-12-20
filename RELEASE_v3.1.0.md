# 🚀 Aktif Laundry v3.1.0

**Release Date:** December 20, 2025

## 📝 Overview

Version 3.1.0 menambahkan fitur browser detection yang powerful untuk meningkatkan user experience, terutama untuk pengguna yang mengakses aplikasi melalui in-app browser (seperti Instagram, Facebook, WhatsApp, dll).

## ✨ What's New

### 🌐 Browser Helper & In-App Browser Detection

- **Browser Detection Utility** - Sistem deteksi browser yang comprehensive (252 lines of code)
  - Deteksi berbagai in-app browsers (Instagram, Facebook, WhatsApp, TikTok, Twitter, LinkedIn, dll)
  - Deteksi browser standar (Chrome, Safari, Firefox, Edge, dll)
  - Deteksi platform (Android, iOS, Windows, macOS, Linux)
  - Support untuk mobile & desktop detection

- **Enhanced FAB Components**
  - FAB Kurir - Updated dengan browser helper support
  - FAB Landing Page - Improved user experience dengan browser detection
  - FAB Pelanggan - Enhanced functionality dengan in-app browser handling

- **Login Page Improvements**
  - Smart detection untuk in-app browsers
  - Better UX untuk users yang mengakses via social media apps
  - Optimized layout dan behavior berdasarkan browser type

- **Layout Updates**
  - Semua layout files (Kurir, Pelanggan, Management) updated
  - Browser helper tersedia di semua halaman (app & guest layouts)
  - Consistent experience across all user roles

## 📊 Technical Details

- **18 files changed**
- **543 additions**
- **91 deletions**
- **Net change:** +452 lines

### Modified Files:
```
app/Helper/ManifestHelper.php
app/Livewire/Components/FabKurir.php
app/Livewire/Components/FabLandingPage.php
app/Livewire/Components/FabPelanggan.php
app/Livewire/Pelanggan/Pages/Auth/Login.php
resources/js/bootstrap.js
resources/js/utils/browserHelper.js (NEW)
resources/views/layouts/*/app.blade.php
resources/views/layouts/*/guest.blade.php
resources/views/livewire/components/fab-*.blade.php
resources/views/livewire/pelanggan/pages/auth/login.blade.php
composer.lock
```

## 🔄 Upgrade Instructions

### For Development:
```bash
git checkout main
git pull origin main
composer install
bun install
bun run build
php artisan migrate
```

### For Production:
```bash
git fetch --tags
git checkout v3.1.0
composer install --no-dev --optimize-autoloader
bun install --production
bun run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔧 Configuration

No additional configuration required. Browser helper automatically loaded on all pages.

## 🐛 Bug Fixes

No bug fixes in this release (feature release).

## 🎯 Breaking Changes

**None** - This release is fully backward compatible with v3.0.0

## 📚 Documentation

Browser helper functions are available globally via `window.BrowserHelper`:
- `isInAppBrowser()` - Check if running in in-app browser
- `getInAppBrowserName()` - Get specific in-app browser name
- `getBrowserInfo()` - Get detailed browser information
- `isMobile()` - Check if mobile device
- `getPlatform()` - Get platform information

## 🙏 Credits

Built with ❤️ by Aktif Laundry Team

## 📌 Notes

- Tested on Chrome, Safari, Firefox, Edge
- Tested on iOS & Android in-app browsers
- Compatible with PWA mode

---

**Full Changelog**: https://github.com/denis156/aktif-laundry/compare/v3.0.0...v3.1.0

**Download**: https://github.com/denis156/aktif-laundry/archive/refs/tags/v3.1.0.zip
