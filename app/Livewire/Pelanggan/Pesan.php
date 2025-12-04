<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Helper\Database\PromoHelper;
use App\Helper\Database\TransaksiHelper;
use App\Models\Layanan;
use App\Models\Promo;
use App\Models\Transaksi;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Pesan')]
#[Layout('layouts.pelanggan.app')]
class Pesan extends Component
{
    use Toast;

    // Layanan yang dipilih (array of layanan IDs)
    public array $selectedLayananIds = [];

    // Form data
    public array $formData = [
        'metode_pembayaran' => TransaksiHelper::METODE_BAYAR_SAAT_ANTAR,
        'tipe_bayar' => TransaksiHelper::TIPE_TUNAI,
        'promo_id' => null,
        'catatan' => '',
    ];

    // Options
    public array $layananOptions = [];

    public array $promoOptions = [];

    // Promo validation result
    public array $promoResult = [
        'valid' => false,
        'diskon' => 0,
        'pesan' => '',
        'tipe' => '',
    ];

    // Cache promo object
    protected $cachedPromo = null;

    public function mount(): void
    {
        // Cek kelengkapan data profil (no telpon dan alamat)
        $pelanggan = Auth::user();

        // Validasi data alamat dan no telpon
        if (empty($pelanggan->no_hp) || empty($pelanggan->alamat)) {
            // Data profil belum lengkap, redirect ke halaman profile
            Log::warning('Pesan: Profile data incomplete, redirecting to profile page', [
                'pelanggan_id' => $pelanggan->id,
                'has_phone' => ! empty($pelanggan->no_hp),
                'has_address' => ! empty($pelanggan->alamat),
            ]);

            // Set flag bahwa user datang dari halaman pemesanan
            session()->flash('from_pesanan', true);

            // Redirect ke halaman profile
            $this->redirect(route('profile.pelanggan'), navigate: true);

            return;
        }

        $this->loadOptions();

        // Load selected layanan from session
        if (session()->has('selected_layanan_ids')) {
            $this->selectedLayananIds = session('selected_layanan_ids', []);

            Log::info('Pesan: Loaded selected layanan from session', [
                'selected_ids' => $this->selectedLayananIds,
            ]);
        } else {
            // If no layanan selected, redirect back to list
            Log::warning('Pesan: No layanan selected, redirecting to list');
            $this->redirect(route('pesanan.pelanggan'), navigate: true);
        }

        // Load selected promo from session if available
        if (session()->has('selected_promo_id')) {
            $this->formData['promo_id'] = session('selected_promo_id');

            Log::info('Pesan: Loaded selected promo from session', [
                'promo_id' => $this->formData['promo_id'],
            ]);

            // Calculate promo discount
            $this->calculatePromoDiskon();

            // Clear session after loading
            session()->forget('selected_promo_id');
        }
    }

    public function loadOptions(): void
    {
        try {
            // Load layanan aktif dengan detail lengkap
            $this->layananOptions = Layanan::where('status', 'Aktif')
                ->orderBy('is_popular', 'desc')
                ->orderBy('nama_layanan')
                ->get()
                ->map(function (Layanan $layanan) {
                    $price = $layanan->tipe_layanan === 'per_kg'
                        ? $layanan->harga_per_kg
                        : $layanan->harga_per_satuan;

                    $unit = $layanan->tipe_layanan === 'per_kg' ? 'kg' : ($layanan->satuan ?? 'pcs');

                    return [
                        'id' => $layanan->id,
                        'nama' => $layanan->nama_layanan,
                        'tipe' => $layanan->tipe_layanan,
                        'harga' => $price,
                        'satuan' => $unit,
                        'durasi_jam' => $layanan->durasi_jam,
                        'deskripsi' => $layanan->deskripsi,
                        'is_popular' => $layanan->is_popular,
                        'icon' => $layanan->icon,
                        'include' => $layanan->include ?? [],
                        'exclude' => $layanan->exclude ?? [],
                    ];
                })
                ->toArray();

            // Load promo aktif untuk pelanggan ini
            $pelangganId = Auth::id();
            $this->promoOptions = PromoHelper::getPromoOptions($pelangganId);
        } catch (Exception $e) {
            Log::error('Pesan: Failed to load options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->layananOptions = [];
            $this->promoOptions = [];
        }
    }

    public function toggleLayanan(int $layananId): void
    {
        if (in_array($layananId, $this->selectedLayananIds, true)) {
            // Remove dari array
            $this->selectedLayananIds = array_values(
                array_filter($this->selectedLayananIds, fn ($id) => $id !== $layananId)
            );
        } else {
            // Tambah ke array
            $this->selectedLayananIds[] = $layananId;
        }

        // Recalculate promo jika ada
        if ($this->formData['promo_id']) {
            $this->calculatePromoDiskon();
        }
    }

    public function removeLayanan(int $layananId): void
    {
        // Remove dari array
        $this->selectedLayananIds = array_values(
            array_filter($this->selectedLayananIds, fn ($id) => $id !== $layananId)
        );

        // Update session
        session(['selected_layanan_ids' => $this->selectedLayananIds]);

        Log::info('Pesan: Layanan removed', [
            'layanan_id' => $layananId,
            'remaining_ids' => $this->selectedLayananIds,
        ]);

        // Recalculate promo jika ada
        if ($this->formData['promo_id']) {
            $this->calculatePromoDiskon();
        }

        // Redirect ke list jika tidak ada layanan tersisa
        if (empty($this->selectedLayananIds)) {
            $this->warning('Pilih minimal 1 layanan', position: 'toast-top');
            $this->redirect(route('pesanan.pelanggan'), navigate: true);
        }
    }

    public function updatedFormDataPromoId(): void
    {
        $this->calculatePromoDiskon();
    }

    protected function calculatePromoDiskon(): void
    {
        $promoId = $this->formData['promo_id'];

        if (! $promoId) {
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => '',
                'tipe' => '',
            ];
            $this->cachedPromo = null;

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

            return;
        }

        // Untuk pelanggan, kita tidak bisa hitung diskon pasti karena belum ada berat
        // Jadi hanya validasi apakah promo valid untuk pelanggan ini
        $pelangganId = Auth::id();

        // Check apakah pelanggan di-exclude
        if ($pelangganId && PromoHelper::isPelangganExcluded($promo, $pelangganId)) {
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => 'Promo tidak berlaku untuk Anda',
                'tipe' => $promo->tipe_diskon,
            ];

            return;
        }

