<nav class="navbar bg-primary text-primary-content sticky top-0 z-50">
    <div class="navbar-start">
        {{-- <x-dropdown no-x-anchor scroll max-height="max-h-64 text-warning-content">
            <x-slot:trigger>
                <div class="indicator">
                    <span class="indicator-item badge badge-warning badge-xs">0</span>
                    <x-button icon="iconpark.remind-o" class="btn-circle btn-md" />
                </div>
            </x-slot:trigger>

            Todo: Tampilan Notifikasi
            <x-menu-item title="Belum Ada Notifikasi terbaru" class="text-info text-xs" />
        </x-dropdown> --}}
    </div>
    <div class="navbar-center">
        <span class="text-md font-extrabold uppercase">{{ config('app.name') }}</span>
    </div>
    <div class="navbar-end">
        {{-- <x-button icon="iconpark.setting-o" class="btn-circle btn-md" link="{{ route('pengaturan.pelanggan') }}" /> --}}
    </div>
</nav>
