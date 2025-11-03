<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh min-w-dvw font-sans antialiased bg-linear-to-br from-primary/10 via-base-200 to-accent/10">

    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>
