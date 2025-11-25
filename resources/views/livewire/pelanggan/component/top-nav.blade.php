<nav class="navbar bg-primary text-primary-content sticky top-0 z-50">
    <div class="navbar-start">
        <x-button icon="iconpark.setting-o" class="btn-circle" link="{{ route('pengaturan.pelanggan') }}" />
    </div>
    <div class="navbar-center">
        <span class="text-md font-extrabold uppercase">{{ config('app.name') }}</span>
    </div>
    <div class="navbar-end">
        <div class="avatar avatar-online avatar-placeholder">
            <div class="h-10 w-auto rounded-full ring-2 ring-success ring-offset ring-offset-base-100">
                <img src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp" />
            </div>
        </div>
    </div>
</nav>
