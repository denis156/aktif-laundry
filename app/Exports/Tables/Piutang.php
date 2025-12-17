<?php

declare(strict_types=1);

namespace App\Exports\Tables;

use App\Helper\Database\TransaksiLayananHelper;
use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Piutang implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Piutang';
    }

    protected $layananKodes = [];

    protected $statusBayarRows = [];

    protected $transaksiByDate = [];

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil semua transaksi yang belum dibayar (status_bayar = 'Belum Bayar')
        $allTransaksi = Transaksi::with(['kasir', 'pelanggan', 'transaksiLayanan.layanan'])
            ->where('status_bayar', 'Belum Bayar')
            ->orderBy('tanggal_masuk', 'asc')
            ->get();

        // Kelompokkan transaksi berdasarkan tanggal
        $this->transaksiByDate = $allTransaksi->groupBy(function ($transaksi) {
            return $transaksi->tanggal_masuk->format('Y-m-d');
        });

        return $allTransaksi;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        // Tidak digunakan karena kita membuat header sendiri di registerEvents
        return [];
    }

    /**
     * Map each row data
     */
    public function map($transaksi): array
    {
        // Tidak digunakan karena kita membuat data sendiri di registerEvents
        return [];
    }

    /**
     * Column formatting
     */
    public function columnFormats(): array
    {
        return [
            // No formatting needed for these columns
        ];
    }

    /**
     * Styles
     */
    public function styles(Worksheet $sheet)
    {
        // Styles akan diterapkan di registerEvents setelah insert rows
    }

    /**
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'I';

                // Hapus semua data yang ada (karena kita akan membuat struktur baru)
                $sheet->removeRow(1, $sheet->getHighestRow());

                // Get date range for report
                $transaksi = $this->collection();
                $firstDate = $transaksi->min('tanggal_masuk');
                $lastDate = $transaksi->max('tanggal_masuk');

                $dateRange = '';
                if ($firstDate && $lastDate) {
                    $dateRange = $firstDate->format('d/m/Y').' - '.$lastDate->format('d/m/Y');
                }

                $currentRow = 1;

                // Row 1: Title - Laporan Piutang
                $sheet->setCellValue('A'.$currentRow, 'Laporan Piutang '.$dateRange.' Aktif Laundry');
                $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
                $sheet->getStyle('A'.$currentRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'name' => 'Arial',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $currentRow++;

                // Row 2: Kasir Name
                $kasirName = auth('web')->user()->name ?? 'Semua Kasir';
                $sheet->setCellValue('A'.$currentRow, 'Kasir '.$kasirName);
                $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
                $sheet->getStyle('A'.$currentRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Arial',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $currentRow++;

                // Kosongkan 1 row
                $currentRow++;

                $totalBeratGlobal = 0;
                $totalVolumeGlobal = 0;
                $totalPiutangGlobal = 0;

                // Loop untuk setiap tanggal
                foreach ($this->transaksiByDate as $date => $transaksiList) {
                    // Title untuk tabel tanggal ini
                    $dateFormatted = \Carbon\Carbon::parse($date)->format('d/m/Y');
                    $sheet->setCellValue('A'.$currentRow, 'Laporan Piutang '.$dateFormatted.' Aktif Laundry');
                    $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
                    $sheet->getStyle('A'.$currentRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 14,
                            'name' => 'Arial',
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $currentRow++;

                    // Header tabel untuk tanggal ini
                    $headerRow = $currentRow;

                    // Merge cells for row header (2 rows)
                    // No
                    $sheet->setCellValue('A'.$currentRow, 'No');
                    $sheet->mergeCells('A'.$currentRow.':A'.($currentRow + 1));

                    // Nama
                    $sheet->setCellValue('B'.$currentRow, 'Nama');
                    $sheet->mergeCells('B'.$currentRow.':B'.($currentRow + 1));

                    // Layanan (merged across C-D)
                    $sheet->setCellValue('C'.$currentRow, 'Layanan');
                    $sheet->mergeCells('C'.$currentRow.':D'.$currentRow);

                    // Detail Layanan (merged across E-F)
                    $sheet->setCellValue('E'.$currentRow, 'Detail Layanan');
                    $sheet->mergeCells('E'.$currentRow.':F'.$currentRow);

                    // Total
                    $sheet->setCellValue('G'.$currentRow, 'Total');
                    $sheet->mergeCells('G'.$currentRow.':G'.($currentRow + 1));

                    // Tipe Bayar
                    $sheet->setCellValue('H'.$currentRow, 'Tipe Bayar');
                    $sheet->mergeCells('H'.$currentRow.':H'.($currentRow + 1));

                    // Status Bayar
                    $sheet->setCellValue('I'.$currentRow, 'Status Bayar');
                    $sheet->mergeCells('I'.$currentRow.':I'.($currentRow + 1));

                    $currentRow++;

                    // Sub headers under Layanan and Detail Layanan
                    $sheet->setCellValue('C'.$currentRow, 'Per_Kg');
                    $sheet->setCellValue('D'.$currentRow, 'Per_satuan');
                    $sheet->setCellValue('E'.$currentRow, 'Berat (Kg)');
                    $sheet->setCellValue('F'.$currentRow, 'Volume');

                    // Style for header rows
                    $sheet->getStyle('A'.$headerRow.':'.$lastColumn.$currentRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'name' => 'Arial',
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFC7CE'], // Light red/pink untuk piutang
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $currentRow++;

                    // Data rows untuk tanggal ini
                    $no = 1;
                    $totalBerat = 0;
                    $totalVolume = 0;
                    $totalRupiah = 0;

                    foreach ($transaksiList as $transaksi) {
                        // Pisahkan layanan per kg dan per satuan menggunakan kode layanan
                        $layananPerKg = [];
                        $layananPerSatuan = [];
                        $beratList = [];
                        $volumeList = [];

                        foreach ($transaksi->transaksiLayanan as $tl) {
                            $kodeLayanan = $tl->layanan->kode_layanan ?? 'N/A';

                            // Simpan kode dan nama layanan untuk keterangan
                            if (! isset($this->layananKodes[$kodeLayanan])) {
                                $this->layananKodes[$kodeLayanan] = $tl->layanan->nama_layanan ?? $tl->nama_layanan;
                            }

                            if (TransaksiLayananHelper::isPerKg($tl)) {
                                $layananPerKg[] = $kodeLayanan;
                                $beratList[] = $tl->berat_kg;
                                $totalBerat += $tl->berat_kg;
                            } else {
                                $layananPerSatuan[] = $kodeLayanan;
                                $volumeList[] = $tl->jumlah_satuan;
                                $totalVolume += $tl->jumlah_satuan;
                            }
                        }

                        // Format layanan dengan kode - pakai kurung per item (item1)(item2)
                        $perKgText = ! empty($layananPerKg) ? implode('', array_map(fn ($item) => '('.$item.')', $layananPerKg)) : '';
                        $perSatuanText = ! empty($layananPerSatuan) ? implode('', array_map(fn ($item) => '('.$item.')', $layananPerSatuan)) : '';

                        // Format detail layanan (berat dan volume) - pakai kurung per item (item1)(item2)
                        $detailBerat = ! empty($beratList) ? implode('', array_map(fn ($item) => '('.$item.')', $beratList)) : '';
                        $detailVolume = ! empty($volumeList) ? implode('', array_map(fn ($item) => '('.$item.')', $volumeList)) : '';

                        $statusBayar = 'BB'; // BB = Belum Bayar

                        // Format tipe bayar - ubah menjadi singkatan
                        $tipeBayar = '-';
                        if (! empty($transaksi->tipe_bayar)) {
                            $tipeBayar = match ($transaksi->tipe_bayar) {
                                'Tunai' => 'TN',
                                'Non-Tunai' => 'NT',
                                default => $transaksi->tipe_bayar,
                            };
                        }

                        $totalRupiah += $transaksi->total;

                        // Isi data
                        $sheet->setCellValue('A'.$currentRow, $no);
                        $sheet->setCellValue('B'.$currentRow, $transaksi->nama_pelanggan);
                        $sheet->setCellValue('C'.$currentRow, $perKgText);
                        $sheet->setCellValue('D'.$currentRow, $perSatuanText);
                        $sheet->setCellValue('E'.$currentRow, $detailBerat);
                        $sheet->setCellValue('F'.$currentRow, $detailVolume);
                        $sheet->setCellValue('G'.$currentRow, 'Rp. '.number_format($transaksi->total, 0, ',', '.'));
                        $sheet->setCellValue('H'.$currentRow, $tipeBayar);
                        $sheet->setCellValue('I'.$currentRow, $statusBayar);

                        // Style untuk data row
                        $sheet->getStyle('A'.$currentRow.':'.$lastColumn.$currentRow)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                ],
                            ],
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);

                        // Align center untuk kolom No
                        $sheet->getStyle('A'.$currentRow)->applyFromArray([
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);

                        // Warna untuk status bayar
                        $sheet->getStyle('I'.$currentRow)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFFFC7CE'], // Merah untuk Belum Bayar
                            ],
                        ]);

                        $currentRow++;
                        $no++;
                    }

                    // Update total global
                    $totalBeratGlobal += $totalBerat;
                    $totalVolumeGlobal += $totalVolume;
                    $totalPiutangGlobal += $totalRupiah;

                    // Total untuk tanggal ini
                    // Add Total Berat row
                    $totalBeratRow = $currentRow;
                    $sheet->setCellValue('E'.$totalBeratRow, 'Total Berat (Kg)');
                    $sheet->mergeCells('E'.$totalBeratRow.':G'.$totalBeratRow);
                    $sheet->setCellValue('H'.$totalBeratRow, $totalBerat);
                    $sheet->mergeCells('H'.$totalBeratRow.':I'.$totalBeratRow);

                    // Style untuk label (E-G): align left
                    $sheet->getStyle('E'.$totalBeratRow.':G'.$totalBeratRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'name' => 'Arial',
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Style untuk nilai (H-I): align right
                    $sheet->getStyle('H'.$totalBeratRow.':I'.$totalBeratRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'name' => 'Arial',
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $currentRow++;

                    // Add Total Satuan row
                    $totalSatuanRow = $currentRow;
                    $sheet->setCellValue('E'.$totalSatuanRow, 'Total Satuan');
                    $sheet->mergeCells('E'.$totalSatuanRow.':G'.$totalSatuanRow);
                    $sheet->setCellValue('H'.$totalSatuanRow, $totalVolume);
                    $sheet->mergeCells('H'.$totalSatuanRow.':I'.$totalSatuanRow);

                    // Style untuk label (E-G): align left
                    $sheet->getStyle('E'.$totalSatuanRow.':G'.$totalSatuanRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'name' => 'Arial',
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Style untuk nilai (H-I): align right
                    $sheet->getStyle('H'.$totalSatuanRow.':I'.$totalSatuanRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'name' => 'Arial',
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $currentRow++;

                    // Add Total Piutang row
                    $totalPiutangRow = $currentRow;
                    $sheet->setCellValue('E'.$totalPiutangRow, 'Total Piutang');
                    $sheet->mergeCells('E'.$totalPiutangRow.':G'.$totalPiutangRow);
                    $sheet->setCellValue('H'.$totalPiutangRow, 'Rp. '.number_format($totalRupiah, 0, ',', '.'));
                    $sheet->mergeCells('H'.$totalPiutangRow.':I'.$totalPiutangRow);

                    // Style untuk label (E-G): align left
                    $sheet->getStyle('E'.$totalPiutangRow.':G'.$totalPiutangRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'name' => 'Arial',
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Style untuk nilai (H-I): align right
                    $sheet->getStyle('H'.$totalPiutangRow.':I'.$totalPiutangRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'name' => 'Arial',
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $currentRow++;

                    // Kosongkan 2 row sebelum tabel berikutnya
                    $currentRow += 2;
                }

                // Total keseluruhan
                $currentRow++;
                $sheet->setCellValue('F'.$currentRow, 'TOTAL KESELURUHAN');
                $sheet->mergeCells('F'.$currentRow.':I'.$currentRow);
                $sheet->getStyle('F'.$currentRow.':I'.$currentRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Arial',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFFC7CE'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                $currentRow++;

                // Add Total Berat Global
                $totalBeratRow = $currentRow;
                $sheet->setCellValue('F'.$totalBeratRow, 'Total Berat (Kg)');
                $sheet->mergeCells('F'.$totalBeratRow.':G'.$totalBeratRow);
                $sheet->setCellValue('H'.$totalBeratRow, $totalBeratGlobal);
                $sheet->mergeCells('H'.$totalBeratRow.':I'.$totalBeratRow);

                $sheet->getStyle('F'.$totalBeratRow.':G'.$totalBeratRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('H'.$totalBeratRow.':I'.$totalBeratRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $currentRow++;

                // Add Total Satuan Global
                $totalSatuanRow = $currentRow;
                $sheet->setCellValue('F'.$totalSatuanRow, 'Total Satuan');
                $sheet->mergeCells('F'.$totalSatuanRow.':G'.$totalSatuanRow);
                $sheet->setCellValue('H'.$totalSatuanRow, $totalVolumeGlobal);
                $sheet->mergeCells('H'.$totalSatuanRow.':I'.$totalSatuanRow);

                $sheet->getStyle('F'.$totalSatuanRow.':G'.$totalSatuanRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('H'.$totalSatuanRow.':I'.$totalSatuanRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $currentRow++;

                // Add Total Piutang Global
                $totalPiutangRow = $currentRow;
                $sheet->setCellValue('F'.$totalPiutangRow, 'Total Piutang');
                $sheet->mergeCells('F'.$totalPiutangRow.':G'.$totalPiutangRow);
                $sheet->setCellValue('H'.$totalPiutangRow, 'Rp. '.number_format($totalPiutangGlobal, 0, ',', '.'));
                $sheet->mergeCells('H'.$totalPiutangRow.':I'.$totalPiutangRow);

                $sheet->getStyle('F'.$totalPiutangRow.':G'.$totalPiutangRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('H'.$totalPiutangRow.':I'.$totalPiutangRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $currentRow++;

                // Set manual width untuk setiap kolom (karena kita buat data manual)
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(25);  // Nama
                $sheet->getColumnDimension('C')->setWidth(20);  // Layanan Per_Kg
                $sheet->getColumnDimension('D')->setWidth(20);  // Layanan Per_satuan
                $sheet->getColumnDimension('E')->setWidth(15);  // Detail Berat
                $sheet->getColumnDimension('F')->setWidth(15);  // Detail Volume
                $sheet->getColumnDimension('G')->setWidth(15);  // Total
                $sheet->getColumnDimension('H')->setWidth(15);  // Tipe Bayar
                $sheet->getColumnDimension('I')->setWidth(15);  // Status Bayar

                // Tambahkan keterangan kode layanan di bawah tabel
                if (! empty($this->layananKodes)) {
                    $keteranganStartRow = $currentRow + 2;

                    // Header keterangan
                    $sheet->setCellValue('A'.$keteranganStartRow, 'Keterangan:');
                    $sheet->mergeCells('A'.$keteranganStartRow.':'.$lastColumn.$keteranganStartRow);
                    $sheet->getStyle('A'.$keteranganStartRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                            'name' => 'Arial',
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Penjelasan format pemisah
                    $currentRow = $keteranganStartRow + 1;
                    $sheet->setCellValue('A'.$currentRow, '- Setiap item ditampilkan dalam kurung tersendiri, contoh: (item1)(item2)(item3)');
                    $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
                    $sheet->getStyle('A'.$currentRow)->applyFromArray([
                        'font' => [
                            'size' => 10,
                            'name' => 'Arial',
                            'italic' => true,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $currentRow++;

                    // Penjelasan singkatan Tipe Bayar
                    $sheet->setCellValue('A'.$currentRow, '- TN = Tunai, NT = Non-Tunai/QRIS');
                    $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
                    $sheet->getStyle('A'.$currentRow)->applyFromArray([
                        'font' => [
                            'size' => 10,
                            'name' => 'Arial',
                            'italic' => true,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $currentRow++;

                    // Penjelasan singkatan Status Bayar
                    $sheet->setCellValue('A'.$currentRow, '- BB = Belum Bayar, MV = Menunggu Verifikasi, SB = Sudah Bayar, DT = Ditolak');
                    $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
                    $sheet->getStyle('A'.$currentRow)->applyFromArray([
                        'font' => [
                            'size' => 10,
                            'name' => 'Arial',
                            'italic' => true,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $currentRow++;

                    // List kode dan nama layanan
                    foreach ($this->layananKodes as $kode => $nama) {
                        $sheet->setCellValue('A'.$currentRow, '- '.$kode.' = '.$nama);
                        $sheet->mergeCells('A'.$currentRow.':'.$lastColumn.$currentRow);
                        $sheet->getStyle('A'.$currentRow)->applyFromArray([
                            'font' => [
                                'size' => 10,
                                'name' => 'Arial',
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                        $currentRow++;
                    }
                }
            },
        ];
    }
}
