<?php

namespace App\Livewire\Admin\Component;

use App\Models\Layanan;
use Livewire\Component;
use App\Models\JenisPakaian;
use Illuminate\Support\Facades\Log;

class MultiLayananForm extends Component
{
    public array $items = [];
    public array $layananOptions = [];
    public array $jenisPakaianOptions = [];

    // Summary data
    public int $totalSubtotal = 0;
    public int $totalDiskon = 0;
    public int $totalGrandTotal = 0;

    public function mount($items = [])
    {
        $this->loadOptions();

        // Load items if provided (for edit mode)
        if (!empty($items)) {
            // Ensure jenis_pakaian is properly initialized for each item
            foreach ($items as $key => $item) {
                // If tipe is per_kg, ensure jenis_pakaian is an array
                if (isset($item['tipe_layanan']) && $item['tipe_layanan'] === 'per_kg') {
                    if (!isset($item['jenis_pakaian'])) {
                        $items[$key]['jenis_pakaian'] = [];
                    }
                }
            }

            $this->items = $items;
            // Recalculate totals after loading items
            $this->calculateTotals();
        } else {
            // Add first layanan by default (for create mode)
            $this->addLayanan();
        }
    }

    protected function loadOptions()
    {
        try {
            $this->layananOptions = Layanan::where('status', 'Aktif')
                ->orderBy('nama_layanan')
                ->get()
                ->map(function($l) {
                    if ($l->tipe_layanan === 'per_kg') {
                        $price = $l->harga_per_kg;
                        $unit = 'kg';
                    } else {
                        $price = $l->harga_per_satuan;
                        $unit = $l->satuan ?? 'pcs';
                    }

                    return [
                        'id' => $l->id,
                        'name' => $l->nama_layanan . ' - ' . $l->tipe_layanan . ' (Rp ' . number_format($price, 0, ',', '.') . '/' . $unit . ')'
                    ];
                })
                ->toArray();

            $this->jenisPakaianOptions = JenisPakaian::where('status', 'Aktif')
                ->orderBy('nama_jenis')
                ->get()
                ->map(fn($jp) => [
                    'id' => $jp->id,
                    'name' => $jp->nama_jenis
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading layanan/jenis pakaian options: ' . $e->getMessage());
            $this->layananOptions = [];
            $this->jenisPakaianOptions = [];
        }
    }

    public function addLayanan()
    {
        $this->items[] = [
            'layanan_id' => '',
            'nama_layanan' => '',
            'tipe_layanan' => '',
            'jenis_pakaian' => [], // Initialize as empty array
            'berat_kg' => '',
            'harga_per_kg' => 0,
            'jumlah_satuan' => 1,
            'harga_per_satuan' => 0,
            'satuan' => 'kg', // Default satuan
            'subtotal' => 0,
        ];

        $this->calculateTotals();
    }

    public function removeLayanan($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function addJenisPakaian($layananIndex)
    {
        if (!isset($this->items[$layananIndex]['jenis_pakaian'])) {
            $this->items[$layananIndex]['jenis_pakaian'] = [];
        }

        $this->items[$layananIndex]['jenis_pakaian'][] = [
            'jenis_id' => '',
            'nama' => '',
            'jumlah' => 1
        ];

        $this->calculateTotals();
    }

    public function removeJenisPakaian($layananIndex, $jenisIndex)
    {
        if (isset($this->items[$layananIndex]['jenis_pakaian'][$jenisIndex])) {
            unset($this->items[$layananIndex]['jenis_pakaian'][$jenisIndex]);
            $this->items[$layananIndex]['jenis_pakaian'] = array_values($this->items[$layananIndex]['jenis_pakaian']);
        }
        $this->calculateTotals();
    }

    public function updatedItems($value, $key)
    {
        // Extract index and property from key like "0.layanan_id" (Livewire format)
        $parts = explode('.', $key);

        // Handle nested keys like "0.jenis_pakaian.0.jenis_id"
        if (count($parts) >= 2 && is_numeric($parts[0])) {
            $index = (int) $parts[0];
            $property = $parts[1] ?? '';
        } else {
            return;
        }

        // Update layanan info when layanan_id changes
        if ($property === 'layanan_id' && !empty($value)) {
            $layanan = Layanan::find($value);
            if ($layanan) {
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
        }

        // Update jenis pakaian when nested key changes (jenis_pakaian.0.jenis_id or jenis_pakaian.0.jumlah)
        if (count($parts) >= 4 && $parts[1] === 'jenis_pakaian') {
            $jenisIndex = (int) ($parts[2] ?? 0);
            $field = $parts[3] ?? '';

            // Safety check - ensure jenis_pakaian array exists and index is valid
            if (isset($this->items[$index]['jenis_pakaian'][$jenisIndex])) {
                if ($field === 'jenis_id') {
                    $jenisPakaian = JenisPakaian::find($value);
                    if ($jenisPakaian) {
                        $this->items[$index]['jenis_pakaian'][$jenisIndex]['nama'] = $jenisPakaian->nama_jenis;
                    }
                } elseif ($field === 'jumlah') {
                    $this->items[$index]['jenis_pakaian'][$jenisIndex]['jumlah'] = $value;
                }
            }
        }

        $this->calculateTotals();
    }

    protected function calculateTotals()
    {
        $totalSubtotal = 0;

        foreach ($this->items as $key => $item) {
            $subtotal = 0;

            if (!empty($item['layanan_id'])) {
                if ($item['tipe_layanan'] === 'per_kg') {
                    // Calculate for per kg services
                    $berat = (float) ($item['berat_kg'] ?? 0);
                    $harga = (int) ($item['harga_per_kg'] ?? 0);
                    $subtotal = $berat * $harga;
                } else {
                    // Calculate for per satuan services
                    $jumlah = (int) ($item['jumlah_satuan'] ?? 0);
                    $harga = (int) ($item['harga_per_satuan'] ?? 0);
                    $subtotal = $jumlah * $harga;
                }
            }

            $this->items[$key]['subtotal'] = $subtotal;
            $totalSubtotal += $subtotal;
        }

        $this->totalSubtotal = $totalSubtotal;
        $this->totalGrandTotal = $totalSubtotal - $this->totalDiskon;

        // Emit event to parent
        $this->dispatch('multiLayananUpdated', [
            'items' => $this->items,
            'totalSubtotal' => $this->totalSubtotal,
            'totalGrandTotal' => $this->totalGrandTotal
        ]);
    }

    public function setDiskon($diskon)
    {
        $this->totalDiskon = (int) $diskon;
        $this->totalGrandTotal = $this->totalSubtotal - $this->totalDiskon;

        $this->dispatch('multiLayananUpdated', [
            'items' => $this->items,
            'totalSubtotal' => $this->totalSubtotal,
            'totalGrandTotal' => $this->totalGrandTotal
        ]);
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getGrandTotal()
    {
        return $this->totalGrandTotal;
    }

    public function render()
    {
        return view('livewire.admin.component.multi-layanan-form');
    }
}
