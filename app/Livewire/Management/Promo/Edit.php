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
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Edit Promo')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;
    use WithFileUploads;

    public int $promoId;

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
        'kuota_terpakai' => 0,
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

    public ?string $currentBannerUrl = null;

    public ?float $minBerat = null;

    public ?float $maxBerat = null;

    public function mount(int $id): void
    {
        $this->promoId = $id;
        $this->loadPromo();
    }

    protected function loadPromo(): void
    {
        try {
            $promo = Promo::findOrFail($this->promoId);

            $this->formData = [
                'kode_promo' => $promo->kode_promo,
                'nama_promo' => $promo->nama_promo,
                'deskripsi' => $promo->deskripsi ?? '',
                'tipe_diskon' => $promo->tipe_diskon,
                'nilai_diskon' => $promo->nilai_diskon,
                'diskon_maksimal' => $promo->diskon_maksimal,
                'min_transaksi' => $promo->min_transaksi,
                'tanggal_mulai' => $promo->tanggal_mulai->format('Y-m-d'),
                'tanggal_berakhir' => $promo->tanggal_berakhir->format('Y-m-d'),
                'kuota_total' => $promo->kuota_total,
                'kuota_terpakai' => $promo->kuota_terpakai,
                'max_per_user' => $promo->max_per_user,
                'berlaku_untuk' => $promo->berlaku_untuk,
                'status' => $promo->status,
            ];

            // Load additional fields
            $this->layananIds = PromoHelper::getLayananIds($promo);
            $this->excludePelangganIds = PromoHelper::getExcludePelangganIds($promo);
            $this->termsConditions = PromoHelper::getTermsConditions($promo) ?? '';
            $this->autoApply = PromoHelper::isAutoApply($promo);
            $this->minBerat = PromoHelper::getMinBerat($promo);
            $this->maxBerat = PromoHelper::getMaxBerat($promo);

            // Load current banner URL
            $bannerPath = PromoHelper::getBannerImage($promo);
            $this->currentBannerUrl = $bannerPath ? Storage::url($bannerPath) : null;
        } catch (Exception $e) {
            Log::error('Promo Edit: Failed to load promo', [
                'promo_id' => $this->promoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Promo tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/promo', navigate: true);
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
            'formData.kode_promo' => 'required|string|max:50|unique:promo,kode_promo,'.$this->promoId,
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
            'formData.status' => 'required|in:Aktif,Tidak Aktif,Habis',
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
                $promo = Promo::findOrFail($this->promoId);

                $promoData = $this->formData;

                // Convert empty string to null
                $promoData['diskon_maksimal'] = $this->formData['diskon_maksimal'] ?: null;
                $promoData['min_transaksi'] = $this->formData['min_transaksi'] ?: null;
                $promoData['kuota_total'] = $this->formData['kuota_total'] ?: null;
                $promoData['max_per_user'] = $this->formData['max_per_user'] ?: null;

                // Jangan ubah kuota_terpakai
                unset($promoData['kuota_terpakai']);

                $promo->update($promoData);

                // Update JSON fields
                PromoHelper::setLayananIds($promo, $this->layananIds);
                PromoHelper::setExcludePelangganIds($promo, $this->excludePelangganIds);

                // Update other fields
                PromoHelper::setTermsConditions($promo, $this->termsConditions);
                PromoHelper::setAutoApply($promo, $this->autoApply);
                PromoHelper::setMinBerat($promo, $this->minBerat);
                PromoHelper::setMaxBerat($promo, $this->maxBerat);

                // Upload banner image baru jika ada
                if ($this->bannerImage) {
                    $bannerPath = $this->bannerImage->store('promo/banners', 'public');
                    PromoHelper::setBannerImage($promo, $bannerPath);
                }

                // Save all changes
                $promo->save();
            });

            $this->success('Promo berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/promo', navigate: true);
        } catch (QueryException $e) {
            Log::error('Promo Edit: Database error', [
                'promo_id' => $this->promoId,
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan promo. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Promo Edit: Unexpected error', [
                'promo_id' => $this->promoId,
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
        return view('livewire.management.promo.edit', [
            'tipeDiskonOptions' => PromoHelper::getTipeDiskonOptions(),
            'layananOptions' => LayananHelper::getLayananOptions(),
            'pelangganOptions' => PelangganHelper::getPelangganOptions(limit: 100),
        ]);
    }
}
