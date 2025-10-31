# Aktif Laundry - Sistem Kasir

![Google Apps Script](https://img.shields.io/badge/Google%20Apps%20Script-4285F4?style=for-the-badge&logo=google&logoColor=white)
![Google Sheets](https://img.shields.io/badge/Google%20Sheets-34A853?style=for-the-badge&logo=google-sheets&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![DaisyUI](https://img.shields.io/badge/DaisyUI-5A0EF8?style=for-the-badge&logo=daisyui&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

![Status](https://img.shields.io/badge/Status-Active-success?style=flat-square)
![License](https://img.shields.io/badge/License-Private-red?style=flat-square)
![Version](https://img.shields.io/badge/Version-1.0.0-blue?style=flat-square)

Sistem manajemen laundry berbasis web yang dibangun menggunakan Google Apps Script dengan Google Sheets sebagai database.

## Fitur Utama

### 1. **Kasir**
- Buat transaksi laundry baru untuk pelanggan
- Tambah pelanggan baru langsung dari form kasir
- Pilih pelanggan existing dari database
- Pilih layanan laundry
- Hitung otomatis subtotal, diskon, dan total
- Konfirmasi transaksi sebelum menyimpan

### 2. **Transaksi**
- Kelola semua transaksi laundry pelanggan
- Filter berdasarkan status (Menunggu, Proses, Selesai, Diambil, Batal)
- Pencarian transaksi berdasarkan pelanggan atau layanan
- Detail, edit, dan hapus transaksi
- Pagination untuk data yang banyak

### 3. **Pelanggan**
- Kelola data pelanggan dan informasi kontak
- Status pelanggan (Aktif/Tidak Aktif)
- Tracking total transaksi per pelanggan
- Filter dan pencarian pelanggan
- CRUD operations (Create, Read, Update, Delete)

### 4. **Layanan**
- Kelola jenis layanan laundry dan harga
- Tentukan harga per kilogram
- Tentukan durasi pengerjaan (dalam jam)
- Status layanan (Aktif/Tidak Aktif)
- Deskripsi layanan

## Tech Stack

- **Frontend**:
  - HTML5
  - [DaisyUI](https://daisyui.com/) - Component library
  - [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework

- **Backend**:
  - [Google Apps Script](https://developers.google.com/apps-script) - Server-side JavaScript
  - [Google Sheets](https://www.google.com/sheets/about/) - Database

- **Deployment**:
  - [Clasp](https://github.com/google/clasp) - Command line tool for Apps Script

## Struktur Database (Google Sheets)

### Sheet: Pelanggan
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| ID | String | ID unik pelanggan (PLG001, PLG002, ...) |
| Nama | String | Nama pelanggan |
| No HP | String | Nomor HP pelanggan |
| Alamat | String | Alamat pelanggan |
| Email | String | Email pelanggan |
| Tanggal Daftar | Date | Tanggal pendaftaran |
| Total Transaksi | Number | Jumlah transaksi yang pernah dilakukan |
| Status | String | Aktif / Tidak Aktif |

### Sheet: Layanan
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| ID | String | ID unik layanan (LAY001, LAY002, ...) |
| Nama | String | Nama layanan |
| Harga | Number | Harga per kilogram |
| Durasi | Number | Durasi pengerjaan (dalam jam) |
| Deskripsi | String | Deskripsi layanan |
| Status | String | Aktif / Tidak Aktif |

### Sheet: Transaksi
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| ID | String | ID unik transaksi (TRX001, TRX002, ...) |
| Tanggal Masuk | Date | Tanggal transaksi dibuat |
| ID Pelanggan | String | Foreign key ke sheet Pelanggan |
| Nama Pelanggan | String | Nama pelanggan (denormalized) |
| ID Layanan | String | Foreign key ke sheet Layanan |
| Nama Layanan | String | Nama layanan (denormalized) |
| Berat | Number | Berat cucian (kg) |
| Harga per Kg | Number | Harga layanan per kg |
| Subtotal | Number | Berat × Harga per Kg |
| Diskon | Number | Nominal diskon |
| Total | Number | Subtotal - Diskon |
| Metode Pembayaran | String | Tunai / Transfer / QRIS |
| Tanggal Selesai | Date | Estimasi selesai (otomatis dihitung) |
| Status | String | Menunggu / Proses / Selesai / Diambil / Batal |
| Catatan | String | Catatan tambahan |

## Instalasi & Setup

### Prerequisites
- Node.js dan npm terinstall
- Akun Google
- Google Sheets sudah dibuat dengan struktur sheet di atas

### 1. Clone Repository
```bash
git clone <repository-url>
cd "aktif laundry"
```

### 2. Install Clasp
```bash
npm install -g @google/clasp
```

### 3. Login ke Google Account
```bash
clasp login
```

### 4. Create Apps Script Project
```bash
clasp create --type webapp --title "Aktif Laundry"
```

Atau jika sudah punya project:
```bash
clasp clone <scriptId>
```

### 5. Setup Google Sheets
1. Buat Google Sheets baru
2. Buat 3 sheet: `Pelanggan`, `Layanan`, `Transaksi`
3. Tambahkan header sesuai struktur database di atas
4. Copy Spreadsheet ID dari URL
5. Bind Apps Script ke Spreadsheet:
   - Buka Google Sheets
   - Extensions > Apps Script
   - Copy Script ID
   - Update `.clasp.json`

### 6. Push Code ke Apps Script
```bash
clasp push
```

### 7. Deploy Web App
```bash
clasp deploy
```

Atau deploy via Apps Script Editor:
1. Buka Apps Script Editor
2. Klik "Deploy" > "New deployment"
3. Pilih type: "Web app"
4. Execute as: "Me"
5. Who has access: "Anyone" atau sesuai kebutuhan
6. Klik "Deploy"
7. Copy Web App URL

## Development

### File Structure
```
aktif-laundry/
├── Index.html              # Main layout & navigation
├── Code.js                 # Main backend functions
├── appsscript.json        # Apps Script manifest
├── .gitignore             # Git ignore rules
├── .clasp.json            # Clasp configuration (ignored)
├── .claspignore           # Files to ignore when pushing
│
├── Kasir/
│   ├── Kasir.html         # Kasir form page
│   ├── KasirModals.html   # Modal components
│   └── KasirScripts.html  # JavaScript logic
│
├── Pelanggan/
│   ├── Pelanggan.html          # Pelanggan list page
│   ├── PelangganScripts.html   # JavaScript logic
│   ├── ListPelanggan.js        # Read operations
│   ├── CreatePelanggan.js      # Create operation
│   ├── UpdatePelanggan.js      # Update operation
│   └── DeletePelanggan.js      # Delete operation
│
├── Layanan/
│   ├── Layanan.html           # Layanan list page
│   ├── LayananScripts.html    # JavaScript logic
│   ├── ListLayanan.js         # Read operations
│   ├── CreateLayanan.js       # Create operation
│   ├── UpdateLayanan.js       # Update operation
│   └── DeleteLayanan.js       # Delete operation
│
└── Transaksi/
    ├── Transaksi.html         # Transaksi list page
    ├── TransaksiScripts.html  # JavaScript logic
    ├── ListTransaksi.js       # Read operations
    ├── CreateTransaksi.js     # Create operation
    ├── UpdateTransaksi.js     # Update operation
    └── DeleteTransaksi.js     # Delete operation
```

### Watch Mode (Development)
```bash
clasp push --watch
```

### View Logs
```bash
clasp logs
```

### Open Apps Script Editor
```bash
clasp open
```

## Validation Rules

### Pelanggan
- Nama: Required
- No HP: Required, harus diawali dengan "8" (format Indonesia tanpa +62 atau 0)
- Alamat: Optional
- Email: Optional

### Layanan
- Nama: Required
- Harga: Required, harus lebih dari 0
- Durasi: Required, harus lebih dari 0

### Transaksi
- Pelanggan: Required
- Layanan: Required
- Berat: Required, harus lebih dari 0
- Diskon: Optional, default 0
- Metode Pembayaran: Default "Tunai"
- Status: Default "Menunggu"

## UI Features

- **Responsive Design**: Mobile-first design yang bekerja di semua device
- **Dark Mode**: Toggle antara light theme (corporate) dan dark theme (business)
- **Real-time Calculation**: Subtotal dan total dihitung otomatis
- **Confirmation Modal**: Konfirmasi sebelum submit atau delete
- **Search & Filter**: Pencarian dan filter di semua halaman list
- **Pagination**: Support pagination untuk data banyak

## Browser Support

- Chrome (Recommended)
- Firefox
- Safari
- Edge

## License

Private project - All rights reserved

## Author

Denis Djadian Ardika

## Contact

Untuk pertanyaan atau support, silakan hubungi developer.

---

**Copyright © 2025 - Aktif Laundry. All rights reserved.**
