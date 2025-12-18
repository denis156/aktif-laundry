<x-card class="bg-base-200 w-full h-[70dvh] mb-24"
    body-class="flex flex-col items-center justify-center py-12 text-center">
    <x-icon name="iconpark.closewifi-o" class="w-32 h-32 text-primary mb-6" />

    <h3 class="text-xl font-bold text-base-content mb-2">Tidak Ada Koneksi Internet</h3>

    <p class="text-base-content/70 max-w-md">
        Periksa koneksi internet Anda dan coba lagi. Pastikan WiFi atau data seluler Anda aktif.
    </p>

    <x-slot:actions separator>
        <x-button label="Coba Lagi" class="btn-primary btn-block" />
    </x-slot:actions>
</x-card>
