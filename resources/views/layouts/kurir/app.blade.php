<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-dvh min-w-dvw font-sans antialiased bg-base-200">

    <livewire:kurir.component.top-nav />

    <main class="h-full w-full p-4">
        {{ $slot }}
    </main>

    <livewire:kurir.component.bottom-nav />

    {{-- Theme Contrroller --}}
    <x-theme-toggle class="hidden" />

    {{--  TOAST area --}}
    <x-toast />
</body>

</html>
