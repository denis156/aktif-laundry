<x-toggle
    class="{{ $toggleClass }}"
    x-data="{ isDark: $store.darkMode.on }"
    x-init="$watch('$store.darkMode.on', value => isDark = value)"
    x-model="isDark"
    @change="$store.darkMode.toggle()"
    :right="$right"
/>
