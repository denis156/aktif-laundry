<label
    class="swap {{ $swapClass }} cursor-pointer"
    x-data="{ isDark: $store.darkMode.on }"
    x-init="$watch('$store.darkMode.on', value => isDark = value)"
>
    <input
        type="checkbox"
        x-model="isDark"
        @change="$store.darkMode.toggle()"
    />
    <x-icon :name="$trueIcon" class="swap-on {{ $iconSize }}" />
    <x-icon :name="$falseIcon" class="swap-off {{ $iconSize }}" />
</label>
