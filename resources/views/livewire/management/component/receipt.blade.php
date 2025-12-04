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
            border-top: 1px dashed #000;
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
            border-bottom: 1px dashed #000;
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
            border-top: 1px dashed #000;
        }

        .footer {
            text-align: center;
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px dashed #000;
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
    <!-- NO TRANSAKSI -->
    <div class="header">
        <h1 style="font-size: 14px; margin-bottom: 4px;">{{ $transaksiData['kode_transaksi'] }}</h1>
    </div>

    <!-- INFO PELANGGAN -->
    <div class="section" style="text-align: center;">
        <div class="bold" style="font-size: 11px; margin-bottom: 2px;">{{ $transaksiData['nama_pelanggan'] }}</div>
        @if(!empty($pelangganAlamat))
        <div style="margin-bottom: 2px; font-size: 9px;">{{ $pelangganAlamat }}</div>
        @endif
        <div class="bold" style="font-size: 11px;">{{ $pelangganNoHp ?: '-' }}</div>
    </div>

    <div class="divider"></div>

    <!-- DETAIL LAYANAN -->
    <div class="section">
        @php
        // Load transaksi layanan data
        $transaksiLayananItems = [];
        try {
        $transaksiLayananItems = \DB::table('transaksi_layanan')
        ->where('transaksi_id', $transaksiData['id'] ?? 0)
        ->get()
        ->toArray();
        } catch(\Exception $e) {
        // Fallback untuk data lama (single layanan)
        $transaksiLayananItems = [[
        'nama_layanan' => $transaksiData['nama_layanan'] ?? '-',
        'berat_kg' => $transaksiData['berat_kg'] ?? 0,
        'jumlah_satuan' => null,
        'harga_per_kg' => $transaksiData['harga_per_kg'] ?? 0,
        'harga_per_satuan' => null,
        'jenis_pakaian' => $transaksiData['jenis_pakaian'] ?? null,
        'subtotal' => $transaksiData['subtotal'] ?? 0
        ]];
        }
        @endphp

        @foreach($transaksiLayananItems as $index => $item)
        @php
        // Convert stdClass to array if needed
        if (is_object($item)) {
        $item = (array) $item;
        }

        // Get satuan from layanan
        $satuan = 'pcs'; // default
        if (!empty($item['layanan_id'])) {
        $layanan = \App\Models\Layanan::find($item['layanan_id']);
        if ($layanan && !empty($layanan->satuan)) {
        $satuan = ucfirst($layanan->satuan);
        }
        }
        @endphp

        @if($index > 0)
        <div class="divider-solid" style="margin: 3px 0;"></div>
        @endif

        <!-- Nama Layanan & Harga per unit -->
        <div class="row" style="margin-bottom: 2px;">
            <span class="label bold">{{ $item['nama_layanan'] }}</span>
            @if (!empty($item['berat_kg']))
            <span class="value">Rp {{ number_format((int)$item['harga_per_kg'], 0, ',', '.') }}</span>
            @elseif (!empty($item['jumlah_satuan']))
            <span class="value">Rp {{ number_format((int)$item['harga_per_satuan'], 0, ',', '.') }}</span>
            @endif
        </div>

        <!-- Jenis Pakaian detail untuk layanan per kg -->
        @if(!empty($item['jenis_pakaian']) && !empty($item['berat_kg']))
        @php
        $jenisPakaianItems = [];
        if (is_array($item['jenis_pakaian'])) {
        $jenisPakaianItems = $item['jenis_pakaian'];
        } elseif (is_string($item['jenis_pakaian'])) {
        $decoded = json_decode($item['jenis_pakaian'], true);
        $jenisPakaianItems = $decoded ?: [];
        }
        @endphp
        @foreach($jenisPakaianItems as $jp)
        @php
        // Convert stdClass to array if needed
        if (is_object($jp)) {
        $jp = (array) $jp;
        }
        @endphp
        <div class="row" style="margin-bottom: 1px;">
            <span class="label" style="font-size: 9px;">{{ $jp['nama'] ?? '-' }}</span>
            <span class="value" style="font-size: 9px;">{{ $jp['jumlah'] ?? '-' }}</span>
        </div>
        @endforeach
        @endif

        <!-- Qty/Berat -->
        <div class="row" style="margin-bottom: 2px;">
            @if (!empty($item['berat_kg']))
            <span class="label">Berat</span>
            <span class="value">{{ number_format((float)$item['berat_kg'], 1, '.', '') }} Kg</span>
            @elseif (!empty($item['jumlah_satuan']))
            <span class="label">{{ $item['jumlah_satuan'] }} {{ $satuan }}</span>
            <span class="value bold">Rp {{ number_format((int)$item['subtotal'], 0, ',', '.') }}</span>
            @endif
        </div>
        @endforeach
    </div>

    <div class="divider-solid"></div>

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
    </div>

    <div class="divider"></div>

    <!-- RINGKASAN PEMBAYARAN -->
    <div class="section">
        <div class="row">
            <span class="label">Subtotal:</span>
            <span class="value">Rp {{ number_format((int)$transaksiData['subtotal'], 0, ',', '.') }}</span>
        </div>

        @if(!empty($transaksiData['promo']))
            @foreach($transaksiData['promo'] as $promo)
            <div class="row">
                <span class="label">{{ $promo['nama_promo'] }}:</span>
                <span class="value">- Rp {{ number_format((int)$promo['diskon'], 0, ',', '.') }}</span>
            </div>
            @endforeach
        @endif

        <div class="row total">
            <span class="label">TOTAL:</span>
            <span class="value">Rp {{ number_format((int)$transaksiData['total'], 0, ',', '.') }}</span>
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
    <div style="margin-top: 8px; text-align: center; width: 100%; padding: 0 4px;">
        @if(!empty($qrCodeSvg))
        <!-- Dynamic QR Code dengan nominal transaksi -->
        <div
            style="width: 140px; height: 140px; margin: 0 auto; padding: 4px; display: flex; align-items: center; justify-content: center;">
            {!! $qrCodeSvg !!}
        </div>
        @else
        <!-- Fallback ke QR static jika generate gagal -->
        <div
            style="width: 108px; height: 108px; margin: 0 auto; padding: 4px; display: flex; align-items: center; justify-content: center;">
            <img src="{{ asset('images/Qris.png') }}" alt="QRIS"
                style="max-width: 108px; max-height: 108px; filter: grayscale(100%) contrast(3) brightness(0.3);">
        </div>
        @endif

        <!-- Text di bawah QRIS -->
        <div style="margin-top: 8px; text-align: center; width: 100%; padding: 0 4px;">
            <p class="bold" style="font-size: 9px; margin-bottom: 2px;">SCAN UNTUK PEMBAYARAN</p>
            <p style="font-size: 8px; font-weight: 600; margin: 1px 0;">QRIS - Rp {{
                number_format((int)$transaksiData['total'], 0, ',', '.') }}</p>
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
