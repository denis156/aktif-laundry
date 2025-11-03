<?php

namespace App\Livewire\Component;

use App\Models\JenisPakaian;
use Illuminate\Support\Collection;
use Livewire\Component;

class KeyValueJenisPakaian extends Component
{
    public array $items = [];
    public Collection $jenisPakaianOptions;
    public string $outputString = '';

    public function mount($value = '')
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

    protected function loadJenisPakaianOptions()
    {
        try {
            // Load dari database MySQL
            $this->jenisPakaianOptions = JenisPakaian::query()
                ->where('status', 'Aktif')
                ->orderBy('nama_jenis')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kode' => $item->kode_jenis,
                        'name' => $item->nama_jenis,
                    ];
                });
        } catch (\Exception $e) {
            $this->jenisPakaianOptions = collect([]);
        }
    }

    protected function parseInitialValue($value)
    {
        // Try to decode JSON first
        $jsonData = json_decode($value, true);

        if (is_array($jsonData) && !empty($jsonData)) {
            // Parse dari JSON format: [{"nama": "Kemeja", "jumlah": 3}]
            foreach ($jsonData as $item) {
                if (isset($item['nama']) && isset($item['jumlah'])) {
                    $nama = $item['nama'];
                    $jumlah = (int) $item['jumlah'];

                    // Cari ID jenis dari nama
                    $jenis = $this->jenisPakaianOptions->firstWhere('name', $nama);

                    $this->items[] = [
                        'jenis_id' => $jenis['id'] ?? '',
                        'nama' => $nama,
                        'jumlah' => $jumlah,
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
                        'jumlah' => $jumlah,
                    ];
                }
            }
        }

        // Jika parsing gagal atau kosong, tambah 1 baris
        if (empty($this->items)) {
            $this->addRow();
        }
    }

    public function addRow()
    {
        $this->items[] = [
            'jenis_id' => '',
            'nama' => '',
            'jumlah' => 1,
        ];
    }

    public function removeRow($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Re-index array
        $this->updateOutput();
    }

    public function updatedItems($value, $key)
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

    protected function updateOutput()
    {
        // Filter items yang sudah terisi
        $validItems = array_filter($this->items, function ($item) {
            return !empty($item['jenis_id']) && !empty($item['nama']) && $item['jumlah'] > 0;
        });

        // Format JSON: [{"nama": "Kemeja", "jumlah": 3}, {"nama": "Celana", "jumlah": 2}]
        $jsonData = array_map(function ($item) {
            return [
                'nama' => $item['nama'],
                'jumlah' => (int) $item['jumlah']
            ];
        }, $validItems);

        // Convert ke JSON string untuk disimpan ke database
        $this->outputString = json_encode(array_values($jsonData));

        // Emit event ke parent component
        $this->dispatch('jenisPakaianUpdated', $this->outputString);
    }

    public function getOutputString()
    {
        return $this->outputString;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.component.key-value-jenis-pakaian');
    }
}
