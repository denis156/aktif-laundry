<?php

declare(strict_types=1);

namespace App\Livewire\Management\Components;

use App\Models\JenisPakaian;
use App\Models\Layanan;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MultiLayananForm extends Component
{
    public array $items = [];

    public array $layananOptions = [];

    public array $jenisPakaianOptions = [];

    // Summary data
    public int $totalSubtotal = 0;

    public int $totalDiskon = 0;

    public int $totalGrandTotal = 0;

    public function mount(array $items = []): void
    {
        $this->loadOptions();

        if (! empty($items)) {
            foreach ($items as $key => $item) {
                if (isset($item['tipe_layanan']) && $item['tipe_layanan'] === 'per_kg') {
                    // ! OPTIONAL: Jenis pakaian tidak wajib diisi, default ke empty array jika tidak ada
                    if (! isset($item['jenis_pakaian'])) {
                        $items[$key]['jenis_pakaian'] = [];
                    }
                }
            }

            $this->items = $items;
            $this->calculateTotals();
        } else {
            $this->addLayanan();
        }
    }

    public function getLayananById(int $layananId): ?array
    {
        return collect($this->layananOptions)->firstWhere('id', $layananId);
    }

    public function getJenisPakaianById(int $jenisPakaianId): ?array
    {
        return collect($this->jenisPakaianOptions)->firstWhere('id', $jenisPakaianId);
    }

    protected function loadOptions(): void
    {
        try {
            $this->layananOptions = $this->getLayananOptions();
            $this->jenisPakaianOptions = $this->getJenisPakaianOptions();
        } catch (\Exception $e) {
            Log::error('Failed to load options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->layananOptions = [];
            $this->jenisPakaianOptions = [];
        }
    }

    protected function getLayananOptions(): array
    {
        return Layanan::where('status', 'Aktif')
            ->orderBy('nama_layanan')
            ->get(['id', 'nama_layanan', 'tipe_layanan', 'harga_per_kg', 'harga_per_satuan', 'satuan', 'icon'])
            ->map(function (Layanan $layanan) {
                $price = $layanan->tipe_layanan === 'per_kg'
                    ? $layanan->harga_per_kg
                    : $layanan->harga_per_satuan;

                $unit = $layanan->tipe_layanan === 'per_kg' ? 'kg' : ($layanan->satuan ?? 'pcs');

                return [
                    'id' => $layanan->id,
                    'name' => sprintf(
                        '%s - %s (Rp %s/%s)',
                        $layanan->nama_layanan,
                        $layanan->tipe_layanan,
                        number_format($price, 0, ',', '.'),
                        $unit
                    ),
                    'icon' => $layanan->icon,
                ];
            })
            ->toArray();
    }

    protected function getJenisPakaianOptions(): array
    {
        return JenisPakaian::where('status', 'Aktif')
            ->orderBy('nama_jenis')
            ->get(['id', 'nama_jenis', 'icon'])
            ->map(fn (JenisPakaian $jp) => [
                'id' => $jp->id,
                'name' => $jp->nama_jenis,
                'icon' => $jp->icon,
            ])
            ->toArray();
    }

    public function addLayanan(): void
    {
        $this->items[] = $this->createEmptyLayananItem();
        $this->calculateTotals();
    }

    protected function createEmptyLayananItem(): array
    {
        return [
            'layanan_id' => '',
            'nama_layanan' => '',
            'tipe_layanan' => '',
            'jenis_pakaian' => [], // OPTIONAL: Bisa dikosongkan, tidak wajib diisi
            'berat_kg' => '',
            'harga_per_kg' => 0,
            'jumlah_satuan' => 1,
            'harga_per_satuan' => 0,
            'satuan' => 'kg',
            'subtotal' => 0,
        ];
    }

    public function removeLayanan(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function addJenisPakaian(int $layananIndex): void
    {
        // ! OPTIONAL: Jenis pakaian tidak wajib diisi - ini hanya helper untuk menambahkan jika user mau
        if (! isset($this->items[$layananIndex]['jenis_pakaian'])) {
            $this->items[$layananIndex]['jenis_pakaian'] = [];
        }

        $this->items[$layananIndex]['jenis_pakaian'][] = $this->createEmptyJenisPakaianItem();
        $this->calculateTotals();
    }

    protected function createEmptyJenisPakaianItem(): array
    {
        return [
            'jenis_id' => '',
            'nama' => '',
            'jumlah' => 1,
        ];
    }

    public function removeJenisPakaian(int $layananIndex, int $jenisIndex): void
    {
        // ! OPTIONAL: Menghapus jenis pakaian dari array - bisa sampai kosong (optional field)
        if (isset($this->items[$layananIndex]['jenis_pakaian'][$jenisIndex])) {
            unset($this->items[$layananIndex]['jenis_pakaian'][$jenisIndex]);
            $this->items[$layananIndex]['jenis_pakaian'] = array_values(
                $this->items[$layananIndex]['jenis_pakaian']
            );
        }
        $this->calculateTotals();
    }

    public function updatedItems(mixed $value, string $key): void
    {
        $parts = explode('.', $key);

        if (count($parts) < 2 || ! is_numeric($parts[0])) {
            return;
        }

        $index = (int) $parts[0];
        $property = $parts[1] ?? '';

        if ($property === 'layanan_id' && ! empty($value)) {
            $this->updateLayananInfo($index, $value);
        }

        if (count($parts) >= 4 && $parts[1] === 'jenis_pakaian') {
            $this->updateJenisPakaianInfo($index, (int) ($parts[2] ?? 0), $parts[3] ?? '', $value);
        }

        $this->calculateTotals();
    }

    protected function updateLayananInfo(int $index, mixed $layananId): void
    {
        $layanan = Layanan::find($layananId);
        if (! $layanan instanceof Layanan) {
            return;
        }

        $this->items[$index]['nama_layanan'] = $layanan->nama_layanan;
        $this->items[$index]['tipe_layanan'] = $layanan->tipe_layanan;

        if ($layanan->tipe_layanan === 'per_kg') {
            $this->items[$index]['harga_per_kg'] = $layanan->harga_per_kg;
            $this->items[$index]['satuan'] = 'kg';
        } else {
            $this->items[$index]['harga_per_satuan'] = $layanan->harga_per_satuan;
            $this->items[$index]['satuan'] = $layanan->satuan ?? 'pcs';
        }
    }

    protected function updateJenisPakaianInfo(int $layananIndex, int $jenisIndex, string $field, mixed $value): void
    {
        if (! isset($this->items[$layananIndex]['jenis_pakaian'][$jenisIndex])) {
            return;
        }

        if ($field === 'jenis_id') {
            $jenisPakaian = JenisPakaian::find($value);
            if ($jenisPakaian instanceof JenisPakaian) {
                $this->items[$layananIndex]['jenis_pakaian'][$jenisIndex]['nama'] = $jenisPakaian->nama_jenis;
            }
        } elseif ($field === 'jumlah') {
            $this->items[$layananIndex]['jenis_pakaian'][$jenisIndex]['jumlah'] = $value;
        }
    }

    protected function calculateTotals(): void
    {
        $totalSubtotal = 0;

        foreach ($this->items as $key => $item) {
            $subtotal = $this->calculateItemSubtotal($item);
            $this->items[$key]['subtotal'] = $subtotal;
            $totalSubtotal += $subtotal;
        }

        $this->totalSubtotal = $totalSubtotal;
        $this->totalGrandTotal = $totalSubtotal - $this->totalDiskon;

        $this->dispatchMultiLayananUpdated();
    }

    protected function calculateItemSubtotal(array $item): int
    {
        if (empty($item['layanan_id'])) {
            return 0;
        }

        if ($item['tipe_layanan'] === 'per_kg') {
            $berat = (float) ($item['berat_kg'] ?? 0);
            $harga = (int) ($item['harga_per_kg'] ?? 0);

            return (int) ($berat * $harga);
        }

        $jumlah = (int) ($item['jumlah_satuan'] ?? 0);
        $harga = (int) ($item['harga_per_satuan'] ?? 0);

        return $jumlah * $harga;
    }

    protected function dispatchMultiLayananUpdated(): void
    {
        $this->dispatch('multiLayananUpdated', [
            'items' => $this->items,
            'totalSubtotal' => $this->totalSubtotal,
            'totalGrandTotal' => $this->totalGrandTotal,
        ]);
    }

    public function setDiskon(int $diskon): void
    {
        $this->totalDiskon = $diskon;
        $this->totalGrandTotal = $this->totalSubtotal - $this->totalDiskon;
        $this->dispatchMultiLayananUpdated();
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getGrandTotal(): int
    {
        return $this->totalGrandTotal;
    }

    public function render()
    {
        return view('livewire.management.components.multi-layanan-form');
    }
}
