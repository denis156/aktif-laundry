<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Helper\Database\PromoHelper;
use App\Helper\Database\TransaksiHelper;
use App\Models\Layanan;
use App\Models\Transaksi;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Edit Pesanan')]
#[Layout('layouts.pelanggan.app')]
class EditPesanan extends Component
{
    use Toast;

    public Transaksi $transaksi;

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

    // Modal state
    public bool $confirmDeleteModal = false;

    public function mount(int $id): void
    {
        $pelanggan = Auth::user();

        // Load transaksi dengan eager loading
        $this->transaksi = Transaksi::with([
            'transaksiLayanan.layanan',
            'transaksiPromo.promo',
        ])
            ->where('id', $id)
            ->where('pelanggan_id', $pelanggan->id)
            ->firstOrFail();

        // Check if transaksi status is 'Menunggu'
        if ($this->transaksi->status !== TransaksiHelper::STATUS_MENUNGGU) {
            Log::warning('EditPesanan: Cannot edit transaksi with status other than Menunggu', [
                'transaksi_id' => $this->transaksi->id,
                'status' => $this->transaksi->status,
            ]);

            $this->error('Pesanan hanya bisa diedit jika masih berstatus Menunggu', position: 'toast-top', timeout: 5000);
            $this->redirect(route('detail-pesanan.pelanggan', ['id' => $this->transaksi->id]), navigate: true);

            return;
        }

        // Load layanan options
        $this->loadOptions();

        // Populate form with existing data
        $this->populateFormData();

        // Load from session if coming back from list layanan
        if (session()->has('selected_layanan_ids')) {
            $sessionLayananIds = session('selected_layanan_ids', []);

            // Merge dengan yang sudah ada (jika ada layanan baru dipilih)
            $this->selectedLayananIds = array_unique(array_merge($this->selectedLayananIds, $sessionLayananIds));

            Log::info('EditPesanan: Merged selected layanan from session', [
                'transaksi_id' => $this->transaksi->id,
                'selected_ids' => $this->selectedLayananIds,
            ]);

            // Clear session
            session()->forget('selected_layanan_ids');
        }

        // Always clear edit_transaksi_id when entering edit page
        // This prevents accidental redirect loops
        session()->forget('edit_transaksi_id');
    }

