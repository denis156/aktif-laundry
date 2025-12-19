<div>
    {{-- HEADER --}}
    <x-header title="Tracking Kurir" icon="o-map-pin"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Pantau lokasi dan aktivitas kurir secara real-time"
        separator progress-indicator>
        <x-slot:actions>
            <x-button icon="o-arrow-path" label="Refresh" class="btn-secondary btn-outline"
                responsive />
            <x-button icon="o-funnel" label="Filters" class="btn-primary"
                responsive />
        </x-slot:actions>
    </x-header>

    {{-- MAIN CONTENT --}}
    <section class="space-y-6">
        {{-- MAP CARD --}}
        <x-card title="Peta Tracking Kurir"
            subtitle="Lihat posisi dan rute kurir yang sedang bertugas"
            class="shadow-sm">
            <x-slot:menu>
                <div class="flex items-center gap-2">
                    <x-badge value="0 Kurir" class="badge-primary badge-md" />
                </div>
            </x-slot:menu>

            {{-- Placeholder untuk map --}}
            <div class="w-full h-[70dvh] bg-base-200 rounded-lg flex items-center justify-center">
                <div class="text-center space-y-2">
                    <x-icon name="o-map" class="w-16 h-16 text-base-content/30 mx-auto" />
                    <p class="text-base-content/50">Map akan ditampilkan di sini</p>
                </div>
            </div>
        </x-card>
    </section>
</div>
