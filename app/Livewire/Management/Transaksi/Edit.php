<?php

declare(strict_types=1);

namespace App\Livewire\Management\Transaksi;

use App\Helper\Database\KurirHelper;
use App\Helper\Database\LayananHelper;
use App\Helper\Database\PelangganHelper;
use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\PromoHelper;
use App\Helper\Database\ReferralHelper;
use App\Helper\Database\TransaksiHelper;
use App\Helper\Database\TransaksiLayananHelper;
use App\Models\Kurir;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Promo;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Mary\Traits\WithMediaSync;

#[Title('Edit Transaksi')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;
    use WithFileUploads;
    use WithMediaSync;

    public int $transaksiId;

    public array $pelangganOptions = [];

    public array $promoOptions = [];

    public array $referralOptions = [];

    public array $kurirOptions = [];

    // Metadata fields
    public ?int $selectedPromoId = null;

    public ?int $selectedReferralId = null;

    public ?int $kurirJemputId = null;

    public ?int $kurirAntarId = null;

    // Image Library - Temporary files
    #[Rule(['fotoTimbangan.*' => 'nullable|image|max:5120'])]
    public array $fotoTimbangan = [];

    #[Rule(['fotoPembayaran.*' => 'nullable|image|max:5120'])]
    public array $fotoPembayaran = [];

    // Image Library - Library metadata
    public Collection $libraryTimbangan;

    public Collection $libraryPembayaran;

    public array $formData = [
        'kode_transaksi' => '',
        'tanggal_masuk' => '',
        'kasir_id' => null,
        'pelanggan_id' => '',
        'referral_id' => null,
        'nama_pelanggan' => '',
        'subtotal' => 0,
        'total' => 0,
        'metode_pembayaran' => TransaksiHelper::METODE_BAYAR_SAAT_JEMPUT,
        'tipe_bayar' => null,
        'status_bayar' => TransaksiHelper::STATUS_BELUM_BAYAR,
        'tanggal_bayar' => null,
        'jumlah_bayar' => null,
        'tanggal_selesai' => '',
        'status' => TransaksiHelper::STATUS_MENUNGGU,
        'catatan' => '',
        'catatan_internal' => '',
    ];

    // Multi-layanan data
    public array $multiLayananData = [
        'items' => [],
        'totalSubtotal' => 0,
        'totalGrandTotal' => 0,
    ];

    // Promo validation result
    public array $promoResult = [
        'valid' => false,
        'diskon' => 0,
        'pesan' => '',
        'tipe' => '',
    ];

    // Cache promo object
    protected $cachedPromo = null;

    protected $listeners = ['jenisPakaianUpdated', 'multiLayananUpdated'];

    public function mount(int $id): void
    {
        $this->transaksiId = $id;
        $this->loadTransaksi();
        $this->search();
        $this->loadOptions();
    }

    public function loadOptions(): void
    {
        $pelangganId = $this->formData['pelanggan_id'] ?? null;
        $this->promoOptions = PromoHelper::getPromoOptions($pelangganId ? (int) $pelangganId : null);
        $this->referralOptions = ReferralHelper::getReferralOptions();
        $this->kurirOptions = KurirHelper::getKurirOptions();
    }

    public function search(string $term = ''): void
    {
        try {
            // Menggunakan PelangganHelper untuk get options (approach OOP)
            $this->pelangganOptions = PelangganHelper::getPelangganOptions($term, 10);
        } catch (Exception $e) {
            Log::error('Error searching pelanggan in Edit', [
                'term' => $term,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->pelangganOptions = [];
        }
    }

    /**
     * Auto-update status ke "Proses" ketika kurir jemput dipilih
     */
    public function updatedKurirJemputId($value): void
    {
        // Jika kurir jemput dipilih dan status masih "Menunggu", auto-update ke "Proses"
        if ($value && $this->formData['status'] === 'Menunggu') {
            $this->formData['status'] = 'Proses';
            $this->info('Status otomatis diubah menjadi "Proses" karena kurir jemput sudah dipilih', position: 'toast-bottom');
        }
    }

    protected function loadTransaksi(): void
    {
        try {
            $transaksi = Transaksi::with(['transaksiLayanan.layanan', 'transaksiPromo.promo'])->findOrFail($this->transaksiId);

            $this->formData = [
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d\TH:i'),
                'kasir_id' => $transaksi->kasir_id,
                'pelanggan_id' => $transaksi->pelanggan_id,
                'referral_id' => $transaksi->referral_id,
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'subtotal' => $transaksi->subtotal,
                'total' => $transaksi->total,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'tipe_bayar' => $transaksi->tipe_bayar,
                'status_bayar' => $transaksi->status_bayar,
                'tanggal_bayar' => $transaksi->tanggal_bayar
                    ? $transaksi->tanggal_bayar->format('Y-m-d\TH:i')
                    : null,
                'jumlah_bayar' => $transaksi->jumlah_bayar,
                'tanggal_selesai' => $transaksi->tanggal_selesai
                    ? $transaksi->tanggal_selesai->format('Y-m-d\TH:i')
                    : '',
                'status' => $transaksi->status,
                'catatan' => $transaksi->catatan ?? '',
                'catatan_internal' => $transaksi->catatan_internal ?? '',
            ];

            // Set selectedReferralId untuk UI dropdown
            if ($transaksi->referral_id) {
                $this->selectedReferralId = $transaksi->referral_id;
            }

            // Set selectedPromoId dari transaksiPromo jika ada
            $latestPromo = $transaksi->transaksiPromo()->latest()->first();
            if ($latestPromo) {
                $this->selectedPromoId = $latestPromo->promo_id;
            }

            // Load multi-layanan data
            $this->loadMultiLayananData($transaksi);

            // Load promo result dari transaksiPromo existing
            if ($latestPromo) {
                $this->promoResult = [
                    'valid' => true,
                    'diskon' => $latestPromo->nilai_diskon_nominal ?? 0,
                    'pesan' => "Promo {$latestPromo->nama_promo} diterapkan",
                    'tipe' => $latestPromo->tipe_diskon ?? '',
                ];
            }

            // Load metadata
            $this->loadMetadata($transaksi);

            // Load existing image library metadata - ensure it's always Collection
            // Handle backward compatibility: null, string JSON, array, or Collection
            $this->libraryTimbangan = $this->ensureCollection($transaksi->foto_bukti_timbangan);
            $this->libraryPembayaran = $this->ensureCollection($transaksi->foto_bukti_pembayaran);
        } catch (Exception $e) {
            Log::error('Transaksi Edit: Failed to load transaksi', [
                'transaksi_id' => $this->transaksiId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Transaksi tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/transaksi', navigate: true);
        }
    }

    protected function loadMultiLayananData($transaksi)
    {
        $items = [];
        $totalSubtotal = 0;

        // Load transaksi layanan data
        if ($transaksi->transaksiLayanan && $transaksi->transaksiLayanan->count() > 0) {
            foreach ($transaksi->transaksiLayanan as $tl) {
                $layanan = $tl->layanan;
                if (! $layanan) {
                    continue; // Skip if layanan not found
                }

                $item = [
                    'layanan_id' => $tl->layanan_id,
                    'nama_layanan' => $tl->nama_layanan ?? $layanan->nama_layanan,
                    'tipe_layanan' => $layanan->tipe_layanan ?? 'per_kg',
                    'subtotal' => $tl->subtotal ?? 0,
                    'jenis_pakaian' => [],
                    'satuan' => $layanan->satuan ?? 'kg',
                ];

                if (TransaksiLayananHelper::isPerKg($tl)) {
                    $item['berat_kg'] = $tl->berat_kg ?? 0;
                    $item['harga_per_kg'] = $tl->harga_per_kg ?? $layanan->harga_per_kg ?? 0;

                    // Decode jenis_pakaian from JSON
                    if (! empty($tl->jenis_pakaian)) {
                        if (is_string($tl->jenis_pakaian)) {
                            $decoded = json_decode($tl->jenis_pakaian, true);
                            $item['jenis_pakaian'] = is_array($decoded) ? $decoded : [];
                        } elseif (is_array($tl->jenis_pakaian)) {
                            $item['jenis_pakaian'] = $tl->jenis_pakaian;
                        }
                    }

                    // Recalculate subtotal if missing
                    if (empty($item['subtotal']) && $item['berat_kg'] > 0 && $item['harga_per_kg'] > 0) {
                        $item['subtotal'] = $item['berat_kg'] * $item['harga_per_kg'];
                    }
                } else {
                    $item['jumlah_satuan'] = $tl->jumlah_satuan ?? 1;
                    $item['harga_per_satuan'] = $tl->harga_per_satuan ?? $layanan->harga_per_satuan ?? 0;

                    // Recalculate subtotal if missing
                    if (empty($item['subtotal']) && $item['jumlah_satuan'] > 0 && $item['harga_per_satuan'] > 0) {
                        $item['subtotal'] = $item['jumlah_satuan'] * $item['harga_per_satuan'];
                    }
                }

                $items[] = $item;
                $totalSubtotal += $item['subtotal'];
            }
        } else {
            // Fallback for old single-layanan transactions
            if ($transaksi->layanan_id) {
                $layanan = Layanan::find($transaksi->layanan_id);
                if ($layanan) {
                    $item = [
                        'layanan_id' => $transaksi->layanan_id,
                        'nama_layanan' => $transaksi->nama_layanan ?? $layanan->nama_layanan,
                        'tipe_layanan' => $layanan->tipe_layanan ?? 'per_kg',
                        'subtotal' => $transaksi->subtotal ?? 0,
                        'jenis_pakaian' => [],
                        'satuan' => $layanan->satuan ?? 'kg',
                    ];

                    if ($layanan->tipe_layanan === 'per_kg') {
                        $item['berat_kg'] = $transaksi->berat_kg ?? 0;
                        $item['harga_per_kg'] = $transaksi->harga_per_kg ?? $layanan->harga_per_kg ?? 0;

                        // Decode jenis_pakaian from JSON
                        if (! empty($transaksi->jenis_pakaian)) {
                            if (is_string($transaksi->jenis_pakaian)) {
                                $decoded = json_decode($transaksi->jenis_pakaian, true);
                                $item['jenis_pakaian'] = is_array($decoded) ? $decoded : [];
                            } elseif (is_array($transaksi->jenis_pakaian)) {
                                $item['jenis_pakaian'] = $transaksi->jenis_pakaian;
                            }
                        }
                    } else {
                        $item['jumlah_satuan'] = $transaksi->total_item ?? 1;
                        $item['harga_per_satuan'] = $layanan->harga_per_satuan ?? 0;
                    }

                    $items[] = $item;
                    $totalSubtotal = $item['subtotal'];
                }
            }
        }

        $this->multiLayananData = [
            'items' => $items,
            'totalSubtotal' => $totalSubtotal,
            'totalGrandTotal' => $totalSubtotal,
        ];
    }

    public function jenisPakaianUpdated($outputString)
    {
        // Legacy method - not used in multi-layanan
    }

    public function multiLayananUpdated(array $data): void
    {
        $this->multiLayananData = $data;
        $this->formData['subtotal'] = $data['totalSubtotal'];

        // Recalculate promo diskon jika ada promo yang dipilih
        if ($this->selectedPromoId) {
            $this->calculatePromoDiskon();
        } else {
            $this->formData['total'] = $data['totalGrandTotal'];
        }

        // Calculate tanggal selesai based on layanan with longest duration
        $this->calculateTanggalSelesaiFromMultiLayanan();
    }

    protected function calculateTanggalSelesaiFromMultiLayanan(): void
    {
        // Create temporary transaksi object untuk menggunakan TransaksiHelper
        $tempTransaksi = new Transaksi;
        $tempTransaksi->tanggal_masuk = $this->formData['tanggal_masuk'];
        $tempTransaksi->setRelation('transaksiLayanan', collect($this->multiLayananData['items'])->map(function ($item) {
            if (! empty($item['layanan_id'])) {
                $tempTransaksiLayanan = new TransaksiLayanan;
                $tempTransaksiLayanan->setRelation('layanan', Layanan::find($item['layanan_id']));

                return $tempTransaksiLayanan;
            }

            return null;
        })->filter());

        $tanggalTerlama = TransaksiHelper::getTanggalSelesaiTerlama($tempTransaksi);
        $this->formData['tanggal_selesai'] = $tanggalTerlama ? $tanggalTerlama->format('Y-m-d H:i') : '';
    }

    public function updatedSelectedPromoId(): void
    {
        $this->calculatePromoDiskon();
    }

    protected function calculatePromoDiskon(): void
    {
        $promoId = $this->selectedPromoId;

        if (! $promoId) {
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => '',
                'tipe' => '',
            ];
            $this->cachedPromo = null;
            $this->updateTotal();

            return;
        }

        // Convert to integer for type safety
        $promoId = (int) $promoId;

        // Use cached promo if available
        if ($this->cachedPromo && $this->cachedPromo->id === $promoId) {
            $promo = $this->cachedPromo;
        } else {
            $promo = PromoHelper::getById($promoId);
            $this->cachedPromo = $promo;
        }

        if (! $promo) {
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => 'Promo tidak ditemukan',
                'tipe' => '',
            ];
            $this->cachedPromo = null;
            $this->updateTotal();

            return;
        }

        // Hitung total berat dari multiLayananData
        $totalBerat = 0.0;
        foreach ($this->multiLayananData['items'] as $item) {
            if (($item['tipe_layanan'] ?? '') === 'per_kg') {
                $totalBerat += (float) ($item['berat_kg'] ?? 0);
            }
        }

        // Hitung diskon menggunakan PromoHelper
        $pelangganId = $this->formData['pelanggan_id'] ? (int) $this->formData['pelanggan_id'] : null;
        $subtotal = (int) ($this->multiLayananData['totalSubtotal'] ?? 0);

        // Pass transaksiId agar tidak dihitung sebagai usage saat edit transaksi yang sama
        $this->promoResult = PromoHelper::hitungDiskon($promo, $subtotal, $totalBerat, $pelangganId, $this->transaksiId);

        // Di Edit: Tidak tampilkan warning, karena data masih bisa berubah
        // Validasi promo akan dilakukan nanti di Kasir saat pembayaran
        // if (!$this->promoResult['valid']) {
        //     $this->warning($this->promoResult['pesan'], position: 'toast-bottom');
        // }

        $this->updateTotal();
    }

    protected function updateTotal(): void
    {
        $subtotal = (int) ($this->multiLayananData['totalSubtotal'] ?? 0);
        $diskon = (int) ($this->promoResult['diskon'] ?? 0);

        $this->multiLayananData['totalGrandTotal'] = $subtotal - $diskon;
        $this->formData['total'] = $this->multiLayananData['totalGrandTotal'];
        $this->formData['subtotal'] = $subtotal;
    }

    public function updatedFormDataPelangganId(mixed $value): void
    {
        if ($value) {
            $pelanggan = Pelanggan::find($value);
            if ($pelanggan instanceof Pelanggan) {
                $this->formData['nama_pelanggan'] = $pelanggan->nama;
            }
        }

        // Reload promo options karena pelanggan berubah
        $this->loadOptions();
    }

    protected function loadMetadata(Transaksi $transaksi): void
    {
        // Load kurir info
        $kurirJemput = TransaksiHelper::getKurirJemput($transaksi);
        if ($kurirJemput && isset($kurirJemput['id'])) {
            $this->kurirJemputId = $kurirJemput['id'];
        }

        $kurirAntar = TransaksiHelper::getKurirAntar($transaksi);
        if ($kurirAntar && isset($kurirAntar['id'])) {
            $this->kurirAntarId = $kurirAntar['id'];
        }
    }

    /**
     * Ensure data is always Collection for Image Library component
     * Handle backward compatibility: null, string JSON, array, or Collection
     */
    protected function ensureCollection(mixed $data): Collection
    {
        $collection = null;

        // Already a Collection
        if ($data instanceof Collection) {
            $collection = $data;
        }
        // Null or empty
        elseif (empty($data)) {
            return new Collection;
        }
        // Array
        elseif (is_array($data)) {
            $collection = new Collection($data);
        }
        // String JSON (dari data lama)
        elseif (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $collection = new Collection($decoded);
            }
        }

        // Fallback: empty Collection
        if (! $collection) {
            return new Collection;
        }

        // Ensure each item has 'uuid' key for Mary UI Image Library
        return $this->ensureCollectionHasUuid($collection);
    }

    /**
     * Ensure each item in Collection has UUID (required by Mary UI Image Library)
     */
    protected function ensureCollectionHasUuid(Collection $collection): Collection
    {
        return $collection->map(function ($item) {
            // Convert to array if it's an object or anything else
            if (! is_array($item)) {
                $item = (array) $item;
            }

            // Always ensure uuid exists
            if (! isset($item['uuid']) || empty($item['uuid'])) {
                $item['uuid'] = \Illuminate\Support\Str::uuid()->toString();
            }

            return $item;
        });
    }

    public function printReceipt(): void
    {
        // Redirect ke halaman print receipt di tab baru
        $this->dispatch('open-print-window', url: route('receipt.print', ['id' => $this->transaksiId]));
    }

    public function save()
    {
        // Validasi multi-layanan
        if (empty($this->multiLayananData['items'])) {
            Log::warning('Transaksi Edit validation failed: items layanan kosong');
            $this->error('Tambahkan minimal 1 layanan!', position: 'toast-bottom');

            return;
        }

        $hasValidLayanan = false;

        // Ambil min_berat_kg dari pengaturan (OOP approach)
        $minBeratKg = (float) PengaturanHelper::getValue('min_berat_kg', 2);

        foreach ($this->multiLayananData['items'] as $index => $item) {
            if (! empty($item['layanan_id'])) {
                if ($item['tipe_layanan'] === 'per_kg') {
                    // Validasi hanya jika berat_kg atau jenis_pakaian sudah diisi
                    if (! empty($item['berat_kg']) && $item['berat_kg'] < $minBeratKg) {
                        Log::warning('Transaksi Edit validation failed: berat_kg invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'berat_kg' => $item['berat_kg'] ?? null,
                            'min_berat_kg_required' => $minBeratKg,
                        ]);
                        $this->error("Berat minimal {$minBeratKg} kg untuk layanan ".$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                    // Berat dan jenis pakaian sekarang optional untuk Edit (belum dijemput)
                } else {
                    // Jumlah satuan sekarang optional untuk Edit (belum dijemput)
                    if (! empty($item['jumlah_satuan']) && $item['jumlah_satuan'] < 1) {
                        Log::warning('Transaksi Edit validation failed: jumlah_satuan invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'jumlah_satuan' => $item['jumlah_satuan'] ?? null,
                        ]);
                        $this->error('Jumlah minimal 1 untuk layanan '.$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                }
                $hasValidLayanan = true;
            }
        }

        if (! $hasValidLayanan) {
            Log::warning('Transaksi Edit validation failed: tidak ada layanan valid');
            $this->error('Pilih layanan yang valid terlebih dahulu!', position: 'toast-bottom');

            return;
        }

        $this->validate([
            'formData.tanggal_masuk' => 'required|date',
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.metode_pembayaran' => [
                'required',
                'in:'.implode(',', TransaksiHelper::getAllMetodePembayaran()),
            ],
            'formData.tipe_bayar' => [
                'nullable',
                'in:'.implode(',', TransaksiHelper::getAllTipeBayar()),
            ],
            'formData.status_bayar' => [
                'required',
                'in:'.implode(',', TransaksiHelper::getAllStatusBayar()),
            ],
            'formData.status' => [
                'required',
                'in:'.implode(',', TransaksiHelper::getAllStatus()),
            ],
            'fotoTimbangan.*' => 'nullable|image|max:5120',
            'fotoPembayaran.*' => 'nullable|image|max:5120',
        ]);

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () {
                $transaksi = Transaksi::findOrFail($this->transaksiId);

                // ID Kasir
                $this->formData['kasir_id'] = Auth::id() ?? 1;

                // Update referral_id from selectedReferralId
                $this->formData['referral_id'] = $this->selectedReferralId;

                // Prepare transaksi data
                $transaksiData = $this->formData;
                $transaksiData['jumlah_layanan'] = count($this->multiLayananData['items']);
                $transaksiData['total_berat'] = 0;
                $transaksiData['total_item'] = 0;

                // Calculate total berat dan total item
                foreach ($this->multiLayananData['items'] as $item) {
                    if ($item['tipe_layanan'] === 'per_kg') {
                        $transaksiData['total_berat'] += (float) ($item['berat_kg'] ?? 0);
                    } else {
                        $transaksiData['total_item'] += (int) ($item['jumlah_satuan'] ?? 0);
                    }
                }

                // Update transaksi
                $transaksi->update($transaksiData);

                // === Handle File Uploads dengan Image Library ===
                // Ensure libraries have UUID before sync (fix Mary UI requirement)
                $this->libraryTimbangan = $this->ensureCollectionHasUuid($this->libraryTimbangan);
                $this->libraryPembayaran = $this->ensureCollectionHasUuid($this->libraryPembayaran);

                // CRITICAL: Update model attributes with UUID-ensured data BEFORE syncMedia
                // This prevents Mary UI from accessing old data without UUID from database
                $transaksi->foto_bukti_timbangan = $this->libraryTimbangan;
                $transaksi->foto_bukti_pembayaran = $this->libraryPembayaran;

                // Sync foto bukti timbangan using WithMediaSync trait
                $this->syncMedia(
                    model: $transaksi,
                    library: 'libraryTimbangan',
                    files: 'fotoTimbangan',
                    storage_subpath: '/transaksi/timbangan',
                    model_field: 'foto_bukti_timbangan',
                    visibility: 'public',
                    disk: 'public'
                );

                // Sync foto bukti pembayaran using WithMediaSync trait
                $this->syncMedia(
                    model: $transaksi,
                    library: 'libraryPembayaran',
                    files: 'fotoPembayaran',
                    storage_subpath: '/transaksi/pembayaran',
                    model_field: 'foto_bukti_pembayaran',
                    visibility: 'public',
                    disk: 'public'
                );

                // === Handle Promo & Kurir ===
                // Note: referral_id already handled in formData update above

                // 1. Handle promo - hapus semua promo lama dan tambah yang baru jika ada
                $transaksi->transaksiPromo()->delete();

                // Di Edit: Simpan promo yang dipilih pelanggan meskipun belum valid
                // (karena data berat/layanan mungkin belum lengkap)
                // Validasi promo akan dilakukan ulang di Kasir saat pembayaran
                if ($this->selectedPromoId) {
                    $promo = $this->cachedPromo ?? PromoHelper::getById((int) $this->selectedPromoId);
                    if ($promo) {
                        // Create TransaksiPromo record dengan snapshot data promo
                        $transaksi->transaksiPromo()->create([
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                            'nama_promo' => $promo->nama_promo,
                            'tipe_diskon' => $promo->tipe_diskon,
                            'nilai_diskon_persen' => $promo->nilai_diskon,
                            'nilai_diskon_nominal' => $this->promoResult['diskon'] ?? 0, // Bisa 0 jika belum valid
                            'diskon_maksimal' => $promo->diskon_maksimal,
                            'gratis_kg' => $promo->gratis_kg,
                            'gratis_hari' => $promo->gratis_hari,
                            'diterapkan_ke' => $promo->diterapkan_ke ?? 'subtotal', // Default 'subtotal' jika null
                            'layanan_id' => $promo->layanan_id,
                            'urutan_apply' => 1, // Future: untuk multiple promo
                        ]);

                        Log::info('Transaksi Edit: Promo saved', [
                            'transaksi_id' => $transaksi->id,
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                            'diskon_nominal' => $this->promoResult['diskon'] ?? 0,
                            'valid' => $this->promoResult['valid'] ?? false,
                        ]);
                    }
                }

                // 2. Kurir Jemput
                if ($this->kurirJemputId) {
                    $kurir = Kurir::find($this->kurirJemputId);
                    if ($kurir) {
                        TransaksiHelper::setKurirJemput($transaksi, $kurir->id, $kurir->nama);

                        // Auto-update status menjadi "Proses" jika kurir jemput sudah dipilih
                        // (karena barang sudah/akan dijemput)
                        if ($transaksi->status === 'Menunggu') {
                            $transaksi->status = 'Proses';
                            Log::info('Transaksi Edit: Status auto-updated to Proses', [
                                'transaksi_id' => $transaksi->id,
                                'kurir_jemput_id' => $kurir->id,
                                'kurir_jemput_nama' => $kurir->nama,
                            ]);
                        }
                    }
                } else {
                    TransaksiHelper::setKurirJemput($transaksi, null);
                }

                // 3. Kurir Antar
                if ($this->kurirAntarId) {
                    $kurir = Kurir::find($this->kurirAntarId);
                    if ($kurir) {
                        TransaksiHelper::setKurirAntar($transaksi, $kurir->id, $kurir->nama);
                    }
                } else {
                    TransaksiHelper::setKurirAntar($transaksi, null);
                }

                // Save ke database
                $transaksi->save();

                // Hapus transaksi layanan lama (force delete untuk menghindari duplikasi)
                DB::table('transaksi_layanan')->where('transaksi_id', $transaksi->id)->delete();

                // Collect all layanan IDs and load them at once to prevent N+1 queries
                $layananIds = collect($this->multiLayananData['items'])
                    ->pluck('layanan_id')
                    ->filter()
                    ->unique()
                    ->toArray();

                $layananMap = Layanan::whereIn('id', $layananIds)->get()->keyBy('id');

                // Simpan detail transaksi layanan baru
                foreach ($this->multiLayananData['items'] as $index => $item) {
                    if (! empty($item['layanan_id'])) {
                        // Get layanan data dari loaded map
                        $layanan = $layananMap->get($item['layanan_id']);

                        if (! $layanan) {
                            Log::error('Transaksi Edit: Layanan not found', [
                                'layanan_id' => $item['layanan_id'],
                                'index' => $index,
                            ]);
                            throw new Exception("Layanan dengan ID {$item['layanan_id']} tidak ditemukan");
                        }

                        $transaksiLayananData = [
                            'transaksi_id' => $transaksi->id,
                            'layanan_id' => $item['layanan_id'],
                            'nama_layanan' => $item['nama_layanan'] ?? $layanan->nama_layanan,
                            'subtotal' => $item['subtotal'] ?? 0,
                        ];

                        if ($item['tipe_layanan'] === 'per_kg') {
                            // Prepare data untuk per_kg
                            $transaksiLayananData['jenis_pakaian'] = ! empty($item['jenis_pakaian']) ? $item['jenis_pakaian'] : null;
                            $transaksiLayananData['berat_kg'] = $item['berat_kg'] ?? 0;
                            $transaksiLayananData['harga_per_kg'] = $item['harga_per_kg'] ?? $layanan->harga_per_kg;
                            $transaksiLayananData['jumlah_satuan'] = null;
                            $transaksiLayananData['harga_per_satuan'] = null;

                            // Calculate subtotal jika belum ada
                            if (empty($item['subtotal'])) {
                                $berat = (float) ($item['berat_kg'] ?? 0);
                                $harga = (int) $transaksiLayananData['harga_per_kg'];
                                $transaksiLayananData['subtotal'] = (int) ($berat * $harga);
                            }
                        } else {
                            // Prepare data untuk per_satuan
                            $transaksiLayananData['jenis_pakaian'] = null;
                            $transaksiLayananData['berat_kg'] = null;
                            $transaksiLayananData['harga_per_kg'] = null;
                            $transaksiLayananData['jumlah_satuan'] = $item['jumlah_satuan'] ?? 1;
                            $transaksiLayananData['harga_per_satuan'] = $item['harga_per_satuan'] ?? $layanan->harga_per_satuan;

                            // Calculate subtotal jika belum ada
                            if (empty($item['subtotal'])) {
                                $jumlah = (int) ($item['jumlah_satuan'] ?? 1);
                                $harga = (int) $transaksiLayananData['harga_per_satuan'];
                                $transaksiLayananData['subtotal'] = $jumlah * $harga;
                            }
                        }

                        // Create TransaksiLayanan using Model (untuk automatic JSON casting)
                        $transaksiLayananData['created_at'] = now();
                        $transaksiLayananData['updated_at'] = now();

                        try {
                            TransaksiLayanan::create($transaksiLayananData);
                        } catch (Exception $e) {
                            Log::error('Transaksi Edit: Failed to create TransaksiLayanan', [
                                'error' => $e->getMessage(),
                                'data' => $transaksiLayananData,
                                'index' => $index,
                            ]);
                            throw $e;
                        }
                    }
                }
            });

            $this->success('Transaksi berhasil diupdate!', position: 'toast-bottom');

            return $this->redirect('/management/transaksi', navigate: true);
        } catch (QueryException $e) {
            Log::error('Transaksi Edit: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            // Jangan expose SQL error ke user - security risk!
            $this->error('Gagal menyimpan transaksi. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Transaksi Edit: Unexpected error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
                'multiLayananData' => $this->multiLayananData,
            ]);

            // Jangan expose technical error ke user - security risk!
            $this->error('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        }
    }

    public function getPelangganOptions(): array
    {
        try {
            return PelangganHelper::getPelangganOptions('', 100);
        } catch (Exception $e) {
            Log::error('Transaksi Edit: Error getting pelanggan options', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function getLayananOptions(): array
    {
        return LayananHelper::getLayananOptions();
    }

    public function getStatusOptions(): array
    {
        return TransaksiHelper::getStatusOptions();
    }

    public function getMetodePembayaranOptions(): array
    {
        return TransaksiHelper::getMetodePembayaranOptions();
    }

    public function getTipeBayarOptions(): array
    {
        return TransaksiHelper::getTipeBayarOptions();
    }

    public function getStatusBayarOptions(): array
    {
        return TransaksiHelper::getStatusBayarOptions();
    }

    public function render()
    {
        return view('livewire.management.transaksi.edit', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'metodePembayaranOptions' => $this->getMetodePembayaranOptions(),
            'tipeBayarOptions' => $this->getTipeBayarOptions(),
            'statusBayarOptions' => $this->getStatusBayarOptions(),
        ]);
    }
}
