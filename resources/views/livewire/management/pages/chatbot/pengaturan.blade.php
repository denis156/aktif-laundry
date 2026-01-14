<div class="p-6">
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">Pengaturan Chatbot WhatsApp</h1>
                <p class="text-base-content/70 mt-1">Konfigurasi AI chatbot untuk WhatsApp otomatis</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- LEFT SIDE: Form (2 columns) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Info Card --}}
                <x-card class="shadow-md bg-info/5 border border-info/20">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0">
                            <x-icon name="o-information-circle" class="w-6 h-6 text-info" />
                        </div>
                        <div class="flex-1 space-y-2 text-sm">
                            <p class="font-semibold text-info">Cara Kerja Chatbot:</p>
                            <ul class="list-disc list-inside space-y-1 text-base-content/70">
                                <li><span class="font-medium">Auto-reply:</span> Bot akan membalas pesan WhatsApp secara
                                    otomatis menggunakan AI</li>
                                <li><span class="font-medium">System Prompt:</span> Instruksi yang mengatur kepribadian dan
                                    perilaku chatbot</li>
                                <li><span class="font-medium">Temperature:</span> Kreativitas jawaban (0 = konservatif, 1 =
                                    kreatif)</li>
                                <li><span class="font-medium">Max Tokens:</span> Panjang maksimal respons yang dihasilkan
                                </li>
                                <li><span class="font-medium">Fallback Message:</span> Pesan yang dikirim jika terjadi error
                                </li>
                            </ul>
                        </div>
                    </div>
                </x-card>

                {{-- Basic Settings --}}
                <x-card class="shadow-md">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-base-300">
                        <div class="bg-primary/10 p-2 rounded-lg">
                            <x-icon name="o-cog-6-tooth" class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Pengaturan Dasar</h3>
                            <p class="text-sm text-base-content/70">Konfigurasi umum chatbot</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <x-input label="Nama Pengaturan" wire:model="name" icon="o-tag"
                            hint="Nama untuk identifikasi pengaturan ini" />

                        <div class="grid grid-cols-2 gap-4">
                            <x-toggle label="Aktifkan Pengaturan Ini" wire:model="is_active" hint="Set sebagai active" />
                            <x-toggle label="Enable Chatbot" wire:model="enable_chatbot" hint="Aktifkan/nonaktifkan bot" />
                        </div>
                    </div>
                </x-card>

                {{-- System Prompt --}}
                <x-card class="shadow-md">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-base-300">
                        <div class="bg-secondary/10 p-2 rounded-lg">
                            <x-icon name="o-chat-bubble-left-right" class="w-6 h-6 text-secondary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">System Prompt (Instruksi AI)</h3>
                            <p class="text-sm text-base-content/70">Atur kepribadian dan aturan chatbot</p>
                        </div>
                    </div>

                    <x-textarea label="System Prompt" wire:model="system_prompt" rows="12"
                        hint="Instruksi lengkap untuk AI tentang cara menjawab pesan"
                        placeholder="Tulis instruksi untuk chatbot di sini..." />
                </x-card>

                {{-- Advanced Settings --}}
                <x-card class="shadow-md">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-base-300">
                        <div class="bg-accent/10 p-2 rounded-lg">
                            <x-icon name="o-adjustments-horizontal" class="w-6 h-6 text-accent" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Pengaturan Lanjutan</h3>
                            <p class="text-sm text-base-content/70">Konfigurasi teknis chatbot</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <x-input label="Max Tokens" wire:model="max_tokens" type="number" icon="o-document-text"
                            hint="Panjang maksimal respons (100-100000)" />

                        <x-input label="Temperature" wire:model="temperature" type="number" step="0.01"
                            icon="o-fire" hint="Kreativitas jawaban (0.00-2.00)" />

                        <x-input label="Timeout (detik)" wire:model="timeout" type="number" icon="o-clock"
                            hint="Waktu tunggu maksimal (5-120 detik)" />

                        <x-textarea label="Fallback Message" wire:model="fallback_message" rows="3"
                            hint="Pesan yang dikirim jika terjadi error" />
                    </div>
                </x-card>

            </div>

            {{-- RIGHT SIDE: Preview & Actions --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 space-y-6">

                    {{-- Status Card --}}
                    <x-card class="shadow-lg bg-primary text-primary-content">
                        <div class="text-center space-y-3">
                            <div class="w-16 h-16 mx-auto bg-white/20 rounded-full flex items-center justify-center">
                                <x-icon name="o-cpu-chip" class="w-8 h-8" />
                            </div>
                            <h3 class="text-xl font-bold">Status Chatbot</h3>
                            <div class="divider divider-neutral opacity-20"></div>
                            <div class="text-left space-y-3 bg-white/10 p-4 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-80">Status:</span>
                                    <span class="font-bold text-sm">
                                        @if ($enable_chatbot)
                                            <span class="badge badge-success badge-sm">Aktif</span>
                                        @else
                                            <span class="badge badge-error badge-sm">Nonaktif</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="divider divider-neutral opacity-10 my-2"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-80">Temperature:</span>
                                    <span class="font-bold text-sm">{{ $temperature }}</span>
                                </div>
                                <div class="divider divider-neutral opacity-10 my-2"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-80">Max Tokens:</span>
                                    <span class="font-bold text-sm">{{ number_format($max_tokens) }}</span>
                                </div>
                                <div class="divider divider-neutral opacity-10 my-2"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-80">Timeout:</span>
                                    <span class="font-bold text-sm">{{ $timeout }}s</span>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    {{-- Action Buttons --}}
                    <x-card class="shadow-md">
                        <div class="space-y-3">
                            <x-button label="Simpan Pengaturan" icon="o-check" wire:click="save" spinner="save"
                                class="btn-success w-full" />
                            @if ($hasChanges)
                                <x-button label="Reset Perubahan" icon="o-arrow-path" wire:click="resetChanges"
                                    class="btn-warning btn-outline w-full" />
                            @endif
                        </div>
                    </x-card>

                    {{-- Help Card --}}
                    <x-card class="shadow-md bg-base-200">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="o-question-mark-circle" class="w-5 h-5 text-accent" />
                                <h4 class="font-semibold">Tips</h4>
                            </div>
                            <div class="space-y-2 text-sm text-base-content/70">
                                <p><span class="font-medium">Temperature rendah (0.3-0.5)</span> untuk jawaban konsisten
                                </p>
                                <p><span class="font-medium">Temperature tinggi (0.7-1.0)</span> untuk jawaban kreatif</p>
                                <p><span class="font-medium">Uji prompt</span> melalui webhook WhatsApp sebelum produksi
                                </p>
                            </div>
                        </div>
                    </x-card>

                </div>
            </div>
        </div>

    </div>
</div>
