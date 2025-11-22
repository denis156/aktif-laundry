<div>
    <!-- HEADER -->
    <x-header title="Edit Promo" icon="o-tag"
        icon-classes="bg-info text-info-content rounded-full p-1 w-8 h-8" subtitle="Ubah Data Promo" separator
        progress-indicator>
        <x-slot:actions>
            <x-button label="Kembali" link="{{ route('promo.index') }}" wire:navigate.hover responsive icon="o-arrow-left"
                class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Dasar -->
                <x-card title="Informasi Dasar" subtitle="Detail promo" class="shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input label="Kode Promo" wire:model="formData.kode_promo" icon="o-hashtag" inline
                            hint="Contoh: NEWUSER10, WEEKEND20" />

                        <x-input label="Nama Promo" wire:model="formData.nama_promo" icon="o-tag" inline />
                    </div>

                    <x-textarea label="Deskripsi" wire:model="formData.deskripsi" icon="o-document-text" rows="3"
                        placeholder="Deskripsi promo (opsional)" inline />
                </x-card>

                <!-- Pengaturan Diskon -->
                <x-card title="Pengaturan Diskon" subtitle="Tipe dan nilai diskon" class="shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select label="Tipe Diskon" wire:model.live="formData.tipe_diskon" icon="o-calculator" inline
                            :options="[
                                ['id' => 'persen', 'name' => 'Persen (%)'],
                                ['id' => 'nominal', 'name' => 'Nominal (Rp)'],
                            ]" option-value="id" option-label="name" />

                        <x-input label="Nilai Diskon" wire:model="formData.nilai_diskon" type="number" icon="o-currency-dollar"
                            inline :suffix="$formData['tipe_diskon'] === 'persen' ? '%' : 'Rp'" min="1" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if ($formData['tipe_diskon'] === 'persen')
                            <x-input label="Diskon Maksimal (Opsional)" wire:model="formData.diskon_maksimal" type="number"
                                icon="o-banknotes" inline prefix="Rp" min="1"
                                hint="Batas maksimal diskon dalam rupiah" />
                        @endif

                        <x-input label="Minimum Transaksi (Opsional)" wire:model="formData.min_transaksi" type="number"
                            icon="o-receipt-percent" inline prefix="Rp" min="1"
                            hint="Minimum pembelian untuk gunakan promo" />
                    </div>
                </x-card>

                <!-- Periode & Kuota -->
                <x-card title="Periode & Kuota" subtitle="Waktu aktif dan batasan penggunaan" class="shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input label="Tanggal Mulai" wire:model="formData.tanggal_mulai" type="date" icon="o-calendar"
                            inline />

                        <x-input label="Tanggal Berakhir" wire:model="formData.tanggal_berakhir" type="date"
                            icon="o-calendar-days" inline />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input label="Kuota Total (Opsional)" wire:model="formData.kuota_total" type="number"
                            icon="o-ticket" inline min="1" hint="Kosongkan untuk unlimited" />

                        <x-input label="Max per User (Opsional)" wire:model="formData.max_per_user" type="number"
                            icon="o-user-group" inline min="1" hint="Berapa kali user bisa pakai" />
                    </div>

                    <!-- Kuota Terpakai (Read-only) -->
                    @if($formData['kuota_total'])
                        <div class="alert alert-info">
                            <x-icon name="o-information-circle" class="w-6 h-6" />
                            <div>
                                <div class="font-semibold">Kuota Terpakai: {{ $formData['kuota_terpakai'] }}/{{ $formData['kuota_total'] }}</div>
                                <div class="text-sm">Sisa: {{ $formData['kuota_total'] - $formData['kuota_terpakai'] }} penggunaan</div>
                            </div>
                        </div>
                    @else
                        <div class="alert">
                            <x-icon name="o-information-circle" class="w-6 h-6" />
                            <div>
                                <div class="font-semibold">Kuota Unlimited</div>
                                <div class="text-sm">Total penggunaan: {{ $formData['kuota_terpakai'] }}x</div>
                            </div>
                        </div>
                    @endif
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status & Berlaku Untuk -->
                <x-card title="Pengaturan" class="shadow-sm">
                    <x-select label="Status" wire:model="formData.status" icon="o-signal" inline :options="[
                        ['id' => 'Aktif', 'name' => 'Aktif'],
                        ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif'],
                        ['id' => 'Habis', 'name' => 'Habis'],
                    ]" option-value="id" option-label="name" />

                    <x-select label="Berlaku Untuk" wire:model="formData.berlaku_untuk" icon="o-users" inline :options="[
                        ['id' => 'semua', 'name' => 'Semua Pelanggan'],
                        ['id' => 'pelanggan_baru', 'name' => 'Pelanggan Baru Saja'],
                        ['id' => 'layanan_tertentu', 'name' => 'Layanan Tertentu'],
                    ]" option-value="id" option-label="name" />
                </x-card>

                <!-- Preview -->
                <x-card title="Preview Promo" class="shadow-sm bg-base-200">
                    <div class="space-y-3 text-sm">
                        <div>
                            <div class="font-semibold text-base-content/60">Kode:</div>
                            <div class="font-mono font-bold text-primary text-lg">
                                {{ $formData['kode_promo'] ?: '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="font-semibold text-base-content/60">Nama:</div>
                            <div class="font-medium">{{ $formData['nama_promo'] ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="font-semibold text-base-content/60">Diskon:</div>
                            <div class="font-bold text-success text-xl">
                                @if ($formData['tipe_diskon'] === 'persen')
                                    {{ $formData['nilai_diskon'] }}%
                                @else
                                    Rp {{ number_format($formData['nilai_diskon'], 0, ',', '.') }}
                                @endif
                            </div>
                        </div>

                        @if ($formData['diskon_maksimal'])
                            <div>
                                <div class="font-semibold text-base-content/60">Max Diskon:</div>
                                <div>Rp {{ number_format($formData['diskon_maksimal'], 0, ',', '.') }}</div>
                            </div>
                        @endif

                        @if ($formData['min_transaksi'])
                            <div>
                                <div class="font-semibold text-base-content/60">Min Transaksi:</div>
                                <div>Rp {{ number_format($formData['min_transaksi'], 0, ',', '.') }}</div>
                            </div>
                        @endif

                        <div>
                            <div class="font-semibold text-base-content/60">Periode:</div>
                            <div class="text-xs">
                                {{ $formData['tanggal_mulai'] ? \Carbon\Carbon::parse($formData['tanggal_mulai'])->format('d M Y') : '-' }}
                                <br>s/d<br>
                                {{ $formData['tanggal_berakhir'] ? \Carbon\Carbon::parse($formData['tanggal_berakhir'])->format('d M Y') : '-' }}
                            </div>
                        </div>

                        @if ($formData['kuota_total'])
                            <div>
                                <div class="font-semibold text-base-content/60">Kuota:</div>
                                <div>{{ $formData['kuota_terpakai'] }}/{{ number_format($formData['kuota_total'], 0, ',', '.') }}</div>
                                <progress class="progress progress-primary w-full mt-1"
                                    value="{{ $formData['kuota_terpakai'] }}"
                                    max="{{ $formData['kuota_total'] }}">
                                </progress>
                            </div>
                        @endif

                        <div>
                            <div class="font-semibold text-base-content/60">Status:</div>
                            <div>
                                @if ($formData['status'] == 'Aktif')
                                    <x-badge value="{{ $formData['status'] }}" class="badge-success" />
                                @elseif ($formData['status'] == 'Habis')
                                    <x-badge value="{{ $formData['status'] }}" class="badge-warning" />
                                @else
                                    <x-badge value="{{ $formData['status'] }}" class="badge-error" />
                                @endif
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <x-button label="Simpan Perubahan" type="submit" spinner icon="o-check-circle" class="btn-info btn-block" />
                    <x-button label="Batal" link="{{ route('promo.index') }}" wire:navigate icon="o-x-circle"
                        class="btn-outline btn-block" />
                </div>
            </div>
        </div>
    </form>
</div>
