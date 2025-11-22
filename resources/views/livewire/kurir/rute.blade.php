<div class="container mx-auto">
    <x-header title="Rute" subtitle="Lihat dan kelola rute pengiriman untuk pesanan" icon="iconpark.maproadtwo-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 mb-24">
        {{-- Maps Lokasi --}}
        <x-card title="Lokasi Anda Saat Ini" subtitle="Pantau posisi real-time" class="shadow-lg border border-primary"
            body-class="overflow-hidden h-[48dvh]">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d248.7586179710606!2d122.51738610480511!3d-3.9920530759842343!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sid!2sid!4v1763769555188!5m2!1sid!2sid"
                class="w-full h-full" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </x-card>

        {{-- Info Tidak Ada Pesanan --}}
        <x-card class="shadow-lg border border-primary"
            body-class="flex flex-col items-center justify-center py-12 space-y-4">
            <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center">
                <x-icon name="iconpark.maproadtwo-o" class="h-10 text-base-content/40" />
            </div>
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-base-content">Belum Ada Pesanan Aktif</h3>
                <p class="text-sm text-base-content/60 max-w-md">
                    Rute pengiriman akan muncul saat ada pesanan baru
                </p>
            </div>
            <x-button label="Refresh" icon="iconpark.refresh-o" class="btn-primary btn-sm" />
        </x-card>
    </div>
</div>
