<?php

declare(strict_types=1);

namespace App\Livewire\Management\Components;

use App\Helper\Database\JenisPakaianHelper;
use App\Models\JenisPakaian;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class KeyValueJenisPakaian extends Component
{
    public array $items = [];

    public Collection $jenisPakaianOptions;

    public string $outputString = '';

    public function mount(string $value = ''): void
    {
        $this->loadJenisPakaianOptions();

        // ! OPTIONAL: Jenis pakaian tidak wajib diisi
        // Parse initial value jika ada (format: "Kemeja (3), Celana (2)")
        if (! empty($value)) {
            $this->parseInitialValue($value);
        } else {
            // Default: 1 baris kosong (user bisa hapus atau isi sesuai kebutuhan)
            $this->addRow();
        }
    }

    protected function loadJenisPakaianOptions(): void
    {
        try {
            $this->jenisPakaianOptions = JenisPakaian::where('status', 'Aktif')
                ->orderBy('nama_jenis')
                ->get(['id', 'kode_jenis', 'nama_jenis', 'icon'])
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'kode' => $item->kode_jenis,
                    'name' => $item->nama_jenis,
                    'icon' => JenisPakaianHelper::getIcon($item),
                ]);
        } catch (Exception $e) {
            Log::error('Failed to load jenis pakaian options', [
                'error' => $e->getMessage(),
            ]);
            $this->jenisPakaianOptions = collect([]);
        }
    }

    protected function parseInitialValue(string $value): void
    {
        $jsonData = json_decode($value, true);

        if (is_array($jsonData) && ! empty($jsonData)) {
            foreach ($jsonData as $item) {
                // Support format baru (nama_jenis) dan format lama (nama)
                $nama = $item['nama_jenis'] ?? $item['nama'] ?? null;
                $jenisId = $item['jenis_pakaian_id'] ?? $item['jenis_id'] ?? null;
                $jumlah = $item['jumlah'] ?? 1;

                if ($nama && $jumlah) {
                    // Cari jenis pakaian berdasarkan ID atau nama
                    if ($jenisId) {
                        $jenis = $this->jenisPakaianOptions->firstWhere('id', $jenisId);
                    } else {
                        $jenis = $this->jenisPakaianOptions->firstWhere('name', $nama);
                    }

                    $this->items[] = [
                        'jenis_id' => $jenis['id'] ?? '',
                        'nama' => $jenis['name'] ?? $nama,
                        'jumlah' => max(1, (int) $jumlah),
                    ];
                }
            }
        } else {
            $items = explode(',', $value);

            foreach ($items as $item) {
                $item = trim($item);

                if (preg_match('/^(.+?)\s*\((\d+)\)$/', $item, $matches)) {
                    $nama = trim($matches[1]);
                    $jumlah = (int) $matches[2];

                    $jenis = $this->jenisPakaianOptions->firstWhere('name', $nama);

                    $this->items[] = [
                        'jenis_id' => $jenis['id'] ?? '',
                        'nama' => $nama,
                        'jumlah' => max(1, $jumlah),
                    ];
                }
            }
        }

        if (empty($this->items)) {
            $this->addRow();
        }
    }

    public function addRow(): void
    {
        $this->items[] = [
            'jenis_id' => '',
            'nama' => '',
            'jumlah' => 1,
        ];
    }

    public function removeRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Re-index array
        $this->updateOutput();
    }

    public function updatedItems(mixed $value, string $key): void
    {
        if (str_contains($key, 'jenis_id')) {
            $index = (int) explode('.', $key)[0];
            $jenisId = $this->items[$index]['jenis_id'] ?? '';

            $jenis = $this->jenisPakaianOptions->firstWhere('id', $jenisId);
            if ($jenis) {
                $this->items[$index]['nama'] = $jenis['name'];
            }
        }

        $this->updateOutput();
    }

    protected function updateOutput(): void
    {
        // ! OPTIONAL: Jenis pakaian tidak wajib diisi - filter hanya yang terisi
        $validItems = array_filter(
            $this->items,
            fn (array $item) => ! empty($item['jenis_id']) && ! empty($item['nama']) && $item['jumlah'] > 0
        );

        $jsonData = array_map(
            fn (array $item) => [
                'jenis_pakaian_id' => (int) $item['jenis_id'],
                'nama_jenis' => $item['nama'],
                'jumlah' => (int) $item['jumlah'],
            ],
            $validItems
        );

        // Output akan kosong jika tidak ada jenis pakaian yang dipilih (dan itu OK - optional field)
        $this->outputString = json_encode(array_values($jsonData), JSON_THROW_ON_ERROR);
        $this->dispatch('jenisPakaianUpdated', $this->outputString);
    }

    public function getOutputString(): string
    {
        return $this->outputString;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.management.components.key-value-jenis-pakaian');
    }
}
