<div class="container mx-auto">
    <x-header title="Pesan" subtitle="Buat pesanan laundry baru dengan mudah" icon="iconpark.listview-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 mb-24">
        {{-- Content akan ditambahkan nanti --}}
        <x-card class="shadow-lg border border-primary"
            body-class="flex flex-col items-center justify-center py-12 space-y-4">
            <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center">
                <x-icon name="iconpark.listview-o" class="h-10 text-base-content/40" />
            </div>
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-base-content">Buat Pesanan Baru</h3>
                <p class="text-sm text-base-content/60 max-w-md">
                    Form pemesanan akan ditambahkan di sini
                </p>
            </div>
        </x-card>
    </div>
</div>
