<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Struk {{ $transaksiData['kode_transaksi'] }} - {{ $setting['nama_toko'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9px;
            line-height: 1.4;
            padding: 1.5mm;
            background: white;
            font-weight: 600;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: 900;
        }

        .header {
            text-align: center;
            margin-bottom: 2px;
            padding-bottom: 2px;
            border-bottom: 1px dashed #000;
        }

        .header h1 {
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 1px;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 9px;
            margin: 0.5px 0;
            font-weight: 600;
        }

        .section {
            margin: 2px 0;
            padding: 1px 0;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 2px 0;
        }

        .divider-solid {
            border-top: 1px solid #000;
            margin: 2px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }

        .label {
            flex: 1;
            font-weight: 900;
        }

        .value {
            flex: 1;
            text-align: right;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1px 0;
        }

        table th {
            text-align: left;
            font-size: 9px;
            padding: 1px 0;
            border-bottom: 1px solid #000;
            font-weight: 900;
        }

        table td {
            padding: 1px 0;
            font-size: 9px;
            font-weight: 600;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total {
            font-size: 10px;
            font-weight: 900;
            margin-top: 2px;
            padding-top: 2px;
            border-top: 1px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px solid #000;
            font-size: 9px;
            font-weight: 600;
        }

        .small {
            font-size: 8px;
            font-weight: 600;
        }

        @media print {
            body {
                margin: 0;
                padding: 2mm;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER TOKO -->
    <div class="header">
        <img src="{{ asset('images/Logo.png') }}" alt="Logo" style="max-width: 120px; max-height: 120px; margin: 0 auto 3px; filter: grayscale(100%) contrast(3) brightness(0.3);">
        @if(!empty($setting['whatsapp']))
        <p>WA: {{ $setting['whatsapp'] }}</p>
        @endif
        @if(!empty($setting['email']))
        <p>{{ $setting['email'] }}</p>
        @endif
        <p class="small">Buka: {{ $setting['jam_buka'] }} - Tutup: {{ $setting['jam_tutup'] }}</p>
    </div>

    <!-- INFO TRANSAKSI -->
    <div class="section">
        <div class="row">
            <span class="label">No:</span>
            <span class="value bold">{{ $transaksiData['kode_transaksi'] }}</span>
        </div>
        <div class="row">
            <span class="label">Tgl Masuk:</span>
            <span class="value">{{ \Carbon\Carbon::parse($transaksiData['tanggal_masuk'])->format('d/m/Y') }}</span>
        </div>
        <div class="row">
            <span class="label">Jam Masuk:</span>
            <span class="value">{{ \Carbon\Carbon::parse($transaksiData['tanggal_masuk'])->format('H:i') }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- INFO PELANGGAN -->
    <div class="section">
        <div class="row">
            <span class="label bold">Pelanggan:</span>
            <span class="value">{{ $transaksiData['nama_pelanggan'] }}</span>
        </div>
        <div class="row">
            <span class="label">No. Telp:</span>
            <span class="value">{{ $pelangganNoHp ?: '-' }}</span>
        </div>
        @if(!empty($pelangganAlamat))
        <div style="margin-top: 3px;">
            <div class="bold" style="margin-bottom: 1px;">Alamat:</div>
            <div>{{ $pelangganAlamat }}</div>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- DETAIL LAYANAN -->
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th class="text-center">Berat</th>
                    <th class="text-right">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $transaksiData['nama_layanan'] }}</td>
                    <td class="text-center">{{ number_format((float)$transaksiData['berat_kg'], 1, '.', '') }} Kg</td>
                    <td class="text-right">Rp {{ number_format((int)$transaksiData['harga_per_kg'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- JENIS PAKAIAN -->
    @if(!empty($transaksiData['jenis_pakaian']))
    <div class="section">
        @php
            // Parse JSON array: [{"nama":"Sprei","jumlah":3},{"nama":"Celana Pendek","jumlah":2}]
            $jenisPakaianItems = [];

            // Cek apakah sudah dalam bentuk array (dari database sudah di-decode)
            if (is_array($transaksiData['jenis_pakaian'])) {
                $jenisPakaianItems = $transaksiData['jenis_pakaian'];
            }
            // Jika masih string JSON, decode dulu
            elseif (is_string($transaksiData['jenis_pakaian'])) {
                $decoded = json_decode($transaksiData['jenis_pakaian'], true);
                $jenisPakaianItems = $decoded ?: [];
            }
        @endphp
        @foreach($jenisPakaianItems as $item)
            <div class="row">
                <span class="label">{{ $item['nama'] ?? '-' }}:</span>
                <span class="value">{{ $item['jumlah'] ?? '-' }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <div class="divider-solid"></div>

    <!-- RINGKASAN PEMBAYARAN -->
    <div class="section">
        <div class="row">
            <span class="label">Subtotal:</span>
            <span class="value">Rp {{ number_format((int)$transaksiData['subtotal'], 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span class="label">Diskon:</span>
            <span class="value">Rp {{ number_format((int)$transaksiData['diskon'], 0, ',', '.') }}</span>
        </div>
        <div class="row total">
            <span class="label">TOTAL:</span>
            <span class="value">Rp {{ number_format((int)$transaksiData['total'], 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- METODE PEMBAYARAN & STATUS -->
    <div class="section">
        <div class="row">
            <span class="label">Pembayaran:</span>
            <span class="value">{{ $transaksiData['metode_pembayaran'] }}</span>
        </div>
        <div class="row">
            <span class="label">Status:</span>
            <span class="value bold">{{ $transaksiData['status'] }}</span>
        </div>
        <div class="row">
            <span class="label">Tgl Selesai:</span>
            <span class="value">{{ !empty($transaksiData['tanggal_selesai']) ? \Carbon\Carbon::parse($transaksiData['tanggal_selesai'])->format('d/m/Y') : '-' }}</span>
        </div>
        <div class="row">
            <span class="label">Jam Selesai:</span>
            <span class="value">{{ !empty($transaksiData['tanggal_selesai']) ? \Carbon\Carbon::parse($transaksiData['tanggal_selesai'])->format('H:i') : '-' }}</span>
        </div>
    </div>

    <!-- CATATAN -->
    @if(!empty($transaksiData['catatan']))
    <div class="section">
        <p class="small"><strong>Catatan:</strong> {{ $transaksiData['catatan'] }}</p>
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p class="bold">{{ strtoupper($setting['nama_toko']) }}</p>
        <p style="font-style: italic; margin: 2px 0;">Tetap Aktif, Tetap Bersih</p>
    </div>

    <!-- Qris -->
    <div class="center" style="margin-top: 28px;">
        <img src="{{ asset('images/Qris.png') }}"
             alt="QRIS"
             style="max-width: 108px; max-height: 108px; margin: 0 auto; display: block; filter: grayscale(100%) contrast(3) brightness(0.3);">

        <!-- Text di bawah QRIS -->
        <div style="margin-top: 8px; text-align: center;">
            <p class="bold" style="font-size: 9px; margin-bottom: 2px;">SCAN UNTUK PEMBAYARAN</p>
            <p style="font-size: 8px; font-weight: 600; margin: 1px 0;">QRIS</p>
            <p class="small" style="margin-top: 2px;">Terima kasih atas kepercayaan Anda</p>
        </div>
    </div>

    <script>
        // Auto print saat halaman dimuat
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
