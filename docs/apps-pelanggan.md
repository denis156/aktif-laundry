<div align="center">
  <img src="../public/icon.png" alt="Aktif Laundry Logo"  height="120">

  # Aktif Laundry - Customer Application

  [![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
  [![PWA](https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
  [![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
  [![Mary UI](https://img.shields.io/badge/Mary_UI-Latest-FF69B4?style=for-the-badge)](https://mary-ui.com)

  **Progressive Web App untuk Pelanggan Laundry**

  Dibuat oleh [Denis Djodian Ardika](https://github.com/denis156) - Tech Lead at PT. Aktif Gapura Internasional

  [![GitHub](https://img.shields.io/badge/GitHub-denis156/aktif--laundry-181717?style=for-the-badge&logo=github)](https://github.com/denis156/aktif-laundry)
</div>

---

## 📋 Daftar Isi

- [Tentang Aplikasi](#-tentang-aplikasi)
- [Fitur Utama](#-fitur-utama)
- [Halaman & Modul](#-halaman--modul)
- [Komponen](#-komponen)
- [PWA Features](#-pwa-features)
- [Teknologi](#-teknologi)

---

## 📱 Tentang Aplikasi

Aplikasi Customer adalah **Progressive Web App (PWA)** yang memungkinkan pelanggan untuk memesan layanan laundry, melacak pesanan, mendapatkan promo, dan menggunakan kode referral. Aplikasi ini dapat diinstall di smartphone seperti aplikasi native dengan pengalaman yang cepat dan responsif. Dilengkapi dengan **real-time chat**, **courier tracking**, dan **interactive maps**.

> **PWA Enabled**: Aplikasi ini dapat diinstall di perangkat mobile (Android & iOS) dan bekerja offline dengan service worker.

### ✨ What's New in This Documentation
- 💬 **Chat System**: Dokumentasi lengkap chat dengan admin/staf
- 📍 **Courier Tracking**: Live tracking kurir untuk transaksi Anda
- 👤 **Profile Management Deep Dive**: Regional address system dengan validation
- 🗺️ **Maps Integration**: Pilih lokasi dengan drag & drop marker
- 🎨 **PWA Features**: Detailed splash screens dan installation guide
- 🔐 **Security Features**: Multi-guard auth dan data privacy

### 📸 Screenshots

<div align="center">

#### 🌞 Light Mode
![Customer Dashboard Light](../public/images/PelangganDashboardLight.png)

#### 🌙 Dark Mode
![Customer Dashboard Dark](../public/images/PelangganDashboardDark.png)

</div>

---

## ✨ Fitur Utama

### 🏠 Beranda & Dashboard
![Dashboard](https://img.shields.io/badge/Module-Dashboard-blue?style=flat-square)

- **Statistik Pesanan**: Total transaksi, pesanan dalam proses, dan selesai
- **Quick Order**: Tombol cepat untuk buat pesanan baru
- **List Layanan**: Tampilan katalog layanan dengan harga dan durasi
- **List Promo**: Menampilkan promo aktif yang tersedia
- **Card Referral**: Kartu referral dengan kode unik pelanggan
- **Pesanan Terbaru**: 5 pesanan terakhir customer
- **Auto Refresh**: Real-time update setiap 10 detik

### 🛒 Pemesanan
![Order](https://img.shields.io/badge/Module-Order-green?style=flat-square)

- **Pilih Layanan**: Browse dan pilih layanan yang diinginkan
- **Detail Layanan**: Informasi lengkap layanan (harga, durasi, include/exclude)
- **Buat Pesanan**: Form pemesanan dengan pilihan metode pembayaran
- **Edit Pesanan**: Edit pesanan yang masih dalam status tertentu
- **Multi-Promo**: Gunakan multiple promo dalam satu pesanan
- **Alamat Penjemputan**: Set lokasi penjemputan dengan peta (Leaflet.js)
- **Jadwal Penjemputan**: Pilih tanggal dan waktu penjemputan

### 📋 Riwayat Pesanan
![History](https://img.shields.io/badge/Module-History-orange?style=flat-square)

- **List Pesanan**: Semua riwayat transaksi pelanggan
- **Detail Pesanan**: Informasi lengkap pesanan dengan timeline
- **Status Tracking**: Track status pesanan real-time
- **Filter & Search**: Cari pesanan berdasarkan kode atau tanggal
- **Receipt Digital**: Lihat struk digital pesanan

### 🎁 Promo & Referral
![Promo](https://img.shields.io/badge/Module-Promo-purple?style=flat-square)

- **Browse Promo**: Lihat semua promo yang tersedia
- **Detail Promo**: Syarat & ketentuan, periode, dan cara pakai promo
- **Auto Apply**: Promo otomatis terapply saat checkout
- **Kode Referral**: Dapatkan kode referral unik untuk dibagikan
- **Referral Tracking**: Lihat statistik referral (total & berhasil)
- **Loyalty Points**: Kumpulkan poin setiap transaksi
- **Member Card**: Nomor kartu member digital

### 💬 Chat & Communication
![Chat](https://img.shields.io/badge/Module-Chat-cyan?style=flat-square)

- **Real-time Messaging**: Chat langsung dengan Admin/Staf
- **File Sharing**: Kirim foto, dokumen (PDF, DOC, DOCX) max 5MB
- **Message Status**: Read/unread indicator untuk setiap pesan
- **Image Preview**: Preview gambar sebelum kirim & full screen viewer
- **Auto Scroll**: Scroll otomatis ke pesan terbaru
- **Search**: Cari percakapan berdasarkan nama atau pesan
- **Conversation List**: Lihat semua percakapan dengan unread count

### 📍 Courier Tracking
![Tracking](https://img.shields.io/badge/Module-Tracking-FF6B35?style=flat-square&logo=google-maps&logoColor=white)

- **Real-time Position**: Track posisi kurir real-time di map
- **GPS Metrics**: Speed, bearing, accuracy dari kurir
- **Customer Location**: Lihat lokasi penjemputan/pengiriman Anda
- **Route Visualization**: Marker kurir dan marker customer
- **Vehicle Info**: Jenis kendaraan kurir (motor/mobil)
- **Auto Refresh**: Update tracking data otomatis (wire:poll)
- **Cache-based**: Data dari cache kurir app (updated < 5 menit)

### 👤 Profile & Pengaturan
![Profile](https://img.shields.io/badge/Module-Profile-gray?style=flat-square)

- **Profile Management**: Update profil, foto, dan data diri
- **Avatar Upload**: Upload foto profil max 5MB
- **Contact Info**: Edit nama, no HP, dan email
- **Address Management**: Update alamat lengkap dengan regional selector
- **GPS Coordinates**: Simpan latitude/longitude lokasi
- **Regional Selection**: Pilih Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan
- **Address Preview**: Preview alamat lengkap auto-generated
- **Modal Editing**: Setiap field edit via modal terpisah
- **Validation**: Phone number validation dan unique email check
- **Change Password**: Ubah password akun (via pengaturan)
- **App Settings**: Pengaturan notifikasi dan preferensi
- **Logout**: Keluar dari akun

### 🔐 Authentication
![Auth](https://img.shields.io/badge/Module-Auth-red?style=flat-square)

- **Register**: Daftar akun baru dengan/tanpa kode referral
- **Login**: Masuk ke aplikasi
- **Forgot Password**: Reset password via email
- **Email Verification**: Verifikasi email (optional)
- **Remember Me**: Login otomatis

---

## 🗂️ Halaman & Modul

### Authentication
```
📁 /pelanggan/auth/
  ├── 🆕 register                 # Register page dengan referral code
  ├── 🔐 login                    # Login page
  ├── 🔑 forgot-password          # Forgot password page
  ├── 🔄 reset-password           # Reset password page
  └── ✉️ verify-email             # Email verification page
```

### Main Application
```
📁 /pelanggan/
  ├── 🏠 beranda                  # Dashboard dengan statistik & quick access
  ├── 📋 riwayat                  # Riwayat semua pesanan
  ├── 💬 chat                     # Chat dengan admin/staf
  ├── 👤 profile                  # Profile management
  └── ⚙️ pengaturan               # App settings
```

### Order Flow
```
📁 /pelanggan/pesanan/
  ├── 🛍️ pilih-layanan            # Browse dan pilih layanan
  ├── 📝 detail-layanan/{id}      # Detail informasi layanan
  ├── ➕ buat-pesanan             # Form buat pesanan baru
  ├── 👁️ detail-pesanan/{id}      # Detail dan tracking pesanan
  └── ✏️ edit-pesanan/{id}        # Edit pesanan
```

### Promo Module
```
📁 /pelanggan/promo/
  └── 📄 detail-promo/{id}        # Detail promo dengan syarat & ketentuan
```

### Chat Module
```
📁 /pelanggan/chat/
  ├── 💬 chat                     # List semua percakapan
  └── 💭 chat-room/{conversation} # Chat room untuk percakapan tertentu
```

### Tracking Module
```
📁 /pelanggan/riwayat/
  └── 📍 {id}/kurir               # Track posisi kurir untuk transaksi tertentu
```

---

## 🧩 Komponen

Aplikasi Customer dilengkapi dengan komponen-komponen custom untuk pengalaman mobile-first:

### Navigation Components
| Komponen | Deskripsi | Lokasi |
|----------|-----------|--------|
| **Top Nav** | Header navigasi dengan logo dan user info | `pelanggan/component/top-nav` |
| **Bottom Nav** | Bottom navigation bar untuk menu utama | `pelanggan/component/bottom-nav` |

### UI Components
| Komponen | Deskripsi | Lokasi |
|----------|-----------|--------|
| **List Layanan** | Card list layanan dengan icon dan harga | `pelanggan/component/list-layanan` |
| **List Promo** | Carousel/grid promo aktif | `pelanggan/component/list-promo` |
| **Card Referral** | Kartu referral code dengan share button | `pelanggan/component/card-referral` |

---

## 📱 PWA Features

### Progressive Web App Capabilities
![PWA](https://img.shields.io/badge/PWA-Full_Support-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)

#### Installation
- ✅ **Add to Home Screen**: Install aplikasi seperti native app
- ✅ **App Icons**: Icon untuk berbagai ukuran device
- ✅ **Splash Screens**: iOS splash screens untuk semua device
- ✅ **Standalone Mode**: Buka tanpa browser UI

#### iOS Support
- 📱 **iPhone SE, 6, 7, 8**: 640x1136, 750x1294
- 📱 **iPhone 6+, 7+, 8+**: 1242x2148
- 📱 **iPhone X, XS, 11 Pro**: 1125x2436
- 📱 **iPhone XR, 11**: 414x816
- 📱 **iPad 7, 8, 9**: 1536x2048
- 📱 **iPad Pro 10.5**: 1668x2224
- 📱 **iPad Pro 12.9**: 2048x2732

#### Performance
- ⚡ **Fast Loading**: Optimized asset loading
- 🔄 **Auto Refresh**: Real-time data dengan wire:poll
- 📡 **Offline Ready**: Service worker untuk offline access
- 💾 **Cache Strategy**: Smart caching untuk performa optimal

#### Mobile Optimization
- 📱 **Touch Optimized**: Large touch targets untuk mobile
- 🎨 **Responsive Design**: Adaptive layout untuk semua screen size
- 🌈 **Theme Color**: Custom theme color untuk browser bar
- 📍 **Geolocation**: Maps integration dengan Leaflet.js

---

## 🛠️ Teknologi

<div align="center">

### Backend
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

### Frontend
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)
![Mary UI](https://img.shields.io/badge/Mary_UI-Latest-FF69B4?style=for-the-badge)
![DaisyUI](https://img.shields.io/badge/DaisyUI-Latest-5A0EF8?style=for-the-badge&logo=daisyui&logoColor=white)

### PWA & Maps
![PWA](https://img.shields.io/badge/PWA-Service_Worker-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)
![Leaflet](https://img.shields.io/badge/Leaflet.js-Maps-199900?style=for-the-badge&logo=leaflet&logoColor=white)

### Communication
![Chat](https://img.shields.io/badge/Real--time_Chat-Livewire_Polling-4E56A6?style=for-the-badge)
![File Upload](https://img.shields.io/badge/File_Upload-5MB_Max-FF2D20?style=for-the-badge)

### Tools
![Laravel Herd](https://img.shields.io/badge/Laravel_Herd-Development-FF2D20?style=for-the-badge)
![Bun](https://img.shields.io/badge/Bun-Asset_Build-000000?style=for-the-badge&logo=bun&logoColor=white)

</div>

### Key Technologies Breakdown

#### Laravel 12 Features Used
- 🔐 **Multi-guard Authentication**: Separate guard `pelanggan` untuk customer
- ✅ **Email Verification**: Optional verification dengan `verified.pelanggan` middleware
- 📁 **File Storage**: Public disk untuk avatar dan chat attachments
- 💾 **Cache System**: Cache tracking data dari kurir app
- 🗄️ **Eloquent ORM**: Relationships dan query optimization
- 🔄 **Observers**: Auto cleanup files on conversation delete
- 📧 **Notifications**: Toast notifications untuk user feedback
- 🌐 **Localization**: Carbon locale Indonesia untuk dates

#### Livewire 3 Features Used
- 🔄 **Wire:navigate**: SPA-like navigation tanpa reload
- 📊 **Computed Properties**: Efficient data caching untuk dashboard
- 🎯 **Wire:poll**: Auto-refresh tracking data dan chat
- 📡 **Event Dispatch**: JavaScript events untuk map & scroll
- 📁 **File Uploads**: WithFileUploads trait untuk avatar dan chat
- 💬 **Toast**: Mary UI toast untuk user feedback
- 🔔 **Listeners**: Event listeners untuk real-time updates
- 🎨 **Wire:loading**: Loading states untuk semua actions

#### PWA Technologies
- 📱 **Service Worker**: Offline caching dan background sync
- 🎨 **Web App Manifest**: Installation prompt dan app icons
- 🖼️ **iOS Splash Screens**: Device-specific splash screens
- 📍 **Geolocation API**: Browser native GPS untuk maps
- 💾 **LocalStorage**: Offline data persistence
- 🔔 **Push Notifications**: (Future) Real-time order updates

---

## 💬 Chat System Deep Dive

### Real-time Messaging Features
```javascript
// Chat Capabilities untuk Pelanggan:
✅ Chat dengan Admin/Staf
✅ File upload: images (jpg, jpeg, png, gif), documents (pdf, doc, docx)
✅ Max file size: 5MB per file
✅ Message validation: max 5000 characters
✅ Auto-scroll to new messages
✅ Unread message counter per conversation
✅ Mark messages as read automatically
✅ Image preview modal sebelum kirim
✅ Full-screen image viewer untuk received images
✅ Search conversations by name atau message content
✅ Last message preview in conversation list
✅ Participant info: nama, tipe (Admin), avatar
✅ Simple & clean mobile-first UI
```

### Chat Storage & Performance
- 📁 **File Storage**: Chat attachments di `storage/app/public/chat-attachments/`
- 💾 **Auto Cleanup**: Files deleted saat conversation deleted (via Observer)
- 🔄 **Message Limit**: Load last 50 messages per conversation
- 📊 **Efficient Queries**: ChatHelper dengan optimized queries
- 🔍 **Search Index**: Search by participant name atau message content
- 🎭 **Participant Type**: Hanya chat dengan User (Admin/Staf)
- 📱 **Mobile Optimized**: Touch-friendly interface

---

## 📍 Courier Tracking Deep Dive

### Real-time Position Tracking
```javascript
// Tracking Features untuk Pelanggan:
✅ Track posisi kurir untuk transaksi tertentu
✅ Real-time GPS updates (wire:poll)
✅ Cache-based tracking (5 menit TTL dari kurir app)
✅ GPS metrics: latitude, longitude, accuracy, speed, bearing
✅ Customer location marker (pickup/delivery address)
✅ Courier location marker dengan icon
✅ Vehicle info: jenis kendaraan (motor/mobil)
✅ Status indicator: active/inactive/searching
✅ Auto-refresh setiap polling cycle
✅ Route visualization dengan dual markers
```

### Tracking Data Source
- 📡 **Cache-based**: Data dari `kurir_tracking_{id}` cache key
- ⏱️ **5 Menit TTL**: Cache expire jika kurir tidak update
- 🔍 **Priority Logic**: Prioritas kurirAntar > kurirJemput
- 🚗 **Vehicle Info**: Nama kurir, jenis kendaraan
- 📍 **Dual Markers**: Customer location + courier position
- 📊 **GPS Metrics**: Speed (km/h), bearing (compass), accuracy (meters)
- 🗺️ **Map Integration**: Leaflet.js dengan OpenStreetMap tiles
- 🎯 **Status Display**: Searching / Active / Inactive tracking state

### Tracking Access
- 🔐 **Transaction-based**: Hanya track kurir untuk transaksi sendiri
- 🔗 **Direct URL**: `/pelanggan/riwayat/{id}/kurir`
- 📱 **Mobile Optimized**: Full-screen map dengan controls
- 🔄 **Real-time Updates**: Auto-refresh dengan wire:poll

---

## 👤 Profile Management Deep Dive

### Personal Information
- **Kode Pelanggan**: Auto-generated unique code (read-only)
- **Nama**: Editable via modal dengan validation (max 255 chars)
- **No HP**: Phone number dengan auto-format validation
  - Format support: +62, 62, 08, 8
  - Auto normalize ke format +62xxxxx
  - Display format: 08xx-xxxx-xxxx (local)
- **Email**: Optional, unique validation
- **Avatar**: Upload max 5MB, auto-resize dengan placeholder fallback

### Address Management System
```javascript
// Address Features:
✅ Detail alamat (max 500 characters)
✅ Regional selector: Provinsi → Kabupaten/Kota → Kecamatan → Kelurahan
✅ Cascading dropdowns dengan Indonesia data
✅ Auto-format alamat lengkap untuk display
✅ GPS coordinates (latitude/longitude) optional
✅ Address preview real-time saat input
✅ Validation: required kelurahan, kecamatan, detail alamat
✅ Save via transaction untuk data consistency
```

### Regional Location Hierarchy
1. **Provinsi**: Select dari daftar provinsi Indonesia
2. **Kabupaten/Kota**: Auto-load based on provinsi
3. **Kecamatan**: Auto-load based on kabupaten/kota
4. **Kelurahan**: Auto-load based on kecamatan
5. **Detail Alamat**: Free text untuk detail spesifik
6. **GPS Coordinates**: Optional lat/lng untuk maps

### Profile Edit Flow
- 🔄 **Modal-based Editing**: Setiap field punya modal terpisah
- ✅ **Validation**: Real-time validation sebelum save
- 💾 **Transaction Safety**: DB transaction untuk consistency
- 🔔 **Toast Feedback**: Success/error notification
- 📱 **Mobile Friendly**: Touch-optimized modal forms
- ⚡ **Auto-reload**: Data refresh after save

### Smart Validation
- **Phone Number**:
  - Normalize format otomatis
  - Support multiple input formats
  - Display format lokal Indonesia
- **Email**:
  - Unique check across pelanggan table
  - Nullable (optional)
  - Email format validation
- **Coordinates**:
  - Latitude: -90 to 90
  - Longitude: -180 to 180
  - Optional untuk flexibility

### Profile Completion Check
- 🚨 **Auto-detect**: Check data lengkap saat dari halaman pesanan
- 🔔 **Warning Toast**: Notifikasi jika data belum lengkap
- 🎯 **Auto-open Modal**: Open relevant modal untuk complete data
- 📋 **Required Fields**: No HP, detail alamat, kelurahan, kecamatan, coordinates

---

## 🎨 UI/UX Features

### Mobile-First Design
![iOS](https://img.shields.io/badge/iOS-Compatible-000000?style=flat-square&logo=apple&logoColor=white)
![Android](https://img.shields.io/badge/Android-Compatible-3DDC84?style=flat-square&logo=android&logoColor=white)
![Mobile](https://img.shields.io/badge/Mobile-Optimized-success?style=flat-square)
![Tablet](https://img.shields.io/badge/Tablet-Supported-success?style=flat-square)

### Theme Support
![Light Mode](https://img.shields.io/badge/Light_Mode-✓-yellow?style=flat-square)
![Dark Mode](https://img.shields.io/badge/Dark_Mode-✓-black?style=flat-square)

### User Experience
- ⚡ **SPA Experience**: No page reload dengan Livewire
- 🔄 **Real-time Updates**: Auto-refresh data pesanan
- ✨ **Smooth Animations**: Transisi yang smooth dan natural
- 👆 **Touch Gestures**: Swipe, tap optimized
- 🚀 **Quick Actions**: Floating action button untuk order cepat
- 💬 **Toast Notifications**: Feedback visual untuk setiap aksi
- 📍 **Interactive Maps**: Pilih lokasi dengan drag & drop marker
- 🎯 **Bottom Navigation**: Easy thumb reach navigation
- 📲 **Share Function**: Share kode referral via social media

---

## 🗺️ Maps Integration

### Leaflet.js Features
- 📍 **Interactive Map**: Pilih lokasi penjemputan dengan marker
- 🔍 **Search Location**: Cari alamat atau tempat
- 📌 **Pin Location**: Drag marker untuk set lokasi tepat
- 🗺️ **Multiple Layers**: OpenStreetMap tiles
- 📱 **Mobile Optimized**: Touch gestures untuk zoom & pan
- 💾 **Save Coordinates**: Simpan latitude & longitude untuk tracking

---

## 🔐 Security Features

- 🔒 **Authentication**: Multi-guard authentication (`auth:pelanggan`)
- 🛡️ **Data Privacy**: Data pelanggan terenkripsi dan protected
- ✅ **CSRF Protection**: Built-in Laravel CSRF protection
- 🔑 **Password Hashing**: Bcrypt password hashing
- 📧 **Email Verification**: Optional email verification dengan `verified.pelanggan` middleware
- 🚫 **Account Protection**: Rate limiting untuk login attempts
- 📝 **Transaction History**: Audit trail untuk semua transaksi
- 🔐 **Guard Isolation**: Separate guard untuk isolasi session pelanggan
- 📁 **File Upload Validation**: Strict validation untuk avatar dan chat files (max 5MB)
- 🚨 **Error Handling**: User-friendly error messages tanpa expose internal details
- 🔄 **Session Regeneration**: Session regeneration pada login/logout
- 📍 **Location Privacy**: GPS coordinates hanya untuk operational purposes

---

## 🎯 Customer Journey

### 1️⃣ Registration
```
Unduh PWA → Register → (Opsional) Input Referral Code → Verifikasi Email →
Login → Complete Profile (No HP & Alamat)
```

### 2️⃣ First Order
```
Browse Layanan → Pilih Layanan → Lihat Detail → Buat Pesanan →
(Jika data belum lengkap: Complete Profile di Modal) →
Pilih Lokasi di Map → Set Jadwal → Apply Promo → Konfirmasi Pesanan → Selesai
```

### 3️⃣ Track Order
```
Lihat Riwayat → Pilih Pesanan → Lihat Status Real-time →
(Optional) Track Kurir Position → Lihat GPS Metrics Real-time →
Terima Notifikasi Update
```

### 4️⃣ Communication
```
Buka Chat → Lihat Conversation List dengan Admin →
Pilih Conversation → Kirim Pesan / Upload File → Real-time messaging
```

### 5️⃣ Referral
```
Buka Card Referral → Copy Kode → Share ke Teman → Teman Register dengan Kode →
Dapat Reward Promo
```

### 6️⃣ Profile Management
```
Buka Profile → Edit via Modal (Nama/No HP/Email/Alamat) →
Upload Avatar (Optional) → Pilih Regional (Provinsi/Kab/Kec/Kel) →
Set GPS Coordinates → Save
```

---

## 📊 Customer Features

### Dashboard Metrics
- ✅ **Total Transaksi**: Jumlah semua pesanan
- 🔄 **Pesanan Proses**: Pesanan yang sedang dikerjakan
- ✅ **Pesanan Selesai**: Pesanan yang sudah selesai
- 🏆 **Loyalty Points**: Total poin yang dikumpulkan
- 💳 **Member Card**: Nomor kartu member digital

### Filter & Search
- 🔍 **Search by Kode**: Cari pesanan berdasarkan kode transaksi
- 📆 **Filter by Date**: Filter riwayat berdasarkan tanggal
- 🏷️ **Filter by Status**: Filter berdasarkan status pesanan
- 🗂️ **Sort**: Urutkan berdasarkan tanggal atau total

### Notifications
- 🔔 **Order Updates**: Notifikasi saat status pesanan berubah
- 🎁 **Promo Alerts**: Notifikasi promo baru
- 💰 **Referral Success**: Notifikasi saat referral berhasil
- ⏰ **Pickup Reminder**: Reminder jadwal penjemputan

---

## 🎁 Loyalty & Rewards

### Loyalty System
- 💎 **Earn Points**: Dapatkan poin setiap transaksi
- 🏆 **Member Tiers**: Level keanggotaan berdasarkan poin
- 🎟️ **Redeem Rewards**: Tukar poin dengan diskon
- 📈 **Track Progress**: Lihat progress menuju tier berikutnya

### Referral Rewards
- 🔗 **Unique Referral Code**: Kode unik untuk setiap pelanggan
- 🎁 **Dual Benefits**: Promo untuk referrer & referee
- 📊 **Performance Tracking**: Lihat total referral & conversion
- 🏅 **Leaderboard**: (Future) Ranking top referrers

---

## 📞 Kontak & Support

**Developer**: Denis Djodian Ardika
**Position**: Tech Lead
**Company**: PT. Aktif Gapura Internasional
**Repository**: [github.com/denis156/aktif-laundry](https://github.com/denis156/aktif-laundry)

---

<div align="center">

  **Aktif Laundry Customer App** © 2025 - PT. Aktif Gapura Internasional

  [![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red?style=for-the-badge)](https://github.com/denis156/aktif-laundry)
  [![PWA Ready](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://github.com/denis156/aktif-laundry)

</div>
