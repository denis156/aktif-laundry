<div>
    @if(!empty($phoneNumber))
    <x-button label="WhatsApp" icon="o-chat-bubble-left-right" wire:click="sendWhatsApp" spinner="sendWhatsApp" class="btn {{ $btnClass }} btn-{{ $size }}" />
    @else
    <x-button label="WhatsApp" icon="o-chat-bubble-left-right" class="btn-{{ $size }} btn-disabled"/>
    @endif
</div>
