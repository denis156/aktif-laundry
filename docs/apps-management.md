<div align="center">
  <img src="../public/icon.png" alt="Aktif Laundry Logo" height="120">

  # Aktif Laundry - Management Application

  [![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
  [![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
  [![Mary UI](https://img.shields.io/badge/Mary_UI-Latest-FF69B4?style=for-the-badge)](https://mary-ui.com)

  **Admin Dashboard untuk Pengelolaan Laundry**

  Dibuat oleh [Denis Djodian Ardika](https://github.com/denis156) - Tech Lead at PT. Aktif Global Vision

  [![GitHub](https://img.shields.io/badge/GitHub-denis156/aktif--laundry-181717?style=for-the-badge&logo=github)](https://github.com/denis156/aktif-laundry)
</div>

---

## 📋 Daftar Isi

- [Tentang Aplikasi](#-tentang-aplikasi)
- [Fitur Utama](#-fitur-utama)
- [Halaman & Modul](#-halaman--modul)
- [Komponen](#-komponen)
- [Teknologi](#-teknologi)

---

## 📱 Tentang Aplikasi

Aplikasi Management adalah **dashboard admin** berbasis web untuk mengelola seluruh operasional laundry. Aplikasi ini dibangun dengan **Laravel 12**, **Livewire 3**, dan **Mary UI** untuk memberikan pengalaman pengguna yang cepat dan responsif tanpa perlu reload halaman.

> **Catatan**: Aplikasi Management **tidak menggunakan PWA**, berbeda dengan aplikasi Kurir dan Pelanggan yang merupakan Progressive Web Apps.

### 📸 Screenshots

<div align="center">

#### 🌞 Light Mode
![Management Dashboard Light](../public/images/ManagementDashboardLight.png)

#### 🌙 Dark Mode
![Management Dashboard Dark](../public/images/ManagementDashboardDark.png)

</div>

---

## ✨ Fitur Utama

### 🎯 Dashboard & Analytics
- **Statistik Real-time**: Total transaksi, pendapatan (hari ini, bulan ini, dan keseluruhan)
- **Grafik Dinamis**: Line chart, area chart, dan bar chart untuk visualisasi data transaksi
- **Top 5 Layanan**: Menampilkan layanan paling populer dengan ranking
- **Status Transaksi**: Donut chart untuk melihat distribusi status transaksi
- **Kalender Transaksi**: Event calendar untuk tracking transaksi harian
- **5 Transaksi Terakhir**: Monitoring transaksi terbaru yang masuk
- **Filter Periode**: Harian, mingguan, bulanan, dan tahunan

### 💼 Manajemen Master Data
![Master Data](https://img.shields.io/badge/Module-Master_Data-blue?style=flat-square)

- **Pelanggan**: CRUD pelanggan, loyalty points, member card, referral tracking
- **Kurir**: CRUD kurir, data kendaraan, bank account, emergency contact
- **Staf**: CRUD staf/kasir, jam kerja, gaji, data bank untuk penggajian
- **Jenis Pakaian**: CRUD jenis pakaian dengan icon picker
- **Layanan**: CRUD layanan, pricing (per kg/satuan), durasi, min/max order
- **Promo**: CRUD promo dengan multiple tipe diskon, targeting, dan auto-apply

### 🧾 Transaksi & Kasir
![Transaksi](https://img.shields.io/badge/Module-Transaksi-green?style=flat-square)

- **Kasir POS**: Point of Sale untuk transaksi cepat dengan kalkulator
- **Multi-Layanan**: Satu transaksi bisa memiliki banyak layanan
- **Multi-Promo**: Apply multiple promo dengan urutan prioritas
- **Payment Tracking**: Metode pembayaran, tipe bayar, status verifikasi
- **Bukti Digital**: Upload foto timbangan dan bukti pembayaran
- **Receipt/Struk**: Generate receipt untuk customer
- **WhatsApp Integration**: Kirim notifikasi via WhatsApp

### 🎁 Referral & Loyalty
![Referral](https://img.shields.io/badge/Module-Referral-orange?style=flat-square)

- **Kode Referral**: Generate kode referral unik per pelanggan
- **Dual Promo**: Promo berbeda untuk referee dan referrer
- **Campaign Tracking**: Track performa per campaign source
- **Statistics**: Total referral dan conversion rate

### 📱 Messaging (Fonnte Integration)
![WhatsApp](https://img.shields.io/badge/Integration-WhatsApp-25D366?style=flat-square&logo=whatsapp&logoColor=white)

- **Send Messages**: Kirim pesan WhatsApp ke pelanggan/kurir
- **Media Support**: Kirim file, gambar, dan lokasi
- **Scheduling**: Jadwalkan pengiriman pesan
- **Typing Simulation**: Simulasi typing untuk natural conversation

### ⚙️ Pengaturan Sistem
![Settings](https://img.shields.io/badge/Module-Settings-gray?style=flat-square)

- **Konfigurasi Global**: Key-value pairs dengan grouping
- **Type Support**: String, number, boolean, dan JSON
- **Referral Settings**: Pengaturan khusus untuk sistem referral

### 👤 Profile & Authentication
![Auth](https://img.shields.io/badge/Module-Auth-red?style=flat-square)

- **Login/Logout**: Autentikasi admin/kasir/staf
- **Forgot Password**: Reset password via email
- **Email Verification**: Verifikasi email pengguna
- **Profile Management**: Update profil, avatar, dan password

---

## 🗂️ Halaman & Modul

### Authentication
```
📁 /management/auth/
  ├── 🔐 login                    # Login page
  ├── 🔑 forgot-password          # Forgot password page
  ├── 🔄 reset-password           # Reset password page
  └── ✉️ verify-email             # Email verification page
```

### Main Application
```
📁 /management/
  ├── 🏠 dashboard                # Dashboard dengan analytics & charts
  ├── 🧮 kasir                    # Point of Sale / Kasir
  ├── 👤 profile                  # User profile management
  └── ⚙️ pengaturan               # Global settings
```

### Master Data Modules
```
📁 /management/pelanggan/
  ├── 📋 index                    # List pelanggan dengan search & filter
  ├── ➕ create                   # Form tambah pelanggan baru
  └── ✏️ edit/{id}                # Form edit pelanggan

📁 /management/kurir/
  ├── 📋 index                    # List kurir dengan status tracking
  ├── ➕ create                   # Form tambah kurir baru
  └── ✏️ edit/{id}                # Form edit kurir

📁 /management/staf/
  ├── 📋 index                    # List staf/kasir
  ├── ➕ create                   # Form tambah staf baru
  └── ✏️ edit/{id}                # Form edit staf

📁 /management/jenis-pakaian/
  ├── 📋 index                    # List jenis pakaian
  ├── ➕ create                   # Form tambah jenis pakaian
  └── ✏️ edit/{id}                # Form edit jenis pakaian

📁 /management/layanan/
  ├── 📋 index                    # List layanan dengan pricing
  ├── ➕ create                   # Form tambah layanan baru
  └── ✏️ edit/{id}                # Form edit layanan

📁 /management/promo/
  ├── 📋 index                    # List promo dengan status & kuota
  ├── ➕ create                   # Form tambah promo baru
  └── ✏️ edit/{id}                # Form edit promo
```

### Transaction Module
```
📁 /management/transaksi/
  ├── 📋 index                    # List transaksi dengan filter
  ├── ➕ create                   # Form transaksi baru (multi-layanan)
  └── ✏️ edit/{id}                # Form edit transaksi
```

### Referral Module
```
📁 /management/referral/
  ├── 📋 index                    # List referral codes dengan statistics
  ├── ✏️ edit/{id}                # Edit referral settings
  └── ⚙️ pengaturan               # Pengaturan sistem referral
```

### Messaging Module (Fonnte)
```
📁 /management/fonnte/
  ├── 📋 index                    # List message history
  ├── ➕ create                   # Send new message
  └── ✏️ edit/{id}                # Edit scheduled message
```

---

## 🧩 Komponen

Aplikasi Management dilengkapi dengan komponen-komponen custom yang dapat digunakan kembali:

### UI Components
| Komponen | Deskripsi | Lokasi |
|----------|-----------|--------|
| **Icon Picker** | Pilih icon dari iconpark untuk jenis pakaian/layanan | `management/component/icon-picker` |
| **String List Input** | Input array of strings (include/exclude layanan) | `management/component/string-list-input` |
| **Key Value Jenis Pakaian** | Input jenis pakaian dengan jumlah dalam transaksi | `management/component/key-value-jenis-pakaian` |
| **Multi Layanan Form** | Form untuk multiple layanan dalam satu transaksi | `management/component/multi-layanan-form` |
| **Receipt** | Generate receipt/struk transaksi untuk print | `management/component/receipt` |
| **WhatsApp Button** | Quick action button untuk kirim WhatsApp | `management/component/whats-app-button` |

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

### Integration
![WhatsApp](https://img.shields.io/badge/WhatsApp-Fonnte_API-25D366?style=for-the-badge&logo=whatsapp&logoColor=white)

### Tools
![Laravel Herd](https://img.shields.io/badge/Laravel_Herd-Development-FF2D20?style=for-the-badge)
![Bun](https://img.shields.io/badge/Bun-Asset_Build-000000?style=for-the-badge&logo=bun&logoColor=white)

</div>

---

## 📊 Fitur Data & Analytics

### Dashboard Metrics
- ✅ **Real-time Statistics**: Auto-refresh setiap periode tertentu
- 📈 **Multiple Chart Types**: Line, Area, Bar charts
- 🎨 **Donut Charts**: Untuk distribusi status transaksi
- 📅 **Event Calendar**: Highlight hari dengan transaksi
- 🏆 **Ranking System**: Top 5 layanan dengan badge

### Filter & Search
- 🔍 **Advanced Search**: Search by kode, nama, email, no HP
- 📆 **Date Range Filter**: Filter berdasarkan periode
- 🏷️ **Status Filter**: Filter by status (Aktif, Tidak Aktif, dll)
- 🗂️ **Sorting**: Multi-column sorting
- 📄 **Pagination**: Server-side pagination untuk performa optimal

### Export & Print
- 🖨️ **Print Receipt**: Print struk transaksi
- 📱 **Send via WhatsApp**: Kirim struk via WhatsApp
- 💾 **Data Backup**: Soft delete untuk semua master data

---

## 🎨 UI/UX Features

### Responsiveness
![Desktop](https://img.shields.io/badge/Desktop-✓-success?style=flat-square)
![Tablet](https://img.shields.io/badge/Tablet-✓-success?style=flat-square)
![Mobile](https://img.shields.io/badge/Mobile-✓-success?style=flat-square)

### Theme Support
![Light Mode](https://img.shields.io/badge/Light_Mode-✓-yellow?style=flat-square)
![Dark Mode](https://img.shields.io/badge/Dark_Mode-✓-black?style=flat-square)

### User Experience
- ⚡ **No Page Reload**: Full SPA experience dengan Livewire
- 🔄 **Loading States**: Wire:loading indicators
- ✨ **Smooth Transitions**: CSS transitions untuk smooth UX
- 🎯 **Inline Editing**: Edit data langsung di table
- 🚀 **Quick Actions**: Action buttons untuk aksi cepat
- 💬 **Toast Notifications**: Real-time feedback untuk user actions
- ⌨️ **Keyboard Shortcuts**: Navigation dengan keyboard

---

## 🔐 Security Features

- 🔒 **Authentication**: Laravel Sanctum untuk session management
- 🛡️ **Authorization**: Role-based access control (Super Admin, Kasir, Staf)
- ✅ **CSRF Protection**: Built-in Laravel CSRF protection
- 🔑 **Password Hashing**: Bcrypt password hashing
- 📧 **Email Verification**: Optional email verification
- 🚫 **Soft Deletes**: Data tidak benar-benar terhapus
- 📝 **Audit Trail**: Snapshot data untuk historical tracking

---

## 📞 Kontak & Support

**Developer**: Denis Djodian Ardika
**Position**: Tech Lead
**Company**: PT. Aktif Global Vision
**Repository**: [github.com/denis156/aktif-laundry](https://github.com/denis156/aktif-laundry)

---

<div align="center">

  **Aktif Laundry Management** © 2025 - PT. Aktif Global Vision

  [![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red?style=for-the-badge)](https://github.com/denis156/aktif-laundry)

</div>
