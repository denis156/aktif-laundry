<div align="center">
  <img src="../public/icon.png" alt="SiAktif Logo" height="120">

  # SiAktif - Courier Application

  [![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
  [![PWA](https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
  [![GPS](https://img.shields.io/badge/GPS-Tracking-FF6B35?style=for-the-badge&logo=google-maps&logoColor=white)](https://leafletjs.com)
  [![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

  **Progressive Web App untuk Kurir Laundry dengan GPS Tracking**

  Dibuat oleh [Denis Djodian Ardika](https://github.com/denis156) - Tech Lead at PT. Aktif Gapura Internasional

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

Aplikasi Courier adalah **Progressive Web App (PWA)** yang dirancang khusus untuk kurir laundry dalam mengelola pengiriman dan penjemputan. Dilengkapi dengan **GPS tracking real-time** (dengan speed & bearing), **route optimization**, **real-time chat system**, dan **maps integration** menggunakan Leaflet.js untuk memudahkan navigasi ke lokasi customer.

> **PWA + GPS + Chat Enabled**: Aplikasi dapat diinstall di smartphone dan menggunakan GPS untuk tracking lokasi real-time serta menampilkan rute pengiriman di peta interaktif. Dilengkapi dengan sistem chat real-time untuk komunikasi dengan admin dan pelanggan, termasuk file sharing untuk bukti delivery.

### ✨ What's New in This Documentation
- 🔔 **Firebase Push Notifications**: Real-time notifications dengan FCM, vibration patterns, dan auto token management
- 💬 **Chat System**: Dokumentasi lengkap fitur chat dengan admin dan pelanggan
- 🗺️ **Enhanced GPS Tracking**: Speed, bearing, dan accuracy monitoring
- 📍 **Location Caching**: Public tracking API untuk pelanggan
- 👤 **Profile Management**: Regional address system dengan cascading dropdown
- 📊 **Detailed Stats**: Penjelasan lengkap metrics dan status management
- 🎨 **UI/UX Details**: Modal system, avatar handling, stream updates
- 🔐 **Security Features**: Multi-guard auth dan file validation

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

### 💬 Chat & Communication
![Chat](https://img.shields.io/badge/Module-Chat-cyan?style=flat-square)

- **Real-time Messaging**: Chat langsung dengan admin dan pelanggan
- **Conversation List**: Daftar percakapan aktif dengan unread count
- **Multi-participant**: Chat dengan Admin (User) dan Pelanggan
- **File Sharing**: Kirim foto, dokumen (PDF, DOC, DOCX) max 5MB
- **Image Preview**: Preview gambar sebelum kirim & full screen viewer
- **Message Status**: Read/unread indicator untuk setiap pesan
- **Auto Scroll**: Scroll otomatis ke pesan terbaru
- **Search**: Cari percakapan berdasarkan nama atau pesan
- **Delete Conversation**: Hapus percakapan beserta file attachment
- **Participant Info**: Info nama, tipe (Admin/Pelanggan), avatar
- **New Conversation**: Buat percakapan baru dengan pilih participant

### 👤 Profile & Pengaturan
![Profile](https://img.shields.io/badge/Module-Profile-gray?style=flat-square)

- **Profile Management**: Update profil, foto, dan data diri
- **Avatar Upload**: Upload foto profil max 5MB
- **Contact Info**: Edit nama, no HP, dan email
- **Address Management**: Update alamat lengkap dengan regional selector
- **GPS Coordinates**: Simpan latitude/longitude lokasi kurir
- **Regional Selection**: Pilih Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan
- **Address Preview**: Preview alamat lengkap auto-generated
- **Change Password**: Ubah password akun (via pengaturan)
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
  ├── 💬 chat                     # Chat dengan admin dan pelanggan
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

### Chat Module
```
📁 /kurir/chat/
  ├── 💬 chat                     # List semua percakapan
  └── 💭 chat-room/{conversation} # Chat room untuk percakapan tertentu
```

---

## 🧩 Komponen

Aplikasi Kurir dilengkapi dengan komponen-komponen khusus untuk delivery operations:

### Navigation Components
| Komponen | Deskripsi | Fitur | Lokasi |
|----------|-----------|-------|--------|
| **Top Nav** | Header navigasi dengan logo dan kurir info | Logo, nama kurir, notifikasi | `kurir/components/top-nav` |
| **Bottom Nav** | Bottom navigation bar untuk menu utama | 4 menu: Beranda, Aktifitas, Rute, Pengaturan dengan active state | `kurir/components/bottom-nav` |

### Maps & GPS Components
| Komponen | Deskripsi | Fitur | Lokasi |
|----------|-----------|-------|--------|
| **Lokasi Saya** | Interactive map dengan GPS tracking real-time | GPS status badge, koordinat live, akurasi, update counter, stream data real-time | `kurir/components/lokasi-saya` |

### FAB (Floating Action Button)
| Komponen | Deskripsi | Fitur | Lokasi |
|----------|-----------|-------|--------|
| **FAB Kurir** | Floating button untuk quick access ke rute | Quick link ke halaman rute dengan icon maps | `components/fab-kurir` |

---

## 🗺️ GPS & Maps Features

### Real-time GPS Tracking
![GPS](https://img.shields.io/badge/GPS-Real--time_Tracking-FF6B35?style=for-the-badge&logo=google-maps&logoColor=white)

#### Location Features
- 📍 **Current Location**: Track lokasi kurir real-time dengan latitude/longitude
- 🎯 **Auto Center**: Auto-center peta ke lokasi kurir
- 📌 **Customer Markers**: Pin untuk setiap lokasi customer
- 🔄 **Live Updates**: GPS position update otomatis setiap detik
- 📏 **Distance Calculation**: Hitung jarak ke setiap lokasi
- 🧭 **Bearing Support**: Track arah pergerakan kurir (compass bearing)
- 🚗 **Speed Tracking**: Monitor kecepatan kurir saat bergerak (km/h)
- ✨ **Accuracy Indicator**: Akurasi GPS dalam meter (±10m = excellent, ±50m = good)
- 💾 **Cache Location**: Cache lokasi untuk tracking history (5 menit)
- 📊 **Real-time Stream**: Stream koordinat dan status ke UI tanpa refresh

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

### Location Tracking & Caching
- ✅ **Continuous Tracking**: GPS tracking selama delivery dengan update otomatis
- 💾 **Tracking Cache**: Cache lokasi kurir real-time untuk public tracking (5 menit TTL)
- 📍 **Route Start Cache**: Simpan lokasi awal rute (24 jam TTL)
- 🗺️ **Track History**: History tracking dengan speed, bearing, dan accuracy
- 📊 **Distance Traveled**: Total jarak tempuh dari titik awal
- ⏱️ **Time Tracking**: Waktu mulai dan selesai delivery
- 🔋 **Battery Optimized**: Efficient GPS usage untuk hemat baterai
- 🎯 **Initial Location**: Load lokasi terakhir dari database untuk map initialization
- 📡 **Public Tracking API**: Pelanggan bisa track posisi kurir real-time via cache

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

### Communication & Notifications
![Chat](https://img.shields.io/badge/Real--time_Chat-Livewire_Polling-4E56A6?style=for-the-badge)
![File Upload](https://img.shields.io/badge/File_Upload-Livewire-FF2D20?style=for-the-badge)
![Firebase](https://img.shields.io/badge/Firebase-FCM_Push-FFCA28?style=for-the-badge&logo=firebase&logoColor=black)

### Tools
![Laravel Herd](https://img.shields.io/badge/Laravel_Herd-Development-FF2D20?style=for-the-badge)
![Bun](https://img.shields.io/badge/Bun-Asset_Build-000000?style=for-the-badge&logo=bun&logoColor=white)

</div>

### Key Technologies Breakdown

#### Laravel 12 Features Used
- 🔐 **Multi-guard Authentication**: Separate auth untuk kurir dengan `auth:kurir`
- ✅ **Email Verification**: Optional verification dengan `verified.kurir` middleware
- 📁 **File Storage**: Public disk untuk avatar dan chat attachments
- 💾 **Cache System**: Cache location tracking dengan TTL
- 🗄️ **Eloquent ORM**: Relationships dan query optimization
- 🔄 **Observers**: Auto cleanup files on conversation delete
- 📧 **Notifications**: Email dan toast notifications
- 🌐 **Localization**: Carbon locale Indonesia untuk dates

#### Livewire 3 Features Used
- 🔄 **Wire:navigate**: SPA-like navigation tanpa reload
- 📊 **Computed Properties**: Efficient data caching
- 🎯 **Wire:poll**: Auto-refresh dashboard setiap 10 detik
- 📡 **Stream API**: Real-time coordinate updates
- 📁 **File Uploads**: WithFileUploads trait untuk avatar dan chat files
- 🎭 **Events**: Custom events untuk scroll dan notifications
- 💬 **Toast**: Mary UI toast untuk user feedback
- 🔔 **On Listener**: Event listeners untuk new messages

#### GPS & Maps Technology
- 🌍 **Leaflet.js**: Open-source interactive maps
- 🗺️ **OpenStreetMap**: Free map tiles
- 📍 **Geolocation API**: Browser native GPS
- 🚗 **Speed & Bearing**: GPS velocity dan direction tracking
- 💾 **Location Cache**: Redis cache untuk real-time tracking
- 📊 **Accuracy Metrics**: GPS precision monitoring

#### Firebase & Push Notifications
- 🔥 **Firebase Cloud Messaging**: Real-time push notifications
- 🔔 **FCM SDK**: Firebase JavaScript SDK untuk web
- 📱 **Service Worker**: Background notification handling
- 🔐 **Token Management**: Auto registration, refresh, dan cleanup
- 📳 **Vibration API**: Custom vibration patterns per notification type
- 🌐 **WebView Detection**: Custom AktifLaundryApp marker detection
- 📊 **Notification Analytics**: Delivery dan open rate tracking
- 🎯 **Deep Linking**: Direct navigation dari notification

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
- ⚡ **SPA Experience**: No page reload dengan Livewire wire:navigate
- 🔄 **Real-time Updates**: Auto-refresh pesanan aktif setiap 10 detik
- ✨ **Smooth Animations**: Transisi smooth dan natural
- 👆 **Touch Gestures**: Swipe, tap optimized untuk maps
- 🚀 **Quick Actions**: FAB untuk akses rute cepat
- 💬 **Toast Notifications**: Feedback untuk setiap aksi dengan position control
- 📍 **Full Screen Maps**: Peta full screen dengan controls
- 🎯 **Bottom Navigation**: Easy thumb reach dengan 4 menu utama
- 📞 **Quick Contact**: Tombol cepat hubungi customer
- 🧭 **Navigation Shortcuts**: Quick open Google Maps/Waze
- 🗨️ **Chat Badge**: Unread message count di conversation list
- 📱 **Modal System**: Clean modal untuk edit profile, chat preview, image viewer
- 🎨 **Avatar System**: Avatar dengan fallback ke placeholder dengan initial
- 📊 **Stream Updates**: Real-time data stream tanpa full page refresh

---

## 🚚 Courier Journey

### 1️⃣ Login & Start Day
```
Login → Lihat Dashboard → Check Pesanan Aktif di Rute → Check Chat untuk pesan baru
```

### 2️⃣ Pickup Flow
```
Buka Rute → Lihat Maps & Marker Customer → Pilih Pesanan Jemput →
Klik Navigasi → GPS Tracking Aktif (Speed & Bearing monitored) →
Perjalanan ke Lokasi → Update Status "Dalam Perjalanan" →
Sampai Lokasi → Jemput Cucian → Upload Foto Bukti → Update "Selesai"
```

### 3️⃣ Delivery Flow
```
Buka Rute → Lihat Maps & Marker Customer → Pilih Pesanan Antar →
Klik Navigasi → GPS Tracking Aktif (Location cached untuk pelanggan) →
Perjalanan ke Lokasi → Update Status "Dalam Perjalanan" →
Sampai Lokasi → Antar Cucian → Upload Foto Bukti → Update "Selesai"
```

### 4️⃣ Communication Flow
```
Buka Chat → Lihat Conversation List → Pilih Chat (Admin/Pelanggan) →
Kirim Pesan / Upload File → Real-time messaging →
Atau: Buat Conversation Baru → Pilih Participant → Start Chat
```

### 5️⃣ End of Day
```
Lihat Aktifitas → Review Semua Pengiriman Hari Ini → Check Statistik →
Reply Chat yang pending → Update Profile/Settings jika perlu
```

---

## 📊 Courier Features

### Dashboard Metrics
- 📦 **Total Pengiriman**: Total semua pengiriman kurir (jemput + antar)
- ✅ **Pesanan Selesai**: Pesanan dengan status "Diambil"
- 🚚 **Total Antar**: Delivery dengan status "Selesai" atau "Diambil"
- 📥 **Total Jemput**: Pickup dengan status "Proses", "Selesai", atau "Diambil"
- 📈 **Performance Stats**: Statistik performa harian/bulanan
- 🔄 **Auto Refresh**: Dashboard auto-refresh setiap 10 detik
- 📋 **Pengantaran Aktif**: List 3 pesanan aktif terbaru
- 📜 **Transaksi Terbaru**: 3 transaksi terbaru termasuk yang menunggu assignment

### Route Optimization
- 🗺️ **Visual Route**: Lihat semua lokasi di peta dengan Leaflet.js
- 📏 **Distance Info**: Jarak dari lokasi kurir ke customer
- 🎯 **Nearest First**: Urutkan pesanan berdasarkan jarak terdekat
- 🔄 **Dynamic Updates**: Update route saat ada pesanan baru
- 📍 **Multi-Stop**: Handle multiple pickup/delivery dalam satu trip
- 🚗 **Jenis Kendaraan**: Info jenis kendaraan kurir (motor/mobil)

### Status Management
- 🟡 **Menunggu**: Pesanan yang belum assigned kurir
- 🟠 **Proses**: Pesanan dijemput, menunggu diproses
- 🔵 **Selesai**: Pesanan selesai diproses, siap diantar
- 🟢 **Diambil**: Pesanan sudah diambil pelanggan (completed)
- 🔴 **Batal**: Pesanan yang dibatalkan

### Evidence Upload
- 📸 **Photo Upload**: Upload foto bukti delivery via chat
- 💾 **Auto Save**: Simpan otomatis ke server
- 🖼️ **Image Preview**: Preview sebelum upload
- 📱 **Camera Access**: Akses kamera langsung dari app
- 📁 **File Support**: Support multiple file types (images, PDF, DOC)

---

## 🔔 Notifications & Communication

### Real-time Push Notifications (Firebase FCM)
- 📬 **New Assignment**: Push notification pesanan baru assigned dengan vibration
- 📍 **Nearby Delivery**: Alert saat mendekati lokasi customer
- ✅ **Status Update**: Konfirmasi setiap status update dengan custom vibration
- 💰 **Payment Received**: Notifikasi pembayaran dari customer
- ⏰ **Schedule Reminder**: Reminder jadwal pickup/delivery
- 💬 **New Message**: Push notification pesan baru dari chat (Admin/Pelanggan)
- 🔴 **Unread Count**: Badge unread message count di conversation list
- 📱 **Multi-device**: Notification sync across multiple devices
- 🔔 **Background/Foreground**: Smart handling untuk app state
- 🎯 **Clickable Actions**: Click notification untuk direct access ke halaman terkait

---

## 🔐 Security Features

- 🔒 **Authentication**: Multi-guard authentication (`auth:kurir`)
- 🛡️ **Courier Verification**: Hanya kurir terverifikasi yang bisa akses (`verified.kurir` middleware)
- ✅ **CSRF Protection**: Built-in Laravel CSRF protection
- 🔑 **Password Hashing**: Bcrypt password hashing
- 📧 **Email Verification**: Optional email verification dengan signed URLs
- 📝 **Activity Logging**: Audit trail untuk semua aktifitas
- 🚫 **Session Timeout**: Auto logout untuk keamanan
- 📍 **Location Privacy**: GPS data hanya untuk operational purposes
- 🔐 **Route Protection**: Guest dan authenticated route separation
- 📁 **File Upload Validation**: Strict validation untuk file types dan size
- 🚨 **Error Handling**: Proper error handling dengan user-friendly messages
- 🔄 **Session Regeneration**: Session regeneration pada login/logout

---

## 💬 Chat System Deep Dive

### Real-time Messaging Features
```javascript
// Chat Capabilities:
✅ Multi-participant chat (Admin & Pelanggan)
✅ Real-time message delivery dengan Livewire polling
✅ File upload: images (jpg, jpeg, png, gif), documents (pdf, doc, docx)
✅ Max file size: 5MB per file
✅ Message validation: max 5000 characters
✅ Auto-scroll to new messages
✅ Unread message counter per conversation
✅ Mark messages as read automatically
✅ Image preview modal before sending
✅ Full-screen image viewer for received images
✅ Delete conversation with cascade file deletion
✅ Search conversations by name or message content
✅ Last message preview in conversation list
✅ Participant type indicator (Admin/Pelanggan)
✅ Avatar support for participants
✅ Timestamp with relative time (diffForHumans)
```

### Chat Storage & Performance
- 📁 **File Storage**: Chat attachments stored in `storage/app/public/chat-attachments/`
- 💾 **Auto Cleanup**: Files deleted when conversation deleted (via Observer)
- 🔄 **Message Limit**: Load last 50 messages per conversation
- 📊 **Efficient Queries**: ChatHelper with optimized queries
- 🔍 **Search Index**: Search by participant name or message content
- 📱 **Mobile Optimized**: Touch-friendly chat UI

---

## 🔔 Push Notifications & Firebase

### Firebase Cloud Messaging (FCM)
```javascript
// Push Notification Features:
✅ Real-time push notifications untuk pesanan baru
✅ Notifikasi perubahan status pesanan
✅ Notifikasi pesan chat baru dari Admin/Pelanggan
✅ Custom vibration patterns untuk setiap tipe notifikasi
✅ Background & foreground notification handling
✅ Auto token registration & management
✅ Auto token cleanup on logout
✅ WebView detection untuk native app integration
✅ Notification permission request
✅ Token refresh handling
✅ Multi-device support
```

### Notification Types
- 🔔 **New Order Assignment**: Notifikasi pesanan baru assigned ke kurir
- 📦 **Order Status Update**: Update status pesanan (Dijadwalkan, Dalam Perjalanan, Selesai)
- 💬 **New Message**: Notifikasi pesan chat baru dari Admin atau Pelanggan
- ⏰ **Schedule Reminder**: Reminder jadwal pickup/delivery yang akan datang
- 📍 **Location Alert**: Alert saat mendekati lokasi customer
- ✅ **Task Completed**: Konfirmasi penyelesaian task

### Vibration Patterns
```javascript
// Vibration patterns untuk berbagai notifikasi:
- New Order: [200, 100, 200] - Double vibrate
- New Message: [100] - Single short vibrate
- Status Update: [200] - Single long vibrate
- Urgent Alert: [200, 100, 200, 100, 200] - Triple vibrate
```

### FCM Token Management
- 🔐 **Auto Registration**: Token otomatis register saat app load
- 🔄 **Auto Refresh**: Token refresh saat expired
- 💾 **Server Sync**: Token sync ke server untuk push notifications
- 🗑️ **Auto Cleanup**: Token otomatis dihapus saat logout
- 📱 **Multi-device**: Support multiple devices per kurir
- 🌐 **WebView Support**: Deteksi AktifLaundryApp WebView marker

### Firebase Service Worker
- 📡 **Background Notifications**: Receive notifications saat app di background
- 🔔 **Foreground Handling**: Custom handling saat app aktif
- 🖼️ **Rich Notifications**: Support icon, image, badge
- 🎯 **Clickable**: Click notification untuk buka app di halaman terkait
- 📊 **Analytics**: Track notification delivery & open rates

### Security & Privacy
- 🔒 **Token Encryption**: FCM token encrypted di database
- 🚫 **Permission-based**: User harus grant notification permission
- 🔐 **Server-side Validation**: Token validation sebelum send notification
- 📝 **Audit Trail**: Log semua notification yang dikirim
- 🗑️ **Privacy Compliant**: Token dihapus saat user logout

---

## 📱 Maps Integration Deep Dive

### Leaflet.js Implementation
```javascript
// Features Implemented:
✅ Real-time GPS tracking with speed & bearing
✅ Multiple custom markers (kurir & customers)
✅ Auto-center to current location
✅ Polyline untuk route visualization
✅ Popup info untuk setiap marker
✅ Distance calculation with accuracy
✅ Touch-optimized controls
✅ Responsive map tiles
✅ Location caching untuk public tracking
✅ Stream updates untuk real-time koordinat display
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

### Profile Management Deep Dive
- 👤 **Personal Info**: Kode kurir (auto-generated), nama, no HP, email
- 🖼️ **Avatar Upload**: Upload avatar dengan validation (max 5MB)
- 📍 **Address System**:
  - Detail alamat (max 500 characters)
  - Regional selector: Provinsi → Kabupaten/Kota → Kecamatan → Kelurahan
  - Cascading dropdown dengan data Indonesia
  - Auto-format alamat lengkap
  - GPS koordinat (latitude/longitude) optional
- 📞 **Phone Validation**: Format nomor HP otomatis (+62, 62, 08, 8)
- ✉️ **Email Unique**: Validasi email unique di database
- 🔄 **Modal Editing**: Setiap field edit via modal terpisah
- 💾 **Transaction Safety**: DB transaction untuk data consistency
- ✅ **Validation Feedback**: Toast notification untuk success/error
- 🗺️ **Location Integration**: Simpan koordinat untuk maps initialization

### Performance Tracking
- 📊 **Dashboard Stats**:
  - Total pengiriman (jemput + antar)
  - Pesanan selesai (status Diambil)
  - Total antar (status Selesai atau Diambil)
  - Total jemput (status Proses, Selesai, atau Diambil)
- 📈 **Activity Grouping**: Group by Hari Ini, Minggu Ini, Bulan Ini, Lebih Lama
- 📅 **Monthly Tracking**: Track performa per bulan dengan nama bulan Indonesia (locale)
- ⭐ **Rating**: Customer rating untuk kurir
- 💰 **Earnings**: Track pendapatan dari delivery fee
- 🏆 **Achievements**: Badge untuk milestone tertentu
- 📋 **Pagination**: Load more aktifitas dengan lazy loading (10 per page)

### Communication Features
- 💬 **Chat Integration**: Direct chat dengan admin dan pelanggan
- 📞 **Quick Contact**: Tombol hubungi customer via WA/Telp
- 📧 **Email Support**: Email untuk komunikasi formal
- 📱 **File Sharing**: Kirim foto bukti delivery via chat
- 🔔 **Push Notifications**: Real-time notification untuk pesan baru

### Emergency Features
- 🆘 **Emergency Contact**: Quick dial kontak darurat
- 📞 **Support Hotline**: Hubungi support jika ada masalah via chat
- ⚠️ **Report Issue**: Laporkan kendala pengiriman
- 🚨 **SOS Button**: (Future) Emergency panic button

---

## 📞 Kontak & Support

**Developer**: Denis Djodian Ardika
**Position**: Tech Lead
**Company**: PT. Aktif Gapura Internasional
**Repository**: [github.com/denis156/aktif-laundry](https://github.com/denis156/aktif-laundry)

---

<div align="center">

  **SiAktif Courier App** © 2025 - PT. Aktif Gapura Internasional

  [![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red?style=for-the-badge)](https://github.com/denis156/aktif-laundry)
  [![PWA Ready](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://github.com/denis156/aktif-laundry)
  [![GPS Enabled](https://img.shields.io/badge/GPS-Enabled-FF6B35?style=for-the-badge&logo=google-maps&logoColor=white)](https://github.com/denis156/aktif-laundry)

</div>
