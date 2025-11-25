<div class="container mx-auto">
    <div class="space-y-4 flex flex-col justify-center items-center mb-24">
        <x-card class="shadow-lg w-full border border-primary"
            body-class="h-18 flex flex-col justify-center items-center">
            <x-slot:figure class="flex justify-between h-28 border-b border-dashed">
                <div class="p-4 space-y-auto">
                    <h1 class="text-sm text-base-content/60 font-medium">Total Transaksi</h1>
                    <span class="text-5xl text-success font-bold">0</span>
                </div>
                <div class="p-4">
                    <x-button class="btn-primary btn-soft border border-primary rounded-lg h-18 w-14"
                        icon="iconpark.plus-o" />
                </div>
            </x-slot:figure>
            <div class="grid grid-cols-4 gap-8 w-full">
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-primary" icon="iconpark.washing-machine-one-o" />
                    <span class="text-xs text-base-content/60 font-light">Cuci</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-warning" icon="iconpark.delivery-o" />
                    <span class="text-xs text-base-content/60 font-light">Antar</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-info" icon="iconpark.history-o" />
                    <span class="text-xs text-base-content/60 font-light">Riwayat</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button class="btn-square btn-lg btn-success" icon="iconpark.setting-o" />
                    <span class="text-xs text-base-content/60 font-light">Akun</span>
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
                    <div class="stat-value text-sm text-success">0</div>
                </div>
            </div>
            <div class="stats shadow-lg border border-primary">
                <div class="stat">
                    <div class="stat-title text-xs">Proses</div>
                    <div class="stat-value text-sm text-warning">0</div>
                </div>
            </div>
            <div class="stats shadow-lg border border-primary">
                <div class="stat">
                    <div class="stat-title">Batal</div>
                    <div class="stat-value text-sm text-error">0</div>
                </div>
            </div>
        </div>

        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/60">Pesanan Terbaru</h1>
                <x-button label="Lihat Semua" class="btn-ghost btn-xs btn-primary"/>
            </div>
            <div class="text-center py-8">
                <x-icon name="iconpark.empty-picture-o" class="w-16 h-16 mx-auto text-base-content/40" />
                <p class="text-sm text-base-content/60 mt-2">Belum ada pesanan</p>
            </div>
        </div>
    </div>
</div>
