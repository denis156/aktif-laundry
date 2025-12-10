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

class PiutangSheet implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Piutang';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil semua transaksi yang belum dibayar (tanggal_bayar = null)
        return Transaksi::with(['kasir', 'pelanggan', 'transaksiLayanan.layanan'])
            ->whereNull('tanggal_bayar')
            ->orderBy('tanggal_masuk', 'asc')
            ->get();
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

        // Pisahkan layanan per kg dan per satuan
        $layananPerKg = [];
        $layananPerSatuan = [];
        $totalBerat = 0;
        $totalVolume = 0;

        foreach ($transaksi->transaksiLayanan as $tl) {
            if (TransaksiLayananHelper::isPerKg($tl)) {
                $layananPerKg[] = '('.$tl->nama_layanan.')';
                $totalBerat += $tl->berat_kg;
            } else {
                $layananPerSatuan[] = '('.$tl->nama_layanan.')';
                $totalVolume += $tl->jumlah_satuan;
            }
        }

        // Format layanan
        $perKgText = ! empty($layananPerKg) ? implode(', ', $layananPerKg) : '';
        $perSatuanText = ! empty($layananPerSatuan) ? implode(', ', $layananPerSatuan) : '';

        // Format detail layanan (berat dan volume)
        $detailBerat = $totalBerat > 0 ? '('.$totalBerat.'Kg)' : '';
        $detailVolume = $totalVolume > 0 ? '('.$totalVolume.')' : '';

        // Tentukan status bayar berdasarkan status_bayar atau status transaksi
        $statusBayar = $transaksi->status_bayar ?? ($transaksi->status === 'Selesai' ? 'Sudah Bayar' : 'Belum Bayar');

        // Format tipe bayar - cek semua kemungkinan
        $tipeBayar = '-';
        if (! empty($transaksi->tipe_bayar)) {
            $tipeBayar = $transaksi->tipe_bayar;
        }

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

                // Row 1: Title - Laporan Piutang
                $sheet->setCellValue('A1', 'Laporan Piutang '.$dateRange.' Aktif Laundry');
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

                // Row 2: Subtitle
                $sheet->setCellValue('A2', 'Transaksi Yang Belum Dibayar');
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
                $sheet->setCellValue('E4', 'Berat');
                $sheet->setCellValue('F4', 'Volume');

                // Style for all header rows (3 and 4)
                $sheet->getStyle('A3:'.$lastColumn.'4')->applyFromArray([
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
                $sheet->setCellValue('E'.$totalBeratRow, 'Total Berat');
                $sheet->mergeCells('E'.$totalBeratRow.':G'.$totalBeratRow);
                $sheet->setCellValue('H'.$totalBeratRow, $totalBerat.'kg');
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

                // Add Total Piutang row
                $totalPiutangRow = $lastRow + 3;
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
            },
        ];
    }
}
