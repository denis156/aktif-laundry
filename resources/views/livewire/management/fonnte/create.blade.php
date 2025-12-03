<div>
    <x-header title="Tambah Device WhatsApp" separator progress-indicator>
        <x-slot:subtitle>
            Tambahkan device WhatsApp baru untuk integrasi Fonnte
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button label="Kembali" link="{{ route('fonnte.index') }}" wire:navigate responsive icon="o-arrow-left"
                class="btn-outline" />
        </x-slot:actions>
    </x-header>

    {{-- Form to add new device --}}
    <x-form wire:submit="save" no-separator>
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Device" subtitle="Data device WhatsApp" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-input label="Nama Device" wire:model="deviceName" placeholder="Contoh: WhatsApp Bisnis"
                    icon="o-device-phone-mobile" hint="Nama untuk mengidentifikasi device ini" required />

                <x-input label="Nomor WhatsApp" wire:model="devicePhone" placeholder="Contoh: 08123456789"
                    icon="o-phone" hint="Format: 08xxx, 628xxx, atau +628xxx" required />
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('fonnte.index') }}" wire:navigate />
            <x-button label="Simpan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
