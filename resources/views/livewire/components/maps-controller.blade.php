<div>
    {{-- Maps Controller Component --}}
    <div
        x-data="{
            layerMenuOpen: false,
            mapId: @entangle('mapId'),
            currentLayer: @entangle('currentLayer'),
            availableLayers: {
                'googleStreet': 'Street',
                'googleSatellite': 'Satellite',
                'googleHybrid': 'Hybrid',
                'googleTraffic': 'Traffic',
                'openStreetMap': 'OSM'
            }
        }"
        @click.away="layerMenuOpen = false"
        class="flex flex-col gap-2"
        wire:ignore
    >
        {{-- Zoom Controls --}}
        @if ($showZoom)
            <div class="bg-base-100 rounded-lg shadow-lg overflow-hidden">
                <button
                    @click="window.dispatchEvent(new CustomEvent('maps:zoom-in', { detail: { mapId } }))"
                    class="flex items-center justify-center w-10 h-10 hover:bg-base-200 active:bg-base-300 transition-colors border-b border-base-300"
                    title="Zoom In"
                >
                    <x-icon name="iconpark.plus-o" class="h-5 text-base-content" />
                </button>
                <button
                    @click="window.dispatchEvent(new CustomEvent('maps:zoom-out', { detail: { mapId } }))"
                    class="flex items-center justify-center w-10 h-10 hover:bg-base-200 active:bg-base-300 transition-colors"
                    title="Zoom Out"
                >
                    <x-icon name="iconpark.minus-o" class="h-5 text-base-content" />
                </button>
            </div>
        @endif

        {{-- Layer Switcher --}}
        @if ($showLayers)
            <div class="relative">
                <button
                    @click="layerMenuOpen = !layerMenuOpen"
                    class="flex items-center justify-center w-10 h-10 bg-base-100 rounded-lg shadow-lg hover:bg-base-200 active:bg-base-300 transition-colors"
                    title="Change Layer"
                >
                    <x-icon name="iconpark.layers-o" class="h-5 text-base-content" />
                </button>

                {{-- Layer Menu --}}
                <div
                    x-show="layerMenuOpen"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute {{ $this->getLayerMenuPosition() }} mt-2 w-36 bg-base-100 rounded-lg shadow-xl overflow-hidden"
                    style="display: none;"
                >
                    <template x-for="(label, key) in availableLayers" :key="key">
                        <button
                            @click="
                                currentLayer = key;
                                layerMenuOpen = false;
                                window.dispatchEvent(new CustomEvent('maps:change-layer', { detail: { mapId, layer: key } }));
                            "
                            :class="currentLayer === key ? 'bg-primary text-primary-content' : 'hover:bg-base-200'"
                            class="w-full px-4 py-2.5 text-left text-sm transition-colors flex items-center justify-between"
                            x-text="label"
                        ></button>
                    </template>
                </div>
            </div>
        @endif

        {{-- Center to Kurir --}}
        @if ($showCenter)
            <button
                @click="window.dispatchEvent(new CustomEvent('maps:center-kurir', { detail: { mapId } }))"
                class="flex items-center justify-center w-10 h-10 bg-base-100 rounded-lg shadow-lg hover:bg-base-200 active:bg-base-300 transition-colors"
                title="Center to Kurir"
            >
                <x-icon name="iconpark.gps-o" class="h-5 text-base-content" />
            </button>
        @endif

        {{-- Center Route --}}
        @if ($showCompass)
            <button
                @click="window.dispatchEvent(new CustomEvent('maps:center-route', { detail: { mapId } }))"
                class="flex items-center justify-center w-10 h-10 bg-base-100 rounded-lg shadow-lg hover:bg-base-200 active:bg-base-300 transition-colors"
                title="Center Route"
            >
                <x-icon name="iconpark.compass-o" class="h-5 text-base-content" />
            </button>
        @endif
    </div>
</div>

@script
    <script>
        // Maps Controller Event Listeners
        console.log('MapsController component initialized');
    </script>
@endscript
