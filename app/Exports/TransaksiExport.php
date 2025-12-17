<?php

namespace App\Exports;

use App\Exports\Tables\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;

class TransaksiExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected $table;

    public function __construct($selectedIds = [])
    {
        $this->table = new Transaksi($selectedIds);
    }

    public function collection()
    {
        return $this->table->collection();
    }

    public function headings(): array
    {
        return $this->table->headings();
    }

    public function map($row): array
    {
        return $this->table->map($row);
    }

    public function columnFormats(): array
    {
        return $this->table->columnFormats();
    }

    public function styles($sheet)
    {
        return $this->table->styles($sheet);
    }

    public function registerEvents(): array
    {
        return $this->table->registerEvents();
    }
}
