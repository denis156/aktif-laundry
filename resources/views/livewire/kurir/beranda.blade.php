<div class="container mx-auto">
    <div class="space-y-4 flex flex-col justify-center items-center mb-24">
        <x-card class="shadow-lg w-full border border-primary"
            body-class="h-18 flex flex-col justify-center items-center">
            <x-slot:figure class="flex justify-between h-28 border-b border-dashed">
                <div class="p-4 space-y-auto">
                    <h1 class="text-sm text-base-content/60 font-medium">Total Pengiriman</h1>
                    <span class="text-5xl text-success font-bold">10k</span>
                </div>
                <div class="p-4">
                    <x-button class="btn-primary btn-soft border border-primary rounded-lg h-18 w-14"
                        icon="iconpark.plus-o" />
                </div>
            </x-slot:figure>
            <div class="grid grid-cols-4 gap-8 w-full">
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-error" icon="iconpark.localpin-o" />
                    <span class="text-xs text-base-content/60 font-light">Jemput</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-warning" icon="iconpark.delivery-o" />
                    <span class="text-xs text-base-content/60 font-light">Antar</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-info" icon="iconpark.info-o" />
                    <span class="text-xs text-base-content/60 font-light">Info</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-success" icon="iconpark.maproadtwo-o" />
                    <span class="text-xs text-base-content/60 font-light">Rute</span>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-2 gap-2 w-full z-5">
            <div class="stats shadow-lg border border-primary col-span-full">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <x-icon name="iconpark.checkone-o" class="inline-block h-8 w-8 text-success" />
                    </div>
                    <div class="stat-title text-xs">Pesanan Selesai</div>
                    <div class="stat-value text-sm text-success">89,400</div>
                </div>
            </div>
            <div class="stats shadow-lg border border-primary">
                <div class="stat">
                    <div class="stat-title text-xs">Proses</div>
                    <div class="stat-value text-sm text-warning">89,400</div>
                </div>
            </div>
            <div class="stats shadow-lg border border-primary">
                <div class="stat">
                    <div class="stat-title">Batal</div>
                    <div class="stat-value text-sm text-error">89,400</div>
                </div>
            </div>
        </div>

        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/60">Pengiriman Terbaru</h1>
                <x-button label="Lihat Semua" class="btn-ghost btn-xs btn-primary"/>
            </div>
            <x-card class="shadow-lg border border-primary" body-class="flex items-center gap-3">
                <x-avatar image="https://img.daisyui.com/images/profile/demo/yellingwoman@192.webp"
                    class="w-14 rounded-full avatar-online ring-neutral ring-offset-success ring-2 ring-offset-2" />
                <div class="flex flex-col flex-1 min-w-0">
                    <h1 class="text-sm font-normal text-base-content">Mbak Ngambek</h1>
                    <p class="text-xs font-thin text-base-content/60 truncate">Jl. Sudirman No. 123, Jakarta</p>
                </div>
                <x-badge value="Antar" class="badge-error badge-sm shrink-0" />
            </x-card>
            <x-card class="shadow-lg border border-primary" body-class="flex items-center gap-3">
                <x-avatar image="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
                    class="w-14 rounded-full avatar-online ring-neutral ring-offset-success ring-2 ring-offset-2" />
                <div class="flex flex-col flex-1 min-w-0">
                    <h1 class="text-sm font-normal text-base-content">Koecing Pant*k</h1>
                    <p class="text-xs font-thin text-base-content/60 truncate">Jl. Sudirman No. 123, Jakarta</p>
                </div>
                <x-badge value="Proses" class="badge-warning badge-sm shrink-0" />
            </x-card>
        </div>

        <div>

        </div>
    </div>
</div>
