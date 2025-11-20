<?php

namespace App\Exports;

use App\Models\Transaksi;
use App\Helper\Database\TransaksiLayananHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransaksiExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected $selectedIds;

    public function __construct($selectedIds = [])
    {
        $this->selectedIds = $selectedIds;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Transaksi::with(['kasir', 'pelanggan', 'transaksiLayanan.layanan']);

        if (!empty($this->selectedIds)) {
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
            'Kode Transaksi',
            'Tanggal Masuk',
            'Kasir',
            'Kode Pelanggan',
            'Nama Pelanggan',
            'No. HP Pelanggan',
            'Layanan',
            'Detail Layanan',
            'Total Berat (kg)',
            'Total Item (pcs)',
            'Subtotal',
            'Diskon',
            'Total',
            'Metode Pembayaran',
            'Tanggal Selesai',
            'Status',
            'Catatan',
        ];
    }

    /**
     * Map each row data
     */
    public function map($transaksi): array
    {
        // Prepare layanan info
        $layananNames = [];
        $layananDetails = [];

        foreach ($transaksi->transaksiLayanan as $tl) {
            $layananNames[] = $tl->nama_layanan;

            if (TransaksiLayananHelper::isPerKg($tl)) {
                $detail = $tl->nama_layanan . ': ' . $tl->berat_kg . ' kg x Rp ' . number_format($tl->harga_per_kg, 0, ',', '.');

                // Add jenis pakaian if exists
                if (!empty($tl->jenis_pakaian)) {
                    $jenisList = [];
                    foreach ($tl->jenis_pakaian as $jp) {
                        $jenisList[] = $jp['nama'] . ' (' . $jp['jumlah'] . ')';
                    }
                    $detail .= ' [' . implode(', ', $jenisList) . ']';
                }
            } else {
                $detail = $tl->nama_layanan . ': ' . $tl->jumlah_satuan . ' pcs x Rp ' . number_format($tl->harga_per_satuan, 0, ',', '.');
            }

            $layananDetails[] = $detail;
        }

        return [
            $transaksi->kode_transaksi,
            $transaksi->tanggal_masuk->format('d/m/Y H:i'),
            $transaksi->kasir->name ?? '-',
            $transaksi->pelanggan->kode_pelanggan ?? '-',
            $transaksi->nama_pelanggan,
            $transaksi->pelanggan->no_hp ?? '-',
            implode(', ', $layananNames),
            implode(' | ', $layananDetails),
            $transaksi->total_berat,
            $transaksi->total_item,
            $transaksi->subtotal,
            $transaksi->diskon,
            $transaksi->total,
            $transaksi->metode_pembayaran,
            $transaksi->tanggal_selesai ? $transaksi->tanggal_selesai->format('d/m/Y H:i') : '-',
            $transaksi->status,
            $transaksi->catatan ?? '-',
        ];
    }

    /**
     * Column formatting
     */
    public function columnFormats(): array
    {
        return [
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Subtotal
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Diskon
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total
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
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert 1 row at the top for app name and date
                $sheet->insertNewRowBefore(1, 1);

                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(50); // Row untuk app name dan tanggal (lebih besar dari header)
                $sheet->getRowDimension(2)->setRowHeight(25); // Row untuk header tabel

                // Get column count for merging
                $lastColumn = $sheet->getHighestColumn();

                // Row 1: App Name + Tanggal Export (centered, merged)
                $appName = config('app.name', 'Aplikasi Laundry');
                $tanggalExport = now()->format('d-m-Y H:i:s');
                $headerText = 'Data Transaksi ' . $appName . "\n" . 'Tanggal Export: ' . $tanggalExport;

                $sheet->setCellValue('A1', $headerText);
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'name' => 'Arial',
                        'color' => [
                            'argb' => 'FFFD191A' // Red #FD191A
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // Get last row for data range
                $lastRow = $sheet->getHighestRow();

                // Style header row (row 2)
                $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 11,
                        'color' => [
                            'argb' => 'FFFFFFFF' // White text
                        ]
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFD191A'], // Red #FD191A
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // Black border
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // Style data rows (row 3 onwards)
                if ($lastRow > 2) {
                    $sheet->getStyle('A3:' . $lastColumn . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'], // Black border
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                }
            },
        ];
    }

}
