<div>
    @if(!empty($phoneNumber))
    <button wire:click="sendWhatsApp" class="btn {{ $btnClass }} btn-{{ $size }}" title="Kirim struk via WhatsApp">
        <x-icon name="o-chat-bubble-left-right" class="w-4 h-4" />
        WhatsApp
    </button>
    @else
    <button class="btn btn-{{ $size }} btn-disabled" disabled title="Pelanggan tidak memiliki nomor WhatsApp">
        <x-icon name="o-chat-bubble-left-right" class="w-4 h-4" />
        WhatsApp
    </button>
    @endif

    @script
    <script>
        $wire.on('open-whatsapp', (event) => {
                window.open(event.url, '_blank');
            });
    </script>
    @endscript
</div>
