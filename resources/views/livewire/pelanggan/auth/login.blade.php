<x-card class="bg-base-200" body-class="space-y-6" title="Selamat Datang Kembali"
    subtitle="Login untuk melanjutkan layanan laundry Anda">
    <x-form wire:submit="login">
        <!-- Email Input -->
        <x-input label="Email" type="email" wire:model="email" placeholder="nama@email.com" icon="o-envelope" required
            autofocus class="input-lg" />

        <!-- Password Input -->
        <x-password label="Password" wire:model="password" placeholder="••••••••" icon="o-lock-closed" right required
            class="input-lg" />

        <!-- Remember & Forgot -->
        <div class="flex items-center justify-between text-sm mt-2">
            <x-checkbox label="Ingat Saya" wire:model="remember" class="checkbox-primary" />
            <a href="{{ route('pelanggan.password.request') }}" class="link link-primary" wire:navigate>
                Lupa Password?
            </a>
        </div>

        <!-- Submit Button -->
        <x-slot:actions>
            <div class="space-y-3 w-full pt-2">
                <x-button label="Masuk" type="submit" spinner="login" class="btn-primary btn-lg btn-block"
                    icon="o-arrow-right-end-on-rectangle" />

                <div class="divider w-full font-bold uppercase py-4">Atau</div>
                <!-- Google -->
                <a href="{{ route('auth.google') }}" class="btn bg-white text-black border-[#e5e5e5] btn-lg btn-block">
                    <svg aria-label="Google logo" width="16" height="16" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512">
                        <g>
                            <path d="m0 0H512V512H0" fill="#fff"></path>
                            <path fill="#34a853" d="M153 292c30 82 118 95 171 60h62v48A192 192 0 0190 341"></path>
                            <path fill="#4285f4" d="m386 400a140 175 0 0053-179H260v74h102q-7 37-38 57"></path>
                            <path fill="#fbbc02" d="m90 341a208 200 0 010-171l63 49q-12 37 0 73"></path>
                            <path fill="#ea4335" d="m153 219c22-69 116-109 179-50l55-54c-78-75-230-72-297 55"></path>
                        </g>
                    </svg>
                    Masuk Dengan Google
                </a>

                <!-- Link to Register -->
                <div class="text-center text-sm">
                    <span class="text-base-content/60">Belum punya akun?</span>
                    <a href="{{ route('register.pelanggan') }}" class="link link-primary font-semibold" wire:navigate>
                        Daftar di sini
                    </a>
                </div>
            </div>
        </x-slot:actions>
    </x-form>

    <!-- Footer -->
    <div class="text-center text-xs">
        <p>{{ config('app.name') }} Developed by <span class="font-bold text-primary uppercase">Team {{
                config('app.name') }}</span></p>
    </div>
</x-card>