    protected function populateFormData(): void
    {
        // Populate selected layanan IDs from transaksi_layanan
        $this->selectedLayananIds = $this->transaksi->transaksiLayanan
            ->pluck('layanan_id')
            ->toArray();

        // Populate form data
        $this->formData['metode_pembayaran'] = $this->transaksi->metode_pembayaran;
        $this->formData['tipe_bayar'] = $this->transaksi->tipe_bayar;
        $this->formData['catatan'] = $this->transaksi->catatan ?? '';

        // Populate promo if exists
        $existingPromo = $this->transaksi->transaksiPromo->first();
        if ($existingPromo) {
            $this->formData['promo_id'] = $existingPromo->promo_id;
            $this->calculatePromoDiskon();
        }

        Log::info('EditPesanan: Form populated', [
            'transaksi_id' => $this->transaksi->id,
            'selected_layanan_ids' => $this->selectedLayananIds,
            'form_data' => $this->formData,
        ]);
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
            Log::error('EditPesanan: Failed to load options', [
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

        Log::info('EditPesanan: Layanan toggled', [
            'transaksi_id' => $this->transaksi->id,
            'layanan_id' => $layananId,
            'selected_ids' => $this->selectedLayananIds,
        ]);

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

        Log::info('EditPesanan: Layanan removed', [
            'transaksi_id' => $this->transaksi->id,
            'layanan_id' => $layananId,
            'remaining_ids' => $this->selectedLayananIds,
        ]);

        // Recalculate promo jika ada
        if ($this->formData['promo_id']) {
            $this->calculatePromoDiskon();
        }

        // Show warning jika tidak ada layanan tersisa
        if (empty($this->selectedLayananIds)) {
            $this->warning('Pilih minimal 1 layanan', position: 'toast-top');
        }
    }

    public function isSelected(int $layananId): bool
    {
        return in_array($layananId, $this->selectedLayananIds, true);
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
        // Saat edit, kita perlu exclude transaksi ini dari penghitungan
        if ($pelangganId && ! PromoHelper::canUserUsePromo($promo, $pelangganId, $this->transaksi->id)) {
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
        Log::info('EditPesanan: Submit started', [
            'transaksi_id' => $this->transaksi->id,
            'pelanggan_id' => Auth::id(),
            'selectedLayananIds' => $this->selectedLayananIds,
            'formData' => $this->formData,
        ]);

        // Validasi minimal 1 layanan dipilih
        if (empty($this->selectedLayananIds)) {
            Log::warning('EditPesanan: No layanan selected', [
                'transaksi_id' => $this->transaksi->id,
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

            Log::info('EditPesanan: Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('EditPesanan: Validation failed', [
                'transaksi_id' => $this->transaksi->id,
                'errors' => $e->errors(),
                'formData' => $this->formData,
            ]);

            throw $e;
        }

        try {
            DB::transaction(function () {
                Log::info('EditPesanan: Starting transaction', [
                    'transaksi_id' => $this->transaksi->id,
                ]);

                // Update transaksi data
                $this->transaksi->update([
                    'metode_pembayaran' => $this->formData['metode_pembayaran'],
                    'tipe_bayar' => $this->formData['tipe_bayar'],
                    'catatan' => $this->formData['catatan'],
                    'jumlah_layanan' => count($this->selectedLayananIds),
                ]);

                Log::info('EditPesanan: Transaksi updated', [
                    'transaksi_id' => $this->transaksi->id,
                ]);

                // Delete existing layanan
                $this->transaksi->transaksiLayanan()->delete();

                Log::info('EditPesanan: Existing transaksi_layanan deleted');

                // Create new layanan entries
                foreach ($this->selectedLayananIds as $layananId) {
                    $layanan = Layanan::find($layananId);

                    if (! $layanan) {
                        Log::warning('EditPesanan: Layanan not found', [
                            'layanan_id' => $layananId,
                            'transaksi_id' => $this->transaksi->id,
                        ]);

                        continue;
                    }

                    $transaksiLayananData = [
                        'layanan_id' => $layanan->id,
                        'nama_layanan' => $layanan->nama_layanan,
                        'berat_kg' => null,
                        'harga_per_kg' => $layanan->harga_per_kg,
                        'jumlah_satuan' => null,
                        'harga_per_satuan' => $layanan->harga_per_satuan,
                        'jenis_pakaian' => null,
                        'subtotal' => 0,
                    ];

                    Log::info('EditPesanan: Creating transaksi_layanan', [
                        'transaksi_id' => $this->transaksi->id,
                        'layanan_id' => $layanan->id,
                        'data' => $transaksiLayananData,
                    ]);

                    $this->transaksi->transaksiLayanan()->create($transaksiLayananData);
                }

                Log::info('EditPesanan: All transaksi_layanan created', [
                    'transaksi_id' => $this->transaksi->id,
                    'count' => count($this->selectedLayananIds),
                ]);

                // Delete existing promo
                $this->transaksi->transaksiPromo()->delete();

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
                            'nilai_diskon_nominal' => 0,
                            'diskon_maksimal' => $promo->diskon_maksimal,
                            'gratis_kg' => $promo->gratis_kg,
                            'gratis_hari' => $promo->gratis_hari,
                            'diterapkan_ke' => $promo->diterapkan_ke ?? 'subtotal',
                            'layanan_id' => $promo->layanan_id,
                            'urutan_apply' => 1,
                        ];

                        Log::info('EditPesanan: Creating transaksi_promo', [
                            'transaksi_id' => $this->transaksi->id,
                            'promo_id' => $promo->id,
                            'data' => $transaksiPromoData,
                        ]);

                        $this->transaksi->transaksiPromo()->create($transaksiPromoData);

                        Log::info('EditPesanan: Promo saved', [
                            'transaksi_id' => $this->transaksi->id,
                            'promo_id' => $promo->id,
                        ]);
                    }
                }

                Log::info('EditPesanan: Transaksi berhasil diupdate', [
                    'transaksi_id' => $this->transaksi->id,
                    'jumlah_layanan' => count($this->selectedLayananIds),
                ]);
            });

            Log::info('EditPesanan: Transaction committed successfully');

            $this->success('Pesanan berhasil diperbarui!', position: 'toast-top', timeout: 5000);

            // Redirect ke detail pesanan
            $this->redirect(route('detail-pesanan.pelanggan', ['id' => $this->transaksi->id]), navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exception to show validation errors
            throw $e;
        } catch (Exception $e) {
            Log::error('EditPesanan: Failed to update transaksi', [
                'transaksi_id' => $this->transaksi->id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
                'selectedLayananIds' => $this->selectedLayananIds,
            ]);

            $this->error('Gagal memperbarui pesanan. Silakan coba lagi.', timeout: 10000, position: 'toast-top');
        }
    }

    public function tambahLayanan(): void
    {
        // Store current selected layanan in session
        session(['selected_layanan_ids' => $this->selectedLayananIds]);

        // Store transaksi ID to return to edit page
        session(['edit_transaksi_id' => $this->transaksi->id]);

        Log::info('EditPesanan: Redirecting to list layanan', [
            'transaksi_id' => $this->transaksi->id,
            'current_selected_ids' => $this->selectedLayananIds,
        ]);

        // Redirect to list layanan
        $this->redirect(route('pesanan.pelanggan'), navigate: true);
    }

    public function hapusPesanan(): void
    {
        Log::info('EditPesanan: Delete pesanan started', [
            'transaksi_id' => $this->transaksi->id,
            'status' => $this->transaksi->status,
        ]);

        // Only allow delete if status is 'Menunggu'
        if ($this->transaksi->status !== TransaksiHelper::STATUS_MENUNGGU) {
            Log::warning('EditPesanan: Cannot delete transaksi with status other than Menunggu', [
                'transaksi_id' => $this->transaksi->id,
                'status' => $this->transaksi->status,
            ]);

            $this->confirmDeleteModal = false;
            $this->error('Pesanan hanya bisa dihapus jika masih berstatus Menunggu', position: 'toast-top', timeout: 5000);

            return;
        }

        try {
            DB::transaction(function () {
                // Delete related records
                $this->transaksi->transaksiLayanan()->delete();
                $this->transaksi->transaksiPromo()->delete();

                // Delete transaksi
                $this->transaksi->delete();

                Log::info('EditPesanan: Transaksi deleted successfully', [
                    'transaksi_id' => $this->transaksi->id,
                ]);
            });

            $this->confirmDeleteModal = false;
            $this->success('Pesanan berhasil dihapus!', position: 'toast-top', timeout: 3000);

            // Redirect to riwayat
            $this->redirect(route('riwayat.pelanggan'), navigate: true);
        } catch (Exception $e) {
            Log::error('EditPesanan: Failed to delete transaksi', [
                'transaksi_id' => $this->transaksi->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->confirmDeleteModal = false;
            $this->error('Gagal menghapus pesanan. Silakan coba lagi.', timeout: 10000, position: 'toast-top');
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
        return view('livewire.pelanggan.edit-pesanan', [
            'metodePembayaranOptions' => $this->getMetodePembayaranOptions(),
            'tipeBayarOptions' => $this->getTipeBayarOptions(),
        ]);
    }
}
