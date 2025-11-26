<?php

declare(strict_types=1);

namespace App\Livewire\Management\Promo;

use App\Helper\Database\LayananHelper;
use App\Helper\Database\PelangganHelper;
use App\Helper\Database\PromoHelper;
use App\Models\Promo;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Tambah Promo')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;

    public array $formData = [
        'kode_promo' => '',
        'nama_promo' => '',
        'deskripsi' => '',
        'tipe_diskon' => 'persen',
        'nilai_diskon' => 0,
        'diskon_maksimal' => null,
        'min_transaksi' => null,
        'tanggal_mulai' => '',
        'tanggal_berakhir' => '',
        'kuota_total' => null,
        'max_per_user' => null,
        'berlaku_untuk' => 'semua',
        'status' => 'Aktif',
    ];

    // * Additional fields
    public array $layananIds = [];

    public array $excludePelangganIds = [];

    public string $termsConditions = '';

    public bool $autoApply = false;

    public $bannerImage;

    public ?float $minBerat = null;

    public ?float $maxBerat = null;

    public function mount(): void
    {
        $this->refreshKodePromo();
        $this->formData['tanggal_mulai'] = now()->format('Y-m-d');
        $this->formData['tanggal_berakhir'] = now()->addDays(30)->format('Y-m-d');
    }

    public function refreshKodePromo(): void
    {
        try {
            $this->formData['kode_promo'] = PromoHelper::generateKodePromo();
        } catch (Exception $e) {
            Log::error('Failed to generate kode promo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal generate kode promo. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function updatedFormDataTipeDiskon(mixed $value): void
    {
        // Reset diskon maksimal jika bukan persen
        if ($value !== PromoHelper::TIPE_PERSEN) {
            $this->formData['diskon_maksimal'] = null;
        }
    }

    public function save(): void
    {
        $tipeDiskonOptions = collect(PromoHelper::getTipeDiskonOptions())->pluck('id')->implode(',');

        $rules = [
            'formData.kode_promo' => 'required|string|max:50|unique:promo,kode_promo',
            'formData.nama_promo' => 'required|string|max:255',
            'formData.deskripsi' => 'nullable|string',
            'formData.tipe_diskon' => 'required|in:'.$tipeDiskonOptions,
            'formData.nilai_diskon' => 'required|integer|min:1',
            'formData.diskon_maksimal' => 'nullable|integer|min:1',
            'formData.min_transaksi' => 'nullable|integer|min:1',
            'formData.tanggal_mulai' => 'required|date',
            'formData.tanggal_berakhir' => 'required|date|after_or_equal:formData.tanggal_mulai',
            'formData.kuota_total' => 'nullable|integer|min:1',
            'formData.max_per_user' => 'nullable|integer|min:1',
            'formData.berlaku_untuk' => 'required|in:semua,pelanggan_baru,pelanggan_lama',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
            'layananIds' => 'nullable|array',
            'excludePelangganIds' => 'nullable|array',
            'termsConditions' => 'nullable|string',
            'bannerImage' => 'nullable|image|max:2048',
            'autoApply' => 'nullable|boolean',
            'minBerat' => 'nullable|numeric|min:0',
            'maxBerat' => 'nullable|numeric|min:0',
        ];

        $this->validate($rules);

        try {
            DB::transaction(function () {
                // Cek ulang apakah kode promo sudah ada, jika ya generate ulang
                if (Promo::where('kode_promo', $this->formData['kode_promo'])->exists()) {
                    $this->refreshKodePromo();
                }

                $promoData = $this->formData;
                $promoData['kuota_terpakai'] = 0;

                // Convert empty string to null
                $promoData['diskon_maksimal'] = $this->formData['diskon_maksimal'] ?: null;
                $promoData['min_transaksi'] = $this->formData['min_transaksi'] ?: null;
                $promoData['kuota_total'] = $this->formData['kuota_total'] ?: null;
                $promoData['max_per_user'] = $this->formData['max_per_user'] ?: null;

                // Create promo
                $promo = Promo::create($promoData);

                // Set JSON fields
                PromoHelper::setLayananIds($promo, $this->layananIds);
                PromoHelper::setExcludePelangganIds($promo, $this->excludePelangganIds);

                // Set other fields
                PromoHelper::setTermsConditions($promo, $this->termsConditions);
                PromoHelper::setAutoApply($promo, $this->autoApply);
                PromoHelper::setMinBerat($promo, $this->minBerat);
                PromoHelper::setMaxBerat($promo, $this->maxBerat);

                // Upload banner image
                if ($this->bannerImage) {
                    $bannerPath = $this->bannerImage->store('promo/banners', 'public');
                    PromoHelper::setBannerImage($promo, $bannerPath);
                }

                // Save all changes
                $promo->save();
            });

            $this->success('Promo berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/promo', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                Log::warning('Duplicate entry detected when creating promo', [
                    'kode_promo' => $this->formData['kode_promo'],
                    'error' => $e->getMessage(),
                ]);
                $this->refreshKodePromo();
                $this->warning('Kode promo di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return;
            }

            Log::error('Promo Create: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan promo. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Promo Create: Unexpected error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
            ]);

            $this->error('Gagal menyimpan promo. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.promo.create', [
            'tipeDiskonOptions' => PromoHelper::getTipeDiskonOptions(),
            'layananOptions' => LayananHelper::getLayananOptions(),
            'pelangganOptions' => PelangganHelper::getPelangganOptions(limit: 100),
        ]);
    }
}
