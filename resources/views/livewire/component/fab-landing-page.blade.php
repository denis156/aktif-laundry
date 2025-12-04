<div x-data="{
    isPwaInstalled: false,
    checkPwaInstalled() {
        // Check if running in standalone mode (installed PWA)
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            this.isPwaInstalled = true;
            return;
        }

        // Check if PWA was previously installed (localStorage flag)
        if (localStorage.getItem('pwa-installed') === 'true') {
            this.isPwaInstalled = true;
        }
    }
}" x-init="checkPwaInstalled()">

    {{-- FAB: Always visible on landing page --}}
    <div class="fab mb-6 mr-4">
        <div tabindex="0" role="button" data-tip="Klik Aku"
            class="btn btn-xl btn-circle btn-secondary text-sm tooltip tooltip-secondary tooltip-open animate-bounce">
            <x-icon name="iconpark.press-o" />
        </div>

        <div class="fab-close text-error font-bold">
            Tutup <span class="btn btn-circle btn-xl btn-error"><x-icon name="iconpark.handlex-o" /></span>
        </div>

        <x-button label="Chat Admin" icon="iconpark.communication-o" class="btn-md btn-success" />

        {{-- Conditional button based on PWA install status --}}
        <x-button x-show="!isPwaInstalled" label="Download Aplikasi" icon="iconpark.downloadfour-o"
            class="btn-md btn-primary" wire:click="redirectToPelanggan" />

        <x-button x-show="isPwaInstalled" label="Buka Aplikasi" icon="iconpark.redo-o" class="btn-md btn-accent"
            wire:click="openInstalledApp" />
    </div>
</div>
