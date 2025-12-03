<div>
    <!-- HEADER -->
    <x-header title="Fonnte WhatsApp" icon="o-chat-bubble-left-right"
        icon-classes="bg-success text-success-content rounded-full p-1 w-8 h-8"
        subtitle="Kelola Device WhatsApp via Fonnte" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Tambah Device" link="{{ route('fonnte.create') }}" wire:navigate.hover responsive icon="o-plus"
                class="btn-success" />
            <x-button label="Refresh" wire:click="loadDevices" responsive icon="o-arrow-path" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2" title="Data Device WhatsApp"
        subtitle="Kelola koneksi WhatsApp melalui Fonnte API">
        <x-table :headers="$this->headers" :rows="$this->devices" striped
            link="{{ route('fonnte.edit', '[token]') }}">
            <x-slot:empty>
                <x-icon name="o-device-phone-mobile" label="Tidak ada device WhatsApp." />
            </x-slot:empty>

            @scope('cell_name', $device)
            @php
                $deviceArray = is_array($device) ? $device : (array) $device;
            @endphp
            <span class="font-semibold">{{ $deviceArray['name'] ?? 'Unnamed Device' }}</span>
            @endscope

            @scope('cell_device', $device)
            @php
                $deviceArray = is_array($device) ? $device : (array) $device;
                $phone = $deviceArray['device'] ?? '-';
                // Format phone number using PhoneNumber helper
                if ($phone !== '-') {
                    $phone = \App\Helper\PhoneNumber::formatInternational($phone) ?? $phone;
                }
            @endphp
            <span class="text-sm font-mono">{{ $phone }}</span>
            @endscope

            @scope('cell_status', $device)
            @php
                $deviceArray = is_array($device) ? $device : (array) $device;
                $status = $deviceArray['status'] ?? '';
            @endphp
            @if ($status === 'connect' || $status === 'connected')
                <x-badge value="Connected" class="badge-success badge-sm" />
            @elseif ($status === 'disconnect' || $status === 'disconnected')
                <x-badge value="Disconnected" class="badge-error badge-sm" />
            @else
                <x-badge value="{{ ucfirst($status) }}" class="badge-warning badge-sm" />
            @endif
            @endscope

            @scope('cell_expire', $device)
            @php
                $deviceArray = is_array($device) ? $device : (array) $device;
                $expired = $deviceArray['expired'] ?? $deviceArray['expire'] ?? null;
                // Format timestamp to readable date
                if ($expired && is_numeric($expired)) {
                    $expired = date('d M Y', (int) $expired);
                }
            @endphp
            <span class="text-sm">{{ $expired ?? '-' }}</span>
            @endscope

            @scope('cell_messages_sent', $device)
            @php
                $deviceArray = is_array($device) ? $device : (array) $device;
            @endphp
            <span class="text-sm">{{ $deviceArray['messages'] ?? $deviceArray['messages_sent'] ?? '0' }}</span>
            @endscope

            @scope('cell_quota', $device)
            @php
                $deviceArray = is_array($device) ? $device : (array) $device;
            @endphp
            <span class="text-sm">{{ $deviceArray['quota'] ?? '-' }}</span>
            @endscope

            @scope('actions', $device)
            @php
                $deviceArray = is_array($device) ? $device : (array) $device;
                $token = $deviceArray['token'] ?? '';
            @endphp
            <div class="flex items-center justify-end gap-2">
                <x-button label="Edit" icon="o-pencil" link="{{ route('fonnte.edit', $token) }}" wire:navigate.hover
                    class="btn-sm btn-info" />
            </div>
            @endscope
        </x-table>
    </x-card>
</div>
