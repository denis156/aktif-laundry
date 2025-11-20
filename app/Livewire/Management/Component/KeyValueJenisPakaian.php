<?php

declare(strict_types=1);

namespace App\Livewire\Management\Component;

use Exception;
use Livewire\Component;
use App\Models\JenisPakaian;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class KeyValueJenisPakaian extends Component
{
    public array $items = [];
    public Collection $jenisPakaianOptions;
    public string $outputString = '';

    public function mount(string $value = ''): void
    {
        $this->loadJenisPakaianOptions();

        // Parse initial value jika ada (format: "Kemeja (3), Celana (2)")
        if (!empty($value)) {
            $this->parseInitialValue($value);
        } else {
            // Default: 1 baris kosong
            $this->addRow();
        }
    }

    protected function loadJenisPakaianOptions(): void
    {
        try {
            // Load dari database dengan query builder approach yang lebih efisien
            $this->jenisPakaianOptions = JenisPakaian::where('status', 'Aktif')
                ->orderBy('nama_jenis')
                ->get(['id', 'kode_jenis', 'nama_jenis'])
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'kode' => $item->kode_jenis,
                    'name' => $item->nama_jenis,
                ]);
        } catch (Exception $e) {
            Log::error('Failed to load jenis pakaian options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->jenisPakaianOptions = collect([]);
        }
    }

    protected function parseInitialValue(string $value): void
    {
        // Try to decode JSON first
        $jsonData = json_decode($value, true);

        if (is_array($jsonData) && !empty($jsonData)) {
            // Parse dari JSON format: [{"nama": "Kemeja", "jumlah": 3}]
            foreach ($jsonData as $item) {
                if (isset($item['nama'], $item['jumlah'])) {
                    $nama = $item['nama'];
                    $jumlah = (int) $item['jumlah'];

                    // Cari ID jenis dari nama menggunakan Collection method
                    $jenis = $this->jenisPakaianOptions->firstWhere('name', $nama);

                    $this->items[] = [
                        'jenis_id' => $jenis['id'] ?? '',
                        'nama' => $nama,
                        'jumlah' => max(1, $jumlah), // Ensure minimum 1
                    ];
                }
            }
        } else {
            // Fallback: Parse dari string format "Kemeja (3), Celana (2)"
            $items = explode(',', $value);

            foreach ($items as $item) {
                $item = trim($item);

                // Extract nama dan jumlah dengan regex
                if (preg_match('/^(.+?)\s*\((\d+)\)$/', $item, $matches)) {
                    $nama = trim($matches[1]);
                    $jumlah = (int) $matches[2];

                    // Cari ID jenis dari nama
                    $jenis = $this->jenisPakaianOptions->firstWhere('name', $nama);

                    $this->items[] = [
                        'jenis_id' => $jenis['id'] ?? '',
                        'nama' => $nama,
                        'jumlah' => max(1, $jumlah),
                    ];
                }
            }
        }

        // Jika parsing gagal atau kosong, tambah 1 baris
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
        // When jenis_id changes, update nama
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
        // Filter items yang sudah terisi menggunakan arrow function
        $validItems = array_filter(
            $this->items,
            fn (array $item) => !empty($item['jenis_id']) && !empty($item['nama']) && $item['jumlah'] > 0
        );

        // Format JSON: [{"nama": "Kemeja", "jumlah": 3}, {"nama": "Celana", "jumlah": 2}]
        $jsonData = array_map(
            fn (array $item) => [
                'nama' => $item['nama'],
                'jumlah' => (int) $item['jumlah'],
            ],
            $validItems
        );

        // Convert ke JSON string untuk disimpan ke database
        $this->outputString = json_encode(array_values($jsonData), JSON_THROW_ON_ERROR);

        // Emit event ke parent component
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
        return view('livewire.management.component.key-value-jenis-pakaian');
    }
}
