<div align="center">
  <img src="../public/icon.png" alt="SiAktif Logo" height="120">

  # SiAktif - Courier Application

  [![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
  [![PWA](https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
  [![GPS](https://img.shields.io/badge/GPS-Tracking-FF6B35?style=for-the-badge&logo=google-maps&logoColor=white)](https://leafletjs.com)
  [![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

  **Progressive Web App untuk Kurir Laundry dengan GPS Tracking**

  Dibuat oleh [Denis Djodian Ardika](https://github.com/denis156) - Tech Lead at PT. Aktif Global Vision

  [![GitHub](https://img.shields.io/badge/GitHub-denis156/aktif--laundry-181717?style=for-the-badge&logo=github)](https://github.com/denis156/aktif-laundry)
</div>

---

## 📋 Daftar Isi

- [Tentang Aplikasi](#-tentang-aplikasi)
- [Fitur Utama](#-fitur-utama)
- [Halaman & Modul](#-halaman--modul)
- [Komponen](#-komponen)
- [GPS & Maps Features](#-gps--maps-features)
- [PWA Features](#-pwa-features)
- [Teknologi](#-teknologi)

---

## 📱 Tentang Aplikasi

Aplikasi Courier adalah **Progressive Web App (PWA)** yang dirancang khusus untuk kurir laundry dalam mengelola pengiriman dan penjemputan. Dilengkapi dengan **GPS tracking real-time**, **route optimization**, dan **maps integration** menggunakan Leaflet.js untuk memudahkan navigasi ke lokasi customer.

> **PWA + GPS Enabled**: Aplikasi dapat diinstall di smartphone dan menggunakan GPS untuk tracking lokasi real-time serta menampilkan rute pengiriman di peta interaktif.

### 📸 Screenshots

<div align="center">

#### 🌞 Light Mode
![Courier Dashboard Light](../public/images/KurirDashboardLight.png)

#### 🌙 Dark Mode
![Courier Dashboard Dark](../public/images/KurirDashboardDark.png)

#### 🗺️ Route & Maps
![Courier Route List with Maps](../public/images/KurirRuteListPage.png)
*Halaman Rute dengan peta menampilkan lokasi kurir dan titik-titik pesanan yang akan diantar*

</div>

---

## ✨ Fitur Utama

### 🏠 Beranda & Dashboard
![Dashboard](https://img.shields.io/badge/Module-Dashboard-blue?style=flat-square)

- **Total Pengiriman**: Statistik total pengiriman kurir
- **Pesanan Selesai**: Jumlah pesanan yang sudah diselesaikan
- **Antar & Jemput**: Split metrics untuk delivery & pickup
- **Quick Access**: Shortcut ke Rute, Aktifitas, Profile, Pengaturan
- **Pesanan Aktif**: List pesanan yang sedang dikerjakan
- **Auto Refresh**: Real-time update setiap 10 detik

### 🗺️ Rute & Navigation
![Route](https://img.shields.io/badge/Module-Route-green?style=flat-square)

- **Interactive Maps**: Peta dengan marker lokasi kurir dan customer
- **Real-time GPS**: Tracking lokasi kurir secara real-time
- **Route List**: Daftar pesanan aktif dengan alamat lengkap
- **Distance Calculation**: Perhitungan jarak dari lokasi kurir ke customer
- **Map Markers**: Pin untuk setiap lokasi pengiriman/penjemputan
- **My Location**: Tombol untuk center map ke lokasi kurir
- **Multi-Destination**: Tampilan multiple markers untuk banyak pesanan
- **Route Detail**: Detail lengkap pesanan dengan navigasi ke maps

### 📦 Detail Rute
![Route Detail](https://img.shields.io/badge/Module-Route_Detail-orange?style=flat-square)

- **Customer Info**: Nama, alamat, nomor HP pelanggan
- **Order Summary**: Detail layanan, berat/jumlah, total bayar
- **Pickup/Delivery Type**: Indikator jemput atau antar
- **Navigation Button**: Buka Google Maps/Waze untuk navigasi
- **Status Update**: Update status pesanan (Dalam Perjalanan, Selesai)
- **Upload Bukti**: Upload foto bukti delivery
- **Timeline**: Timeline pengiriman (jadwal, mulai, selesai)
- **Contact Customer**: Tombol untuk hubungi customer via WA/Telp

### 📋 Aktifitas
![Activity](https://img.shields.io/badge/Module-Activity-purple?style=flat-square)

- **History Pengiriman**: Riwayat semua pengiriman kurir
- **Filter by Status**: Filter berdasarkan status pengiriman
- **Filter by Type**: Filter jemput atau antar
- **Search**: Cari berdasarkan kode transaksi atau nama customer
- **Detail Aktifitas**: Lihat detail setiap pengiriman
- **Earnings Tracking**: Track pendapatan dari pengiriman
- **Performance Stats**: Statistik performa kurir

### 👤 Profile & Pengaturan
![Profile](https://img.shields.io/badge/Module-Profile-gray?style=flat-square)

- **Profile Management**: Update profil, foto, dan data diri
- **Vehicle Info**: Data kendaraan (no polisi, jenis)
- **Bank Account**: Informasi rekening untuk gaji/bonus
- **Emergency Contact**: Kontak darurat untuk keamanan
- **Change Password**: Ubah password akun
- **App Settings**: Pengaturan notifikasi dan GPS
- **Logout**: Keluar dari akun

### 🔐 Authentication
![Auth](https://img.shields.io/badge/Module-Auth-red?style=flat-square)

- **Login**: Masuk dengan email/no HP dan password
- **Forgot Password**: Reset password via email
- **Email Verification**: Verifikasi email (optional)
- **Remember Me**: Login otomatis
- **Secure Session**: Session management untuk keamanan

---

## 🗂️ Halaman & Modul

### Authentication
```
📁 /kurir/auth/
  ├── 🔐 login                    # Login page
  ├── 🔑 forgot-password          # Forgot password page
  ├── 🔄 reset-password           # Reset password page
  └── ✉️ verify-email             # Email verification page
```

### Main Application
```
📁 /kurir/
  ├── 🏠 beranda                  # Dashboard dengan statistik pengiriman
  ├── 🗺️ rute                     # Maps dengan list pesanan aktif (GPS tracking)
  ├── 📋 aktifitas                # Riwayat semua pengiriman
  ├── 👤 profile                  # Profile management
  └── ⚙️ pengaturan               # App settings
```

### Route Module
```
📁 /kurir/rute/
  ├── 📍 rute                     # Main route page dengan maps & list
  └── 🧭 rute-detail/{id}         # Detail rute untuk navigasi & update status
```

### Activity Module
```
📁 /kurir/aktifitas/
  ├── 📋 aktifitas                # List semua aktifitas pengiriman
  └── 👁️ detail-aktifitas/{id}    # Detail pengiriman history
```

---

## 🧩 Komponen

Aplikasi Kurir dilengkapi dengan komponen-komponen khusus untuk delivery operations:

### Navigation Components
| Komponen | Deskripsi | Lokasi |
|----------|-----------|--------|
| **Top Nav** | Header navigasi dengan logo dan kurir info | `kurir/component/top-nav` |
| **Bottom Nav** | Bottom navigation bar untuk menu utama | `kurir/component/bottom-nav` |

### Maps Components
| Komponen | Deskripsi | Lokasi |
|----------|-----------|--------|
| **Lokasi Saya** | Interactive map dengan GPS tracking dan markers | `kurir/component/lokasi-saya` |

---

## 🗺️ GPS & Maps Features

### Real-time GPS Tracking
![GPS](https://img.shields.io/badge/GPS-Real--time_Tracking-FF6B35?style=for-the-badge&logo=google-maps&logoColor=white)

#### Location Features
- 📍 **Current Location**: Track lokasi kurir real-time
- 🎯 **Auto Center**: Auto-center peta ke lokasi kurir
- 📌 **Customer Markers**: Pin untuk setiap lokasi customer
- 🔄 **Live Updates**: GPS position update otomatis
- 📏 **Distance Calculation**: Hitung jarak ke setiap lokasi
- 🧭 **Compass Direction**: Arah ke lokasi customer

#### Map Interaction
- 🗺️ **Leaflet.js Maps**: Interactive maps dengan OpenStreetMap
- 🖱️ **Touch Gestures**: Pinch to zoom, drag to pan
- 📱 **Mobile Optimized**: Touch-friendly controls
- 🎨 **Custom Markers**: Icon berbeda untuk jemput/antar
- 🔍 **Zoom Controls**: Zoom in/out untuk detail area
- 📍 **My Location Button**: Quick center ke lokasi kurir

#### Navigation Integration
- 🚗 **Google Maps**: Buka di Google Maps untuk turn-by-turn navigation
- 🗺️ **Waze Integration**: Opsi navigasi via Waze
- 🧭 **Route Planning**: Optimize route untuk multiple destinations
- 📱 **Deep Links**: Direct link ke aplikasi maps

### Location Tracking
- ✅ **Continuous Tracking**: GPS tracking selama delivery
- 💾 **Track History**: Simpan tracking history untuk audit
- 📊 **Distance Traveled**: Total jarak tempuh
- ⏱️ **Time Tracking**: Waktu mulai dan selesai delivery
- 🔋 **Battery Optimized**: Efficient GPS usage untuk hemat baterai

---

## 📱 PWA Features

### Progressive Web App Capabilities
![PWA](https://img.shields.io/badge/PWA-Full_Support-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)

#### Installation
- ✅ **Add to Home Screen**: Install seperti native app
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
- 💾 **Cache Strategy**: Smart caching untuk maps tiles
- 🔋 **Battery Efficient**: Optimized untuk penggunaan battery

#### Mobile Optimization
- 📱 **Touch Optimized**: Large touch targets
- 🎨 **Responsive Design**: Adaptive layout
- 🌈 **Theme Color**: Custom theme color
- 📍 **Background Geolocation**: GPS tracking di background
- 🔔 **Push Notifications**: Notifikasi pesanan baru

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

### Maps & GPS
![PWA](https://img.shields.io/badge/PWA-Service_Worker-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)
![Leaflet](https://img.shields.io/badge/Leaflet.js-Maps-199900?style=for-the-badge&logo=leaflet&logoColor=white)
![GPS](https://img.shields.io/badge/Geolocation_API-GPS-FF6B35?style=for-the-badge)

### Tools
![Laravel Herd](https://img.shields.io/badge/Laravel_Herd-Development-FF2D20?style=for-the-badge)
![Bun](https://img.shields.io/badge/Bun-Asset_Build-000000?style=for-the-badge&logo=bun&logoColor=white)

</div>

---

## 🎨 UI/UX Features

### Mobile-First Design
![iOS](https://img.shields.io/badge/iOS-Compatible-000000?style=flat-square&logo=apple&logoColor=white)
![Android](https://img.shields.io/badge/Android-Compatible-3DDC84?style=flat-square&logo=android&logoColor=white)
![Mobile](https://img.shields.io/badge/Mobile-Optimized-success?style=flat-square)
![Maps](https://img.shields.io/badge/Maps-Full_Screen-success?style=flat-square)

### Theme Support
![Light Mode](https://img.shields.io/badge/Light_Mode-✓-yellow?style=flat-square)
![Dark Mode](https://img.shields.io/badge/Dark_Mode-✓-black?style=flat-square)

### User Experience
- ⚡ **SPA Experience**: No page reload dengan Livewire
- 🔄 **Real-time Updates**: Auto-refresh pesanan aktif
- ✨ **Smooth Animations**: Transisi smooth dan natural
- 👆 **Touch Gestures**: Swipe, tap optimized untuk maps
- 🚀 **Quick Actions**: FAB untuk akses rute cepat
- 💬 **Toast Notifications**: Feedback untuk setiap aksi
- 📍 **Full Screen Maps**: Peta full screen dengan controls
- 🎯 **Bottom Navigation**: Easy thumb reach
- 📞 **Quick Contact**: Tombol cepat hubungi customer
- 🧭 **Navigation Shortcuts**: Quick open Google Maps/Waze

---

## 🚚 Courier Journey

### 1️⃣ Login & Start Day
```
Login → Lihat Dashboard → Check Pesanan Aktif di Rute
```

### 2️⃣ Pickup Flow
```
Buka Rute → Lihat Maps & Marker Customer → Pilih Pesanan Jemput →
Klik Navigasi → Perjalanan ke Lokasi → Update Status "Dalam Perjalanan" →
Sampai Lokasi → Jemput Cucian → Upload Foto Bukti → Update "Selesai"
```

### 3️⃣ Delivery Flow
```
Buka Rute → Lihat Maps & Marker Customer → Pilih Pesanan Antar →
Klik Navigasi → Perjalanan ke Lokasi → Update Status "Dalam Perjalanan" →
Sampai Lokasi → Antar Cucian → Upload Foto Bukti → Update "Selesai"
```

### 4️⃣ End of Day
```
Lihat Aktifitas → Review Semua Pengiriman Hari Ini → Check Statistik
```

---

## 📊 Courier Features

### Dashboard Metrics
- 📦 **Total Pengiriman**: Total semua pengiriman kurir
- ✅ **Pesanan Selesai**: Pesanan yang sudah diselesaikan
- 🚚 **Total Antar**: Jumlah delivery
- 📥 **Total Jemput**: Jumlah pickup
- 📈 **Performance Stats**: Statistik performa harian/bulanan

### Route Optimization
- 🗺️ **Visual Route**: Lihat semua lokasi di peta
- 📏 **Distance Info**: Jarak dari lokasi kurir ke customer
- 🎯 **Nearest First**: Urutkan pesanan berdasarkan jarak terdekat
- 🔄 **Dynamic Updates**: Update route saat ada pesanan baru
- 📍 **Multi-Stop**: Handle multiple pickup/delivery dalam satu trip

### Status Management
- 🟡 **Dijadwalkan**: Pesanan yang dijadwalkan untuk hari ini
- 🔵 **Dalam Perjalanan**: Status saat kurir sedang on the way
- 🟢 **Selesai**: Pesanan yang sudah diselesaikan
- 🔴 **Batal**: Pesanan yang dibatalkan

### Evidence Upload
- 📸 **Photo Upload**: Upload foto bukti delivery
- 💾 **Auto Save**: Simpan otomatis ke server
- 🖼️ **Image Preview**: Preview sebelum upload
- 📱 **Camera Access**: Akses kamera langsung dari app

---

## 🔔 Notifications

### Real-time Alerts
- 📬 **New Assignment**: Notifikasi pesanan baru assigned
- 📍 **Nearby Delivery**: Alert saat mendekati lokasi customer
- ✅ **Status Update**: Konfirmasi setiap status update
- 💰 **Payment Received**: Notifikasi pembayaran dari customer
- ⏰ **Schedule Reminder**: Reminder jadwal pickup/delivery

---

## 🔐 Security Features

- 🔒 **Authentication**: Laravel Sanctum untuk session management
- 🛡️ **Courier Verification**: Hanya kurir terverifikasi yang bisa akses
- ✅ **CSRF Protection**: Built-in Laravel CSRF protection
- 🔑 **Password Hashing**: Bcrypt password hashing
- 📧 **Email Verification**: Optional email verification
- 📝 **Activity Logging**: Audit trail untuk semua aktifitas
- 🚫 **Session Timeout**: Auto logout untuk keamanan
- 📍 **Location Privacy**: GPS data hanya untuk operational purposes

---

## 📱 Maps Integration Deep Dive

### Leaflet.js Implementation
```javascript
// Features Implemented:
✅ Real-time GPS tracking
✅ Multiple custom markers (kurir & customers)
✅ Auto-center to current location
✅ Polyline untuk route visualization
✅ Popup info untuk setiap marker
✅ Distance calculation
✅ Touch-optimized controls
✅ Responsive map tiles
```

### Marker Types
- 🔴 **Kurir Marker**: Real-time location kurir (red marker)
- 🟢 **Pickup Marker**: Lokasi jemput cucian (green marker)
- 🔵 **Delivery Marker**: Lokasi antar cucian (blue marker)
- ⚪ **Completed Marker**: Pesanan selesai (gray marker)

### Map Controls
- 🎯 **My Location**: Center map ke GPS kurir
- ➕ **Zoom In/Out**: Control zoom level
- 📏 **Scale**: Tampilan skala jarak
- 🧭 **Attribution**: Map data attribution

---

## 💼 Courier Management

### Performance Tracking
- 📊 **Daily Stats**: Statistik harian pengiriman
- 📈 **Weekly/Monthly Reports**: Laporan performa bulanan
- ⭐ **Rating**: Customer rating untuk kurir
- 💰 **Earnings**: Track pendapatan dari delivery fee
- 🏆 **Achievements**: Badge untuk milestone tertentu

### Emergency Features
- 🆘 **Emergency Contact**: Quick dial kontak darurat
- 📞 **Support Hotline**: Hubungi support jika ada masalah
- ⚠️ **Report Issue**: Laporkan kendala pengiriman
- 🚨 **SOS Button**: (Future) Emergency panic button

---

## 📞 Kontak & Support

**Developer**: Denis Djodian Ardika
**Position**: Tech Lead
**Company**: PT. Aktif Global Vision
**Repository**: [github.com/denis156/aktif-laundry](https://github.com/denis156/aktif-laundry)

---

<div align="center">

  **SiAktif Courier App** © 2025 - PT. Aktif Global Vision

  [![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red?style=for-the-badge)](https://github.com/denis156/aktif-laundry)
  [![PWA Ready](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://github.com/denis156/aktif-laundry)
  [![GPS Enabled](https://img.shields.io/badge/GPS-Enabled-FF6B35?style=for-the-badge&logo=google-maps&logoColor=white)](https://github.com/denis156/aktif-laundry)

</div>
