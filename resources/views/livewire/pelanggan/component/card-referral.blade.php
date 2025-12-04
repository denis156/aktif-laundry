<div class="block w-full ">
    <x-card title="Untung Bareng Teman!" subtitle="Bagikan kode & nikmati promo spesial"
        class="shadow-lg border border-primary col-span-full text-primary">

        {{-- Benefit Info --}}
        <div class="space-y-3 mb-4">
            {{-- Benefit untuk Kamu --}}
            <div>
                <p class="text-xs font-semibold text-success mb-2">Keuntungan Kamu:</p>
                <div class="space-y-1">
                    @forelse ($this->benefitReferrer() as $benefit)
                    <div class="flex items-start gap-2">
                        <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-success shrink-0 mt-0.5" />
                        <span class="text-xs text-base-content/80">Dapat Promo {{ $benefit }}</span>
                    </div>
                    @empty
                    <div class="flex items-start gap-2">
                        <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-success shrink-0 mt-0.5" />
                        <span class="text-xs text-base-content/80">Ajak teman dan dapatkan promo</span>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Benefit untuk Teman --}}
            <div>
                <p class="text-xs font-semibold text-info mb-2">Teman Kamu Dapat:</p>
                <div class="space-y-1">
                    @forelse ($this->benefitReferee() as $benefit)
                    <div class="flex items-start gap-2">
                        <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-info shrink-0 mt-0.5" />
                        <span class="text-xs text-base-content/80">Dapat Promo {{ $benefit }}</span>
                    </div>
                    @empty
                    <div class="flex items-start gap-2">
                        <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-info shrink-0 mt-0.5" />
                        <span class="text-xs text-base-content/80">Promo spesial untuk pendaftar baru</span>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Referral Code Input --}}
        <x-input label="Kode Referral Kamu" value="{{ $this->kodeReferral() }}" readonly>
            <x-slot:prepend>
                <x-button class="join-item btn-success" label="Salin" wire:click="salinKode" />
            </x-slot:prepend>
            <x-slot:append>
                <x-button icon="iconpark.refresh-o" class="join-item btn-primary" tooltip="Generate Baru"
                    wire:click="confirmGenerate" />
            </x-slot:append>
        </x-input>
    </x-card>

    {{-- Modal Konfirmasi Generate Baru --}}
    <x-modal wire:model="confirmGenerateModal" title="Konfirmasi Generate Kode Baru"
        class="modal-bottom w-full backdrop-blur" persistent>
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="iconpark.info-o" class="w-16 h-16 text-warning" />
            </div>
            <div class="text-center space-y-2">
                <p class="text-base font-semibold">Yakin ingin membuat kode referral baru?</p>
                <p class="text-sm text-base-content/70">Kode lama <span class="font-bold text-primary">{{
                        $this->kodeReferral() }}</span> tidak bisa digunakan lagi
                    setelah dibuat kode baru.</p>
            </div>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-2 gap-4 w-full">
                <x-button label="Batal" class="btn-ghost" @click="$wire.confirmGenerateModal = false" />
                <x-button label="Ya, Generate Baru" icon="iconpark.refresh-o" class="btn-warning"
                    wire:click="generateBaru" spinner="generateBaru" />
            </div>
        </x-slot:actions>
    </x-modal>

    {{-- Script untuk copy to clipboard --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', (event) => {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(event.text).catch(err => {
                        console.error('Failed to copy:', err);
                    });
                } else {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = event.text;
                    textArea.style.position = 'fixed';
                    textArea.style.opacity = '0';
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                    } catch (err) {
                        console.error('Failed to copy:', err);
                    }
                    document.body.removeChild(textArea);
                }
            });
        });
    </script>
</div>