        // Check apakah pelanggan sudah mencapai limit max_per_user
        if ($pelangganId && ! PromoHelper::canUserUsePromo($promo, $pelangganId)) {
            $maxPerUser = $promo->max_per_user;
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => "Anda sudah menggunakan promo ini {$maxPerUser}x (maksimal {$maxPerUser}x per orang)",
                'tipe' => $promo->tipe_diskon,
            ];

            return;
        }

        // Check apakah promo masih valid (aktif, dalam periode, ada kuota)
        if (! PromoHelper::isValid($promo)) {
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => 'Promo tidak aktif atau sudah berakhir',
                'tipe' => $promo->tipe_diskon,
            ];

            return;
        }

        // Promo valid - tampilkan info promo
        $this->promoResult = [
            'valid' => true,
            'diskon' => 0, // Akan dihitung nanti saat staff input berat
            'pesan' => PromoHelper::formatNilaiDiskon($promo->tipe_diskon, $promo->nilai_diskon).' - '.$promo->nama_promo,
            'tipe' => $promo->tipe_diskon,
        ];
    }

    public function submit(): void
    {
        Log::info('Pesan: Submit started', [
            'pelanggan_id' => Auth::id(),
            'selectedLayananIds' => $this->selectedLayananIds,
            'formData' => $this->formData,
        ]);

        // Validasi minimal 1 layanan dipilih
        if (empty($this->selectedLayananIds)) {
            Log::warning('Pesan: No layanan selected', [
                'pelanggan_id' => Auth::id(),
            ]);

            $this->error('Pilih minimal 1 layanan!', position: 'toast-top');

            return;
        }

        // Validasi form data
        try {
            $this->validate([
                'formData.metode_pembayaran' => [
                    'required',
                    'in:'.implode(',', TransaksiHelper::getAllMetodePembayaran()),
                ],
                'formData.tipe_bayar' => [
                    'required',
                    'in:'.implode(',', TransaksiHelper::getAllTipeBayar()),
                ],
                'formData.promo_id' => 'nullable|exists:promo,id',
                'formData.catatan' => 'nullable|string|max:1000',
            ]);

            Log::info('Pesan: Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Pesan: Validation failed', [
                'pelanggan_id' => Auth::id(),
                'errors' => $e->errors(),
                'formData' => $this->formData,
            ]);

            throw $e;
        }

        try {
            DB::transaction(function () {
                $pelanggan = Auth::user();

                Log::info('Pesan: Starting transaction', [
                    'pelanggan_id' => $pelanggan->id,
                    'pelanggan_nama' => $pelanggan->nama,
                ]);

                // Generate kode transaksi
                $kodeTransaksi = TransaksiHelper::generateKodeTransaksi();

                Log::info('Pesan: Kode transaksi generated', [
                    'kode_transaksi' => $kodeTransaksi,
                ]);

                // Prepare transaksi data
                $transaksiData = [
                    'kode_transaksi' => $kodeTransaksi,
                    'tanggal_masuk' => now(),
                    'kasir_id' => null, // Dari pelanggan, bukan kasir
                    'pelanggan_id' => $pelanggan->id,
                    'nama_pelanggan' => $pelanggan->nama,
                    'metode_pembayaran' => $this->formData['metode_pembayaran'],
                    'tipe_bayar' => $this->formData['tipe_bayar'],
                    'status' => TransaksiHelper::STATUS_MENUNGGU,
                    'status_bayar' => TransaksiHelper::STATUS_BELUM_BAYAR,
                    'catatan' => $this->formData['catatan'],
                    'jumlah_layanan' => count($this->selectedLayananIds),
                    'total_berat' => 0, // Diisi staff nanti
                    'total_item' => 0, // Diisi staff nanti
                    'subtotal' => 0, // Diisi staff nanti
                    'total' => 0, // Diisi staff nanti
                ];

                Log::info('Pesan: Creating transaksi', [
                    'transaksi_data' => $transaksiData,
                ]);

                // Simpan transaksi
                $transaksi = Transaksi::create($transaksiData);

                Log::info('Pesan: Transaksi created successfully', [
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                ]);

                // Simpan layanan yang dipilih sebagai placeholder
                // Berat/jumlah akan diisi staff saat jemput
                foreach ($this->selectedLayananIds as $layananId) {
                    $layanan = Layanan::find($layananId);

                    if (! $layanan) {
                        Log::warning('Pesan: Layanan not found', [
                            'layanan_id' => $layananId,
                            'transaksi_id' => $transaksi->id,
                        ]);

                        continue;
                    }

                    $transaksiLayananData = [
                        'layanan_id' => $layanan->id,
                        'nama_layanan' => $layanan->nama_layanan,
                        'berat_kg' => null, // Diisi staff nanti
                        'harga_per_kg' => $layanan->harga_per_kg,
                        'jumlah_satuan' => null, // Diisi staff nanti
                        'harga_per_satuan' => $layanan->harga_per_satuan,
                        'jenis_pakaian' => null, // Diisi staff nanti
                        'subtotal' => 0, // Diisi staff nanti
                    ];

                    Log::info('Pesan: Creating transaksi_layanan', [
                        'transaksi_id' => $transaksi->id,
                        'layanan_id' => $layanan->id,
                        'data' => $transaksiLayananData,
                    ]);

                    $transaksi->transaksiLayanan()->create($transaksiLayananData);
                }

                Log::info('Pesan: All transaksi_layanan created', [
                    'transaksi_id' => $transaksi->id,
                    'count' => count($this->selectedLayananIds),
                ]);

                // Simpan promo jika valid
                if ($this->formData['promo_id'] && $this->promoResult['valid']) {
                    $promo = $this->cachedPromo ?? PromoHelper::getById((int) $this->formData['promo_id']);

                    if ($promo) {
                        $transaksiPromoData = [
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                            'nama_promo' => $promo->nama_promo,
                            'tipe_diskon' => $promo->tipe_diskon,
                            'nilai_diskon_persen' => $promo->nilai_diskon,
                            'nilai_diskon_nominal' => 0, // Akan dihitung staff nanti
                            'diskon_maksimal' => $promo->diskon_maksimal,
                            'gratis_kg' => $promo->gratis_kg,
                            'gratis_hari' => $promo->gratis_hari,
                            'diterapkan_ke' => $promo->diterapkan_ke ?? 'subtotal',
                            'layanan_id' => $promo->layanan_id,
                            'urutan_apply' => 1,
                        ];

                        Log::info('Pesan: Creating transaksi_promo', [
                            'transaksi_id' => $transaksi->id,
                            'promo_id' => $promo->id,
                            'data' => $transaksiPromoData,
                        ]);

                        // Simpan snapshot promo (diskon akan dihitung staff nanti)
                        $transaksi->transaksiPromo()->create($transaksiPromoData);

                        Log::info('Pesan: Promo saved (akan dihitung staff)', [
                            'transaksi_id' => $transaksi->id,
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                        ]);
                    } else {
                        Log::warning('Pesan: Promo not found despite validation', [
                            'transaksi_id' => $transaksi->id,
                            'promo_id' => $this->formData['promo_id'],
                        ]);
                    }
                } else {
                    if ($this->formData['promo_id'] && ! $this->promoResult['valid']) {
                        Log::warning('Pesan: Promo selected but not valid', [
                            'promo_id' => $this->formData['promo_id'],
                            'promo_result' => $this->promoResult,
                        ]);
                    }
                }

                Log::info('Pesan: Transaksi berhasil dibuat', [
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'pelanggan_id' => $pelanggan->id,
                    'jumlah_layanan' => count($this->selectedLayananIds),
                ]);
            });

            Log::info('Pesan: Transaction committed successfully');

            $this->success('Pesanan berhasil dibuat! Tim kami akan segera menghubungi Anda.', position: 'toast-top', timeout: 5000);

            // Redirect ke halaman riwayat atau home
            $this->redirect('/pelanggan/riwayat', navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exception to show validation errors
            throw $e;
        } catch (Exception $e) {
            Log::error('Pesan: Failed to create transaksi', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
                'selectedLayananIds' => $this->selectedLayananIds,
                'pelanggan_id' => Auth::id(),
            ]);

            $this->error('Gagal membuat pesanan. Silakan coba lagi atau hubungi CS kami.', timeout: 10000, position: 'toast-top');
        }
    }

    public function getMetodePembayaranOptions(): array
    {
        return TransaksiHelper::getMetodePembayaranOptions();
    }

    public function getTipeBayarOptions(): array
    {
        return TransaksiHelper::getTipeBayarOptions();
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.pesan', [
            'metodePembayaranOptions' => $this->getMetodePembayaranOptions(),
            'tipeBayarOptions' => $this->getTipeBayarOptions(),
        ]);
    }
}
