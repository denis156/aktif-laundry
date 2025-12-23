<div align="center">
  <img src="public/icon.png" alt="Aktif Laundry" height="150">

  # Aktif Laundry Management System

  [![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
  [![PWA](https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
  [![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

  **Sistem Manajemen Laundry Terintegrasi dengan PWA & GPS Tracking**

  Dikembangkan untuk **PT. Aktif Gapura Internasional**

  ---

  **Tech Lead**: [Denis Djodian Ardika](https://github.com/denis156)

  [![Private Repository](https://img.shields.io/badge/Status-Private_Project-red?style=flat-square)](https://github.com/denis156/aktif-laundry)
  [![Production](https://img.shields.io/badge/Status-Production-success?style=flat-square)](https://github.com/denis156/aktif-laundry)

</div>

---

## 📖 Tentang Proyek

**Aktif Laundry** adalah sistem manajemen laundry modern yang dikembangkan khusus untuk mengoptimalkan operasional bisnis laundry **PT. Aktif Gapura Internasional**. Sistem ini terdiri dari 3 aplikasi terintegrasi yang dirancang untuk memudahkan pengelolaan transaksi, pelacakan pesanan, dan koordinasi antara admin, pelanggan, dan kurir.

### 🎯 Tujuan Bisnis

Proyek ini dikembangkan untuk:
- ✅ **Digitalisasi Operasional**: Mengubah proses manual menjadi digital dan terotomasi
- ✅ **Meningkatkan Efisiensi**: Mempercepat proses transaksi dan pengiriman
- ✅ **Customer Experience**: Memberikan kemudahan pelanggan dalam memesan dan tracking pesanan
- ✅ **GPS Tracking**: Real-time tracking kurir dan optimasi rute pengiriman
- ✅ **Loyalty & Referral**: Sistem reward untuk meningkatkan customer retention

---

## 🏗️ Arsitektur Sistem

Sistem ini terdiri dari **3 aplikasi** yang terintegrasi:

<div align="center">

| Aplikasi | Tipe | Platform | Pengguna |
|----------|------|----------|----------|
| **Management** | Web Dashboard | Desktop/Tablet | Admin, Kasir, Staf |
| **Customer** | Progressive Web App | Mobile (iOS/Android) | Pelanggan |
| **Courier** | Progressive Web App + GPS | Mobile (iOS/Android) | Kurir |

</div>

### 🔗 Stack Teknologi

- **Backend**: Laravel 12, PHP 8.4, Livewire 3, MySQL 8
- **Frontend**: Tailwind CSS 4, Alpine.js, Mary UI, DaisyUI
- **PWA**: Service Worker, Web App Manifest, iOS Splash Screens
- **Maps & GPS**: Leaflet.js, Mapbox Search Box API, Geolocation API
- **Push Notifications**: Firebase Cloud Messaging (FCM) dengan custom vibration patterns
- **Integration**: WhatsApp (Fonnte API), Google Maps/Waze
- **Real-time**: Cache-based GPS Tracking, Live Chat System, FCM Push Notifications

---

## 📚 Dokumentasi

Dokumentasi lengkap tersedia untuk setiap komponen sistem:

### 📱 Dokumentasi Aplikasi

1. **[Management Application](docs/apps-management.md)**
   - Dashboard admin dengan analytics & charts (Chart.js)
   - Manajemen master data (pelanggan, kurir, staf, layanan, promo)
   - Point of Sale (POS) / Kasir
   - Sistem referral & loyalty
   - Integrasi WhatsApp (Fonnte API)
   - **Chat System**: Multi-participant dengan file sharing
   - **Live Tracking**: Track semua kurir aktif real-time
   - **Mapbox Integration**: Search Box API untuk location picker
   - Non-PWA (Web-based)

2. **[Customer Application](docs/apps-pelanggan.md)**
   - Progressive Web App untuk pelanggan
   - Browse layanan & promo
   - Pemesanan online dengan maps integration
   - **Track Courier**: Live tracking posisi kurir untuk pesanan
   - **Chat with Admin**: Real-time messaging dengan file sharing
   - Loyalty points & referral system
   - **Smart Profile**: Auto-validation data lengkap
   - Installable di smartphone

3. **[Courier Application](docs/apps-kurir.md)**
   - Progressive Web App untuk kurir
   - **GPS Tracking**: Real-time dengan speed, bearing, accuracy
   - **Dual Chat**: Komunikasi dengan Admin & Pelanggan
   - Route optimization dengan maps
   - Multi-destination delivery
   - Upload bukti delivery
   - **Cache-based Tracking**: 5 menit TTL untuk live updates
   - Navigation integration (Google Maps/Waze)

### 🗄️ Dokumentasi Database

4. **[Database Schema](docs/database-diagram.md)**
   - Entity Relationship Diagram (ERD)
   - 25 tables dengan 210+ fields
   - Detail setiap tabel dan relationship
   - Business logic & indexing strategy
   - Chat system dengan polymorphic relationships

---

## 🌟 Fitur Unggulan

### 💼 Management Dashboard
- 📊 Real-time analytics & statistics
- 📈 Dynamic charts (line, area, bar, donut) dengan Chart.js
- 🧾 Multi-service & multi-promo transaction
- 🎁 Referral & loyalty management
- 📱 WhatsApp notification integration
- 🔔 **Firebase Push Notifications**: Send real-time notifications ke Kurir & Pelanggan
- 💬 **Chat System**: Multi-participant chat dengan Kurir & Pelanggan
- 📍 **Live Tracking**: Track semua kurir aktif di satu map real-time
- 🗺️ **Mapbox Integration**: Search Box API untuk location picker
- 📋 **Regional Address**: Cascading dropdown Indonesia (Provinsi → Kelurahan)

### 📱 Customer PWA
- 🏠 Dashboard dengan statistik pesanan
- 🛒 Browse & order layanan laundry
- 🗺️ Set alamat pickup dengan maps
- 🎁 Promo & referral code system
- 💳 Member card & loyalty points
- 📲 Installable seperti native app
- 🔔 **Firebase Push Notifications**: Real-time notifications untuk order updates, chat, dan promo
- 💬 **Chat with Admin**: Real-time messaging dengan file sharing
- 📍 **Track Courier**: Live tracking posisi kurir untuk pesanan aktif
- 👤 **Smart Profile**: Auto-detect data belum lengkap dengan validation
- 📋 **Regional Address**: Form alamat lengkap dengan GPS coordinates

### 🚚 Courier PWA + GPS
- 📍 **Real-time GPS Tracking**: Speed, bearing, accuracy metrics
- 🗺️ Interactive maps dengan multi-marker
- 🧭 Route optimization & navigation
- 📦 Multi-destination pickup/delivery
- 📸 Upload foto bukti delivery
- ⚡ Performance tracking & statistics
- 🔔 **Firebase Push Notifications**: Real-time notifications untuk order assignment, chat, dengan custom vibration
- 💬 **Dual Chat**: Chat dengan Admin/Staf & Pelanggan
- 📤 **File Sharing**: Kirim foto & dokumen (max 5MB)
- 🔄 **Cache-based Tracking**: 5 menit TTL untuk real-time updates
- 📋 **Regional Profile**: Alamat lengkap dengan cascading dropdowns

---

## 🚀 Fitur Advanced

### 🔔 Firebase Push Notification System
Sistem notifikasi real-time terintegrasi menggunakan Firebase Cloud Messaging:

- **Real-time Delivery**: Instant push notifications untuk semua users
- **Multi-device Support**: Satu user bisa terima notifikasi di multiple devices
- **Custom Vibration**: Pattern vibration berbeda untuk setiap tipe notifikasi
- **Background & Foreground**: Smart handling untuk app state
- **Auto Token Management**: Registration, refresh, dan cleanup otomatis
- **WebView Detection**: Support AktifLaundryApp native wrapper
- **Deep Linking**: Click notification langsung ke halaman terkait
- **Delivery Analytics**: Track delivery rate dan open rate

**Notification Scenarios:**
- 📦 **Kurir**: Order assignment, status updates, chat messages, route changes
- 👥 **Pelanggan**: Order confirmations, courier updates, promo alerts, loyalty points
- 🔹 **Admin/Staf**: System alerts, chat messages from kurir/pelanggan

### 💬 Chat System
Sistem chat terintegrasi untuk komunikasi real-time antar semua pengguna:

- **Multi-Participant**: User (Admin/Staf), Kurir, Pelanggan
- **File Sharing**: Support images (jpg, jpeg, png, gif) & documents (pdf, doc, docx)
- **Max File Size**: 5MB per file
- **Max Message**: 5000 karakter
- **Read Receipts**: Tracking status baca pesan
- **Search & Filter**: Cari percakapan dan pesan
- **Unread Counter**: Badge untuk pesan belum dibaca
- **Auto-delete**: Files terhapus otomatis saat conversation dihapus
- **Storage**: `storage/app/public/chat-attachments/`
- **Push Notification Integration**: Notifikasi pesan baru via Firebase FCM

**Use Cases:**
- 🔹 Kurir ↔ Admin/Staf: Koordinasi pengiriman
- 🔹 Kurir ↔ Pelanggan: Konfirmasi lokasi pickup/delivery
- 🔹 Pelanggan ↔ Admin/Staf: Customer support & inquiries

### 📍 GPS Tracking System
Sistem pelacakan GPS real-time dengan data metrics lengkap:

- **Speed Tracking**: Kecepatan kurir dalam km/h
- **Bearing**: Arah kompas (0-360°) untuk rotasi marker
- **Accuracy**: Radius akurasi GPS dalam meter
- **Cache-based**: Data disimpan di cache dengan 5 menit TTL
- **Live Updates**: Streaming real-time ke Management & Customer app
- **Multi-Courier**: Management bisa track semua kurir aktif sekaligus
- **Customer Tracking**: Pelanggan track kurir untuk pesanan mereka
- **Active Filter**: Hanya tampilkan kurir yang update < 2 menit

**Technical Stack:**
- Frontend: Leaflet.js (Kurir & Pelanggan), Mapbox (Management)
- Cache: Laravel Cache dengan key `kurir_tracking_{id}`
- TTL: 5 menit dari last update
- Maps: OpenStreetMap tiles

### 🗺️ Regional Address System
Form alamat Indonesia-specific dengan cascading selection:

- **4-Level Cascade**: Provinsi → Kabupaten/Kota → Kecamatan → Kelurahan
- **Auto-format**: Generate full address display otomatis
- **GPS Coordinates**: Optional latitude/longitude
- **Smart Validation**: Deteksi data belum lengkap
- **All Apps**: Tersedia di Management, Customer, dan Courier apps

---

## 🔒 Catatan Penting

> **⚠️ PRIVATE PROJECT**: Proyek ini adalah sistem proprietary yang dikembangkan khusus untuk PT. Aktif Gapura Internasional dan **BUKAN proyek open source**. Semua hak cipta dilindungi.

### 🚫 Tidak untuk:
- ❌ Redistribusi
- ❌ Penggunaan komersial oleh pihak lain
- ❌ Modifikasi tanpa izin
- ❌ Public contribution

### ✅ Hanya untuk:
- ✅ Penggunaan internal PT. Aktif Gapura Internasional
- ✅ Development & maintenance oleh authorized team
- ✅ Documentation & reference purposes

---

## 👥 Tim Development

**Tech Lead**: Denis Djodian Ardika
**Company**: PT. Aktif Gapura Internasional
**Repository**: Private (Internal Use Only)

---

## 📄 License

**Proprietary Software** - © 2025 PT. Aktif Gapura Internasional. All Rights Reserved.

Sistem ini dilindungi oleh hak cipta dan merupakan properti eksklusif PT. Aktif Gapura Internasional. Penggunaan, modifikasi, atau distribusi tanpa izin tertulis dilarang keras.

---

<div align="center">

  **Aktif Laundry Management System** © 2025

  Dikembangkan dengan ❤️ untuk PT. Aktif Gapura Internasional

  [![Laravel](https://img.shields.io/badge/Powered_by-Laravel_12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)

</div>
