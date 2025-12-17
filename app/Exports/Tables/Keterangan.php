<?php

declare(strict_types=1);

namespace App\Exports\Tables;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Keterangan
{
    protected $layananKodes = [];

    public function __construct(array $layananKodes = [])
    {
        $this->layananKodes = $layananKodes;
    }

    public function render(Worksheet $sheet, int $startRow): int
    {
        if (empty($this->layananKodes)) {
            return $startRow;
        }

        $currentRow = $startRow + 2;
        $headerRow = $currentRow;

        // HEADER KODE LAYANAN (A-D) - Blue
        $sheet->setCellValue('A'.$currentRow, 'KODE LAYANAN');
        $sheet->mergeCells('A'.$currentRow.':D'.$currentRow);
        $this->applyHeaderStyle($sheet, 'A'.$currentRow.':D'.$currentRow, 'FF4472C4');

        // HEADER KETERANGAN (E-I) - Green
        $sheet->setCellValue('E'.$currentRow, 'KETERANGAN');
        $sheet->mergeCells('E'.$currentRow.':I'.$currentRow);
        $this->applyHeaderStyle($sheet, 'E'.$currentRow.':I'.$currentRow, 'FF70AD47');

        $currentRow++;
        $dataStartRow = $currentRow;

        // Data Kode Layanan (Kiri) dan Keterangan (Kanan)
        $keteranganData = [
            ['Format Data', 'Setiap item ditampilkan dalam kurung tersendiri, contoh: (item1)(item2)(item3)'],
            ['Tipe Bayar', 'TN = Tunai, NT = Non-Tunai/QRIS'],
            ['Status Bayar', 'BB = Belum Bayar, MV = Menunggu Verifikasi, SB = Sudah Bayar, DT = Ditolak'],
        ];

        $layananArray = [];
        foreach ($this->layananKodes as $kode => $nama) {
            $layananArray[] = [$kode, $nama];
        }

        $maxRows = max(count($layananArray), count($keteranganData));

        for ($i = 0; $i < $maxRows; $i++) {
            // Render Kode Layanan (A-D)
            if (isset($layananArray[$i])) {
                $sheet->setCellValue('A'.$currentRow, $layananArray[$i][0]);
                $sheet->setCellValue('B'.$currentRow, $layananArray[$i][1]);
                $sheet->mergeCells('B'.$currentRow.':D'.$currentRow);

                // Style untuk kode layanan
                $sheet->getStyle('A'.$currentRow.':D'.$currentRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A'.$currentRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }

            // Render Keterangan (E-I)
            if (isset($keteranganData[$i])) {
                $sheet->setCellValue('E'.$currentRow, $keteranganData[$i][0]);
                $sheet->setCellValue('F'.$currentRow, $keteranganData[$i][1]);
                $sheet->mergeCells('F'.$currentRow.':I'.$currentRow);

                // Style untuk keterangan
                $sheet->getStyle('E'.$currentRow.':I'.$currentRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('E'.$currentRow)->applyFromArray([
                    'font' => ['bold' => true],
                ]);
            }

            $currentRow++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);  // Kode
        $sheet->getColumnDimension('B')->setWidth(35);  // Nama Layanan
        $sheet->getColumnDimension('E')->setWidth(15);  // Label Keterangan
        $sheet->getColumnDimension('F')->setWidth(50);  // Isi Keterangan

        return $currentRow;
    }

    protected function applyHeaderStyle(Worksheet $sheet, string $range, string $color): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'name' => 'Arial',
                'color' => ['argb' => 'FFFFFFFF'], // White text
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $color],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
}
