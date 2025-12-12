<?php

namespace App\Exports;

use App\Exports\Sheets\PiutangSheet;
use App\Exports\Sheets\TransaksiSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransaksiExport implements WithMultipleSheets
{
    protected $selectedIds;

    public function __construct($selectedIds = [])
    {
        $this->selectedIds = $selectedIds;
    }

    public function sheets(): array
    {
        return [
            new TransaksiSheet($this->selectedIds),
            new PiutangSheet(),
        ];
    }
}
