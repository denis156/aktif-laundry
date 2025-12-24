<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#cf4040">

    <title>Kebijakan Privasi - {{ config('app.name') }}</title>
    <meta name="description"
        content="Kebijakan Privasi {{ config('app.name') }} menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <link rel="canonical" href="{{ url('/privacy-policy') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-dvh antialiased bg-base-200">

    <!-- Navbar -->
    <nav class="navbar bg-base-200 w-full sticky top-0 z-10 shadow-lg border-b border-accent border-dashed">
        <div class="flex-1 px-2">
            <div class="flex items-center gap-4">
                <img src="{{ asset('icon.png') }}" alt="{{ config('app.name') }}" class="h-12 w-auto">
                <span class="font-bold text-lg text-primary hidden md:block">{{ config('app.name') }}</span>
            </div>
        </div>
        <div class="flex-none">
            <x-button label="Kembali" link="{{ url('/') }}" icon="iconpark.back-o" class="btn-ghost btn-primary" responsive />
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 max-w-4xl">

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body prose max-w-none">

                <h1 class="text-primary border-b-4 border-primary pb-2">Kebijakan Privasi</h1>
                <p class="text-base-content/60 italic">Terakhir diperbarui: {{ date('d F Y') }}</p>

                <p>Selamat datang di <strong>{{ config('app.name') }}</strong>. Kami sangat menghargai privasi Anda dan
                    berkomitmen untuk melindungi informasi pribadi yang Anda berikan kepada kami.</p>

                <p>Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi
                    informasi Anda saat menggunakan aplikasi mobile {{ config('app.name') }} ("Aplikasi").</p>

                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span><strong>Penting:</strong> Dengan menggunakan Aplikasi ini, Anda menyetujui pengumpulan dan
                        penggunaan informasi sesuai dengan Kebijakan Privasi ini.</span>
                </div>

                <h2 class="text-primary mt-8">Area Layanan</h2>
                <p>Saat ini layanan {{ config('app.name') }} tersedia di <strong>Kota Kendari, Sulawesi
                        Tenggara</strong>. Kami terus berkembang dan berencana untuk memperluas jangkauan layanan ke
                    kota-kota lainnya di seluruh Indonesia.</p>

                <h2 class="text-primary mt-8">1. Informasi yang Kami Kumpulkan</h2>

                <h3 class="text-base-content/80">1.1 Informasi yang Anda Berikan Secara Langsung</h3>
                <p>Kami mengumpulkan informasi yang Anda berikan saat mendaftar dan menggunakan layanan kami:</p>
                <ul>
                    <li><strong>Informasi Akun:</strong> Nama lengkap, alamat email, nomor telepon, password
                        (terenkripsi)</li>
                    <li><strong>Informasi Profil:</strong> Foto profil (opsional), kode pelanggan</li>
                    <li><strong>Informasi Alamat:</strong> Alamat lengkap (detail alamat, kelurahan, kecamatan,
                        kabupaten/kota, provinsi)</li>
                    <li><strong>Informasi Lokasi:</strong> Koordinat GPS (latitude dan longitude) untuk memudahkan
                        layanan antar-jemput</li>
                    <li><strong>Informasi Pesanan:</strong> Detail pesanan laundry, jenis layanan, catatan khusus,
                        metode pembayaran</li>
                    <li><strong>Komunikasi:</strong> Pesan chat dengan customer service, file atau gambar yang Anda
                        kirim melalui fitur chat</li>
                    <li><strong>Informasi Referral:</strong> Kode referral yang Anda gunakan saat mendaftar (jika ada)
                    </li>
                </ul>

                <h3 class="text-base-content/80">1.2 Informasi yang Dikumpulkan Secara Otomatis</h3>
                <ul>
                    <li><strong>Token Perangkat:</strong> Firebase Cloud Messaging (FCM) token untuk mengirimkan
                        notifikasi push</li>
                    <li><strong>Informasi Perangkat:</strong> Model perangkat, sistem operasi, versi aplikasi</li>
                    <li><strong>Data Penggunaan:</strong> Log aktivitas penggunaan aplikasi untuk meningkatkan layanan
                        kami</li>
                    <li><strong>Data Lokasi Real-time:</strong> Lokasi kurir saat proses pengantaran untuk fitur
                        tracking pesanan</li>
                </ul>

                <h2 class="text-primary mt-8">2. Bagaimana Kami Menggunakan Informasi Anda</h2>

                <p>Kami menggunakan informasi yang dikumpulkan untuk tujuan berikut:</p>
                <ul>
                    <li><strong>Menyediakan Layanan:</strong> Memproses pesanan laundry, mengatur penjemputan dan
                        pengantaran</li>
                    <li><strong>Komunikasi:</strong> Mengirimkan notifikasi tentang status pesanan, promosi, dan update
                        layanan</li>
                    <li><strong>Customer Service:</strong> Menanggapi pertanyaan dan memberikan dukungan pelanggan
                        melalui fitur chat</li>
                    <li><strong>Pembayaran:</strong> Memproses transaksi pembayaran Anda</li>
                    <li><strong>Personalisasi:</strong> Menyesuaikan pengalaman pengguna dan menampilkan promosi yang
                        relevan</li>
                    <li><strong>Keamanan:</strong> Melindungi akun Anda dan mencegah aktivitas penipuan</li>
                    <li><strong>Peningkatan Layanan:</strong> Menganalisis penggunaan aplikasi untuk meningkatkan fitur
                        dan layanan kami</li>
                    <li><strong>Program Referral:</strong> Mengelola program referral dan memberikan reward kepada
                        pelanggan</li>
                </ul>

                <h2 class="text-primary mt-8">3. Berbagi Informasi dengan Pihak Ketiga</h2>

                <p>Kami TIDAK menjual informasi pribadi Anda kepada pihak ketiga. Namun, kami dapat berbagi informasi
                    dengan:</p>

                <h3 class="text-base-content/80">3.1 Penyedia Layanan</h3>
                <ul>
                    <li><strong>Firebase (Google):</strong> Untuk layanan push notification, authentication, dan cloud
                        storage</li>
                    <li><strong>Mapbox:</strong> Untuk layanan peta dan tracking lokasi kurir</li>
                    <li><strong>Penyedia Hosting:</strong> Untuk menyimpan data aplikasi dengan aman</li>
                </ul>

                <h3 class="text-base-content/80">3.2 Kewajiban Hukum</h3>
                <p>Kami dapat mengungkapkan informasi Anda jika diwajibkan oleh hukum, proses hukum, atau permintaan
                    pemerintah yang sah.</p>

                <h2 class="text-primary mt-8">4. Keamanan Data</h2>

                <p>Kami menerapkan langkah-langkah keamanan teknis dan organisasi untuk melindungi informasi pribadi
                    Anda:</p>
                <ul>
                    <li>Password dienkripsi menggunakan algoritma hashing yang aman</li>
                    <li>Koneksi data menggunakan HTTPS (SSL/TLS)</li>
                    <li>Akses ke data dibatasi hanya untuk personel yang berwenang</li>
                    <li>Backup data secara berkala</li>
                    <li>Monitoring keamanan sistem secara rutin</li>
                </ul>

                <div class="alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        class="stroke-current shrink-0 w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><strong>Catatan:</strong> Meskipun kami berupaya maksimal melindungi data Anda, tidak ada
                        sistem keamanan yang 100% sempurna. Kami menyarankan Anda untuk menjaga kerahasiaan password
                        Anda.</span>
                </div>

                <h2 class="text-primary mt-8">5. Penyimpanan Data</h2>

                <p>Kami menyimpan informasi pribadi Anda selama:</p>
                <ul>
                    <li>Anda masih aktif menggunakan layanan kami</li>
                    <li>Diperlukan untuk tujuan bisnis yang sah (misalnya: riwayat transaksi, perpajakan)</li>
                    <li>Diwajibkan oleh peraturan perundang-undangan yang berlaku</li>
                </ul>

                <p>Setelah akun Anda dihapus, kami akan menghapus atau menganonim informasi pribadi Anda, kecuali jika
                    kami diwajibkan untuk menyimpannya berdasarkan hukum yang berlaku.</p>

                <h2 class="text-primary mt-8">6. Hak Anda</h2>

                <p>Sebagai pengguna, Anda memiliki hak untuk:</p>
                <ul>
                    <li><strong>Akses:</strong> Melihat informasi pribadi yang kami simpan tentang Anda</li>
                    <li><strong>Perbaikan:</strong> Memperbarui atau memperbaiki informasi pribadi Anda melalui halaman
                        profil</li>
                    <li><strong>Penghapusan:</strong> Meminta penghapusan akun dan data pribadi Anda</li>
                    <li><strong>Penarikan Persetujuan:</strong> Mencabut persetujuan untuk penggunaan data tertentu
                        (misalnya: notifikasi push)</li>
                    <li><strong>Portabilitas:</strong> Meminta salinan data pribadi Anda dalam format yang dapat dibaca
                    </li>
                </ul>

                <p>Untuk menggunakan hak-hak ini, silakan hubungi kami melalui informasi kontak di bawah.</p>

                <h2 class="text-primary mt-8">7. Push Notification</h2>

                <p>Aplikasi kami menggunakan Firebase Cloud Messaging untuk mengirimkan notifikasi push tentang:</p>
                <ul>
                    <li>Status pesanan (dijemput, diproses, selesai, diantar)</li>
                    <li>Promosi dan penawaran khusus</li>
                    <li>Pesan dari customer service</li>
                    <li>Update aplikasi</li>
                </ul>

                <p>Anda dapat menonaktifkan notifikasi push kapan saja melalui pengaturan perangkat Anda.</p>

                <h2 class="text-primary mt-8">8. Lokasi dan Tracking</h2>

                <p>Kami menggunakan data lokasi untuk:</p>
                <ul>
                    <li>Memudahkan input alamat pengiriman</li>
                    <li>Menampilkan lokasi kurir secara real-time saat proses pengantaran</li>
                    <li>Menghitung estimasi waktu tiba</li>
                </ul>

                <p>Anda dapat mengontrol izin lokasi melalui pengaturan perangkat Anda. Namun, beberapa fitur mungkin
                    tidak berfungsi optimal tanpa akses lokasi.</p>

                <h2 class="text-primary mt-8">9. Cookies dan Teknologi Serupa</h2>

                <p>Aplikasi kami menggunakan Service Worker dan local storage untuk:</p>
                <ul>
                    <li>Menyimpan preferensi pengguna</li>
                    <li>Meningkatkan performa aplikasi</li>
                    <li>Menyediakan fitur offline (Progressive Web App)</li>
                </ul>

                <h2 class="text-primary mt-8">10. Anak-anak</h2>

                <p>Layanan kami ditujukan untuk pengguna berusia 17 tahun ke atas. Kami tidak secara sengaja
                    mengumpulkan informasi pribadi dari anak-anak di bawah 17 tahun. Jika Anda adalah orang tua atau
                    wali dan mengetahui bahwa anak Anda memberikan informasi pribadi kepada kami, silakan hubungi kami
                    untuk menghapus informasi tersebut.</p>

                <h2 class="text-primary mt-8">11. Perubahan Kebijakan Privasi</h2>

                <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Kami akan memberitahu Anda tentang
                    perubahan signifikan melalui:</p>
                <ul>
                    <li>Notifikasi push</li>
                    <li>Email</li>
                    <li>Pemberitahuan di dalam aplikasi</li>
                </ul>

                <p>Perubahan akan berlaku segera setelah dipublikasikan. Kami menyarankan Anda untuk meninjau Kebijakan
                    Privasi ini secara berkala.</p>

                <h2 class="text-primary mt-8">12. Informasi Kontak</h2>

                <p>Jika Anda memiliki pertanyaan, kekhawatiran, atau permintaan terkait Kebijakan Privasi ini atau data
                    pribadi Anda, silakan hubungi kami:</p>

                <div class="bg-base-200 p-6 rounded-lg">
                    <p class="font-bold text-lg mb-2">{{ config('app.name') }}</p>
                    <p><strong>Email:</strong> support@aktiflaundry.com</p>
                    <p><strong>WhatsApp/Telepon:</strong> +6282156912202</p>
                    <p><strong>Alamat:</strong> 2G58+6X9, Jl. Lumba - Lumba, Lalolara, Kec. Kambu, Kota Kendari,
                        Sulawesi Tenggara 93561</p>
                    <p><strong>Jam Operasional:</strong> Buka 24 Jam</p>
                </div>

                <h2 class="text-primary mt-8">13. Yurisdiksi</h2>

                <p>Kebijakan Privasi ini diatur oleh dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap
                    perselisihan yang timbul terkait kebijakan ini akan diselesaikan melalui musyawarah atau melalui
                    pengadilan yang berwenang di Indonesia.</p>

                <div class="divider my-8"></div>

                <p class="text-center text-base-content/60 text-sm">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.<br>
                    Dengan menggunakan aplikasi ini, Anda menyetujui Kebijakan Privasi dan Syarat & Ketentuan kami.
                </p>
            </div>
        </div>

        <!-- Back to Home Button -->
        <div class="mt-8 text-center">
            <x-button label="Kembali" link="{{ url('/') }}" icon="iconpark.back-o" class="btn-primary btn-block" />
        </div>

    </main>

    <!-- Footer -->
    <footer class="footer footer-center bg-primary text-primary-content rounded-t-2xl p-4 mt-16">
        <aside>
            <p class="font-bold">{{ config('app.name') }}</p>
            <p>Laundry Praktis, Hidup Lebih Mudah</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
        </aside>
    </footer>

</body>

</html>
