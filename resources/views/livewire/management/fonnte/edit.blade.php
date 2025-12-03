<div>
    <x-header title="Kelola Device WhatsApp" separator progress-indicator>
        <x-slot:subtitle>
            Detail dan pengaturan device WhatsApp
        </x-slot:subtitle>
        <x-slot:actions>
            @if ($device)
            @php
            $deviceStatus = $device['device_status'] ?? $device['status'] ?? '';
            @endphp
            @if ($deviceStatus === 'connect' || $deviceStatus === 'connected')
            <x-button label="Disconnect" wire:click="confirmDisconnect" responsive icon="o-x-circle"
                class="btn-warning" />
            @endif
            @endif
            <x-button label="Kembali" link="{{ route('fonnte.index') }}" wire:navigate responsive icon="o-arrow-left"
                class="btn-outline" />
        </x-slot:actions>
    </x-header>

    @if ($device)
    {{-- Device Form --}}
    <div class="lg:grid grid-cols-5">
        <div class="col-span-2">
            <x-header title="Informasi Device" subtitle="Edit informasi device WhatsApp" size="text-lg" />
        </div>
        <x-card class="col-span-3">
            <div class="space-y-4">
                <x-input label="Nama Device" wire:model="name" placeholder="Masukkan nama device" icon="o-device-phone-mobile" />
                <x-input label="Nomor Device" wire:model="phoneNumber" placeholder="628123456789" icon="o-phone"
                    hint="Nomor device tidak dapat diubah" readonly />
            </div>

            <x-slot:actions>
                <x-button label="Simpan" wire:click="saveDevice" spinner="saveDevice" class="btn-primary" icon="o-check" />
            </x-slot:actions>
        </x-card>
    </div>

    {{-- Device Details --}}
    <div class="lg:grid grid-cols-5 mt-8">
        <div class="col-span-2">
            <x-header title="Detail Device" subtitle="Informasi lengkap device" size="text-lg" />
        </div>
        <x-card class="col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col">
                    <span class="text-sm text-base-content/70 font-medium">Status:</span>
                    @php
                    $status = $device['device_status'] ?? $device['status'] ?? '';
                    @endphp
                    @if ($status === 'connect' || $status === 'connected')
                    <x-badge value="Connected" class="badge-success badge-sm w-fit" />
                    @elseif ($status === 'disconnect' || $status === 'disconnected')
                    <x-badge value="Disconnected" class="badge-error badge-sm w-fit" />
                    @else
                    <x-badge value="{{ ucfirst($status) }}" class="badge-warning badge-sm w-fit" />
                    @endif
                </div>

                <div class="flex flex-col">
                    <span class="text-sm text-base-content/70 font-medium">Paket:</span>
                    <span class="text-base font-semibold">{{ $device['package'] ?? '-' }}</span>
                </div>

                <div class="flex flex-col">
                    <span class="text-sm text-base-content/70 font-medium">Expired:</span>
                    <span class="text-base font-semibold">{{ $device['expired'] ?? $device['expire'] ?? '-' }}</span>
                </div>

                <div class="flex flex-col">
                    <span class="text-sm text-base-content/70 font-medium">Quota:</span>
                    <span class="text-base font-semibold">{{ $device['quota'] ?? '-' }}</span>
                </div>

                <div class="flex flex-col">
                    <span class="text-sm text-base-content/70 font-medium">Messages Sent:</span>
                    <span class="text-base font-semibold">{{ $device['messages'] ?? $device['messages_sent'] ?? '0' }}</span>
                </div>

                <div class="flex flex-col">
                    <span class="text-sm text-base-content/70 font-medium">Token:</span>
                    <span class="text-xs font-mono break-all">{{ $device['token'] ?? $token }}</span>
                </div>
            </div>
        </x-card>
    </div>

    {{-- QR Code Section (only if disconnected) --}}
    @php
    $deviceStatus = $device['device_status'] ?? $device['status'] ?? '';
    @endphp
    @if ($deviceStatus === 'disconnect' || $deviceStatus === 'disconnected')
    <div class="lg:grid grid-cols-5 mt-8">
        <div class="col-span-2">
            <x-header title="Koneksi Device" subtitle="Hubungkan device dengan WhatsApp" size="text-lg" />
        </div>
        <div class="col-span-3">
            @if ($showQR && $qrCode)
            {{-- QR Code Display --}}
            <x-card body-class="flex justify-center">
                <img src="{{ $qrCode }}" alt="QR Code" class="w-[18dvw] h-auto">
                <x-slot:actions separator>
                    <div class="w-full flex justify-center">
                        <x-button label="Muat Ulang QR Code" wire:click="requestQR" icon="o-arrow-path"
                            class="btn-primary" spinner="requestQR" />
                    </div>
                </x-slot:actions>
            </x-card>
            @else
            {{-- Empty State --}}
            <x-card body-class="flex flex-col items-center space-y-6 py-8">
                <div class="bg-warning/10 p-6 rounded-full">
                    <x-icon name="o-qr-code" class="w-20 h-20 text-warning" />
                </div>

                <div class="text-center space-y-2">
                    <h3 class="text-lg font-semibold">Belum Ada QR Code</h3>
                    <p class="text-sm text-base-content/70 max-w-md">
                        Generate QR Code untuk menghubungkan device WhatsApp.
                    </p>
                </div>

                <x-slot:actions separator>
                    <div class="w-full flex justify-center">
                        <x-button label="Generate QR Code" wire:click="requestQR" icon="o-qr-code" class="btn-success"
                            spinner="requestQR" />
                    </div>
                </x-slot:actions>
            </x-card>
            @endif
        </div>
    </div>
    @endif

    {{-- DISCONNECT CONFIRMATION MODAL --}}
    <x-modal wire:model="disconnectModal" box-class="max-w-md" class="modal-bottom sm:modal-middle backdrop-blur-md">
        <div class="text-center space-y-2">
            <div class="flex justify-center">
                <x-icon name="o-exclamation-triangle"
                    class="w-18 h-18 p-4 bg-warning rounded-full text-warning-content" />
            </div>

            <div>
                <h3 class="text-lg font-bold text-warning">Konfirmasi Disconnect!</h3>
                <p class="text-sm text-base-content mt-2">Device akan terputus dari WhatsApp dan perlu scan QR Code
                    ulang untuk terhubung kembali.</p>
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin disconnect device <span
                        class="font-bold">{{ $device['name'] ?? 'Unnamed' }}</span>?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Disconnect" wire:click="disconnectDevice" spinner class="btn-warning btn-block"
                icon="o-x-circle" />
        </x-slot:actions>
    </x-modal>

    @else
    <x-card class="shadow-sm">
        <div class="flex flex-col items-center space-y-4 py-8">
            <x-loading class="loading-lg text-primary" />
            <p class="text-center text-base-content/70">Memuat data device...</p>
        </div>
    </x-card>
    @endif
</div>
