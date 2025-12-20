<div x-data="{
    isPwaInstalled: false,
    isWebView: false,
    checkPwaInstalled() {
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            this.isPwaInstalled = true;
            return;
        }
        if (localStorage.getItem('pwa-installed-kurir') === 'true') {
            this.isPwaInstalled = true;
        }
    },
    checkWebView() {
        const detection = window.browserHelper.detect();
        this.isWebView = detection.isWebView;
    }
}" x-init="checkPwaInstalled(); checkWebView()" x-on:pwa-installed-kurir.window="isPwaInstalled = true; localStorage.setItem('pwa-installed-kurir', 'true')">

    {{-- FAB: Hide after PWA installed OR if in WebView --}}
    <div class="fab mb-6 mr-4" x-show="!isPwaInstalled && !isWebView">
        <div tabindex="0" role="button" data-tip="Klik Aku"
            class="btn btn-xl btn-circle btn-secondary text-sm tooltip tooltip-secondary tooltip-open animate-bounce">
            <x-icon name="iconpark.press-o" />
        </div>

        <div class="fab-close text-error font-bold">
            Tutup <span class="btn btn-circle btn-xl btn-error"><x-icon name="iconpark.handlex-o" /></span>
        </div>

        <x-button label="Download Aplikasi" icon="iconpark.downloadfour-o" class="btn-md btn-primary"
            wire:click="openInstallModal" />
    </div>

    {{-- Modal Manual Install Instructions --}}
    <x-modal wire:model="showManualInstallModal" title="Cara Install Aplikasi" class="modal-bottom w-full backdrop-blur"
        persistent>
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="iconpark.info-o" class="w-16 h-16 text-primary" />
            </div>

            <div class="text-center space-y-2">
                <p class="text-sm text-base-content/70">Browser Anda tidak mendukung instalasi otomatis. Silakan ikuti
                    panduan manual di bawah:</p>
            </div>

            {{-- Android Instructions --}}
            <div class="bg-base-200 rounded-lg p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <x-icon name="bi.android2" class="w-5 h-5 text-success" />
                    <p class="font-bold text-sm">Android (Chrome/Brave)</p>
                </div>
                <ol class="list-decimal list-inside space-y-1 text-sm text-base-content/70 ml-2">
                    <li>Tap ikon <span class="font-semibold">Menu (⋮)</span> di pojok kanan atas</li>
                    <li>Pilih <span class="font-semibold">"Install app"</span> atau <span class="font-semibold">"Add to
                            Home screen"</span></li>
                    <li>Tap <span class="font-semibold">"Install"</span></li>
                </ol>
            </div>

            {{-- iOS Instructions --}}
            <div class="bg-base-200 rounded-lg p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <x-icon name="bi.apple" class="w-5 h-5 text-base-content" />
                    <p class="font-bold text-sm">iOS (Safari)</p>
                </div>
                <ol class="list-decimal list-inside space-y-1 text-sm text-base-content/70 ml-2">
                    <li class="flex items-center gap-1">
                        <span>Tap ikon</span>
                        <span class="inline-flex items-center gap-1 font-semibold">
                            <x-icon name="bi.box-arrow-up" class="w-4 h-4" />
                            Share
                        </span>
                        <span>di bawah</span>
                    </li>
                    <li>Scroll dan pilih <span class="font-semibold">"Add to Home Screen"</span></li>
                    <li>Tap <span class="font-semibold">"Add"</span></li>
                </ol>
            </div>

            <div class="alert alert-warning">
                <x-icon name="iconpark.attention-o" class="w-5 h-5" />
                <span class="text-xs">Catatan: Pastikan Anda menggunakan browser Chrome (Android) atau Safari
                    (iOS)</span>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Mengerti" class="btn-primary btn-block" @click="$wire.showManualInstallModal = false" />
        </x-slot:actions>
    </x-modal>

    {{-- Modal Install PWA --}}
    <x-modal wire:model="showInstallModal" title="Install Aplikasi" class="modal-bottom w-full backdrop-blur" persistent>
        <div class="space-y-4">
            <div class="flex justify-center">
                <img src="{{ asset('icons/icon-512x512.png') }}" alt="App Icon" class="w-24 h-24 rounded-2xl shadow-lg" />
            </div>

            <div class="text-center space-y-2">
                <h3 class="font-bold text-lg">SiAktif</h3>
                <p class="text-sm text-base-content/70">Aplikasi kurir untuk kelola pengiriman laundry dengan mudah</p>
            </div>

            <div class="bg-base-200 rounded-lg p-4 space-y-2">
                <div class="flex items-start gap-3">
                    <x-icon name="iconpark.check-o" class="text-success w-5 h-5 mt-0.5" />
                    <div>
                        <p class="font-semibold text-sm">Akses Cepat</p>
                        <p class="text-xs text-base-content/70">Buka aplikasi langsung dari home screen</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <x-icon name="iconpark.check-o" class="text-success w-5 h-5 mt-0.5" />
                    <div>
                        <p class="font-semibold text-sm">Pengalaman Seperti Aplikasi Native</p>
                        <p class="text-xs text-base-content/70">Tampilan fullscreen tanpa browser bar</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <x-icon name="iconpark.check-o" class="text-success w-5 h-5 mt-0.5" />
                    <div>
                        <p class="font-semibold text-sm">Loading Lebih Cepat</p>
                        <p class="text-xs text-base-content/70">Assets di-cache untuk performa optimal</p>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-2 gap-4 w-full">
                <x-button label="Nanti Saja" class="btn-ghost" @click="$wire.showInstallModal = false" />
                <x-button label="Install Sekarang" class="btn-primary" wire:click="installPWA" spinner="installPWA" />
            </div>
        </x-slot:actions>
    </x-modal>
</div>
