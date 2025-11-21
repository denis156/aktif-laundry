<div>
    <x-header title="Edit Pelanggan" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi pelanggan
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Data identitas pelanggan" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-input
                    label="Kode Pelanggan"
                    wire:model="formData.kode_pelanggan"
                    readonly
                    hint="Kode tidak dapat diubah"
                    icon="o-hashtag"
                />

                <x-input
                    label="Nama Pelanggan"
                    wire:model="formData.nama"
                    placeholder="Contoh: Ahmad Rizki"
                    icon="o-user"
                    required
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="No. HP"
                        wire:model="formData.no_hp"
                        placeholder="Contoh: 08123456789"
                        icon="o-phone"
                        required
                    />

                    <x-input
                        label="Email"
                        type="email"
                        wire:model="formData.email"
                        placeholder="Contoh: pelanggan@email.com"
                        icon="o-envelope"
                        hint="Opsional"
                    />
                </div>

                <x-textarea
                    label="Alamat"
                    wire:model="formData.alamat"
                    placeholder="Masukkan alamat lengkap pelanggan..."
                    rows="3"
                    required
                />
            </x-card>
        </div>

        {{-- Status & Tanggal section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Status & Tanggal" subtitle="Status dan tanggal registrasi" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime
                        label="Tanggal Daftar"
                        type="datetime-local"
                        wire:model="formData.tanggal_daftar"
                        icon="o-calendar"
                        required
                    />

                    <x-select
                        label="Status"
                        wire:model="formData.status"
                        icon="o-check-circle"
                        :options="[
                            ['id' => 'Aktif', 'name' => 'Aktif'],
                            ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif']
                        ]"
                        option-value="id"
                        option-label="name"
                        required
                    />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('pelanggan.index') }}" wire:navigate />
            <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
