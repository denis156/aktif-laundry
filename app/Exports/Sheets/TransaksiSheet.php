<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

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

class TransaksiSheet implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $selectedIds;

    protected $layananKodes = [];

    protected $statusBayarRows = [];

    public function __construct($selectedIds = [])
    {
        $this->selectedIds = $selectedIds;
    }

    public function title(): string
    {
        return 'Transaksi';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Transaksi::with(['kasir', 'pelanggan', 'transaksiLayanan.layanan']);

        if (! empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->orderBy('kode_transaksi', 'desc')->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Layanan',
            '',
            'Detail Layanan',
            '',
            '',
            'Tipe Bayar',
            'Status Bayar',
        ];
    }

    /**
     * Map each row data
     */
    public function map($transaksi): array
    {
        static $rowNumber = 1;
        $currentNumber = $rowNumber;
        $rowNumber++;

        // Pisahkan layanan per kg dan per satuan menggunakan kode layanan
        $layananPerKg = [];
        $layananPerSatuan = [];
        $beratList = [];
        $volumeList = [];
        $totalBerat = 0;
        $totalVolume = 0;

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

        // Tentukan status bayar berdasarkan status_bayar atau status transaksi - ubah ke singkatan
        $statusBayarFull = $transaksi->status_bayar ?? ($transaksi->status === 'Selesai' ? 'Sudah Bayar' : 'Belum Bayar');
        $statusBayar = match ($statusBayarFull) {
            'Sudah Bayar' => 'SB',
            'Belum Bayar' => 'BB',
            'Menunggu Verifikasi' => 'MV',
            'Ditolak' => 'DT',
            default => $statusBayarFull,
        };

        // Format tipe bayar - ubah menjadi singkatan
        $tipeBayar = '-';
        if (! empty($transaksi->tipe_bayar)) {
            $tipeBayar = match ($transaksi->tipe_bayar) {
                'Tunai' => 'TN',
                'Non-Tunai' => 'NT',
                default => $transaksi->tipe_bayar,
            };
        }

        // Simpan row number untuk status bayar (untuk pewarnaan nanti)
        // Row 5 adalah data pertama (setelah header di row 3-4 dan title di row 1-2)
        $this->statusBayarRows[$currentNumber + 4] = $statusBayar;

        return [
            $currentNumber,
            $transaksi->nama_pelanggan,
            $perKgText,
            $perSatuanText,
            $detailBerat,
            $detailVolume,
            'Rp. '.number_format($transaksi->total, 0, ',', '.'),
            $tipeBayar,
            $statusBayar,
        ];
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

                // Insert rows at the top for report header (2 rows for title + kasir, 1 row for double header)
                $sheet->insertNewRowBefore(1, 3);

                // Get last row for data range
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                // Get date range for report
                $transaksi = $this->collection();
                $firstDate = $transaksi->min('tanggal_masuk');
                $lastDate = $transaksi->max('tanggal_masuk');

                $dateRange = '';
                if ($firstDate && $lastDate) {
                    $dateRange = $firstDate->format('d/m/Y').' - '.$lastDate->format('d/m/Y');
                }

                // Row 1: Title - Laporan Tanggal
                $sheet->setCellValue('A1', 'Laporan Tanggal '.$dateRange.' Transaksi Aktif Laundry');
                $sheet->mergeCells('A1:'.$lastColumn.'1');
                $sheet->getStyle('A1')->applyFromArray([
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

                // Row 2: Kasir Name
                $kasirName = auth('web')->user()->name ?? 'Semua Kasir';
                $sheet->setCellValue('A2', 'Kasir '.$kasirName);
                $sheet->mergeCells('A2:'.$lastColumn.'2');
                $sheet->getStyle('A2')->applyFromArray([
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

                // Row 3: Empty row (will be used for merged headers)

                // Merge cells for row 3-4 headers
                // No
                $sheet->setCellValue('A3', 'No');
                $sheet->mergeCells('A3:A4');

                // Nama
                $sheet->setCellValue('B3', 'Nama');
                $sheet->mergeCells('B3:B4');

                // Layanan (merged across C-D)
                $sheet->setCellValue('C3', 'Layanan');
                $sheet->mergeCells('C3:D3');

                // Detail Layanan (merged across E-F)
                $sheet->setCellValue('E3', 'Detail Layanan');
                $sheet->mergeCells('E3:F3');

                // Total
                $sheet->setCellValue('G3', 'Total');
                $sheet->mergeCells('G3:G4');

                // Tipe Bayar
                $sheet->setCellValue('H3', 'Tipe Bayar');
                $sheet->mergeCells('H3:H4');

                // Status Bayar
                $sheet->setCellValue('I3', 'Status Bayar');
                $sheet->mergeCells('I3:I4');

                // Row 4: Sub headers under Layanan and Detail Layanan
                $sheet->setCellValue('C4', 'Per_Kg');
                $sheet->setCellValue('D4', 'Per_satuan');
                $sheet->setCellValue('E4', 'Berat (Kg)');
                $sheet->setCellValue('F4', 'Volume');

                // Style for all header rows (3 and 4)
                $sheet->getStyle('A3:'.$lastColumn.'4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFB4C7E7'], // Light blue
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

                // Data rows styling
                if ($lastRow > 4) {
                    $sheet->getStyle('A5:'.$lastColumn.$lastRow)->applyFromArray([
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
                    $sheet->getStyle('A5:A'.$lastRow)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Tambahkan warna di kolom I (Status Bayar) sesuai status
                    foreach ($this->statusBayarRows as $rowNum => $status) {
                        $colorConfig = match ($status) {
                            'SB' => 'FFC6EFCE', // Hijau untuk Sudah Bayar
                            'MV' => 'FFFFFFCC', // Kuning untuk Menunggu Verifikasi
                            'BB', 'DT' => 'FFFFC7CE', // Merah untuk Belum Bayar & Ditolak
                            default => null,
                        };

                        if ($colorConfig) {
                            $sheet->getStyle('I'.$rowNum)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => $colorConfig],
                                ],
                            ]);
                        }
                    }
                }

                // Calculate totals
                $totalBerat = 0;
                $totalVolume = 0;
                $totalRupiah = 0;

                foreach ($this->collection() as $t) {
                    $totalBerat += $t->total_berat;
                    $totalVolume += $t->total_item;
                    $totalRupiah += $t->total;
                }

                // Add Total Berat row
                $totalBeratRow = $lastRow + 1;
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

                // Add Total Satuan row
                $totalSatuanRow = $lastRow + 2;
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

                // Add Total Pembayaran row
                $totalPembayaranRow = $lastRow + 3;
                $sheet->setCellValue('E'.$totalPembayaranRow, 'Total Pembayaran');
                $sheet->mergeCells('E'.$totalPembayaranRow.':G'.$totalPembayaranRow);
                $sheet->setCellValue('H'.$totalPembayaranRow, 'Rp. '.number_format($totalRupiah, 0, ',', '.'));
                $sheet->mergeCells('H'.$totalPembayaranRow.':I'.$totalPembayaranRow);

                // Style untuk label (E-G): align left
                $sheet->getStyle('E'.$totalPembayaranRow.':G'.$totalPembayaranRow)->applyFromArray([
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
                $sheet->getStyle('H'.$totalPembayaranRow.':I'.$totalPembayaranRow)->applyFromArray([
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

                // Tambahkan keterangan kode layanan di bawah tabel
                if (! empty($this->layananKodes)) {
                    $keteranganStartRow = $totalPembayaranRow + 2;

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
                    $sheet->setCellValue('A'.$currentRow, '- TN = Tunai, NT = Non-Tunai');
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

                // Set manual width untuk setiap kolom
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(25);  // Nama
                $sheet->getColumnDimension('C')->setWidth(25);  // Layanan Per_Kg
                $sheet->getColumnDimension('D')->setWidth(25);  // Layanan Per_satuan
                $sheet->getColumnDimension('E')->setWidth(15);  // Detail Berat
                $sheet->getColumnDimension('F')->setWidth(15);  // Detail Volume
                $sheet->getColumnDimension('G')->setWidth(20);  // Total
                $sheet->getColumnDimension('H')->setWidth(15);  // Tipe Bayar
                $sheet->getColumnDimension('I')->setWidth(15);  // Status Bayar
            },
        ];
    }
}
