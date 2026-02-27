<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
    class="min-h-screen flex items-center justify-center
           bg-background text-foreground
           dark:bg-[#0a0a0a]">

<div
    class="relative w-full max-w-5xl h-[520px] overflow-hidden rounded-lg
           shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]
           dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]
           flex">

    <div class="w-1/2 bg-white dark:bg-[#161615] p-10 flex flex-col justify-center">
        <div class="mb-6 text-center">
            <a href="/">
                <img
    src="{{ env('MINIO_PUBLIC_URL') }}/sipena/public/logo-smktag.png"
    class="w-10 h-20 mx-auto mb-3 object-contain"
    alt="Logo SIPENA"
/>
            </a>
            <h2 class="text-xl font-semibold">
                {{ request()->routeIs('login') ? __('Log in') : __('Register') }}
            </h2>
        </div>

        {{ $slot }}
    </div>

    {{-- RIGHT: CTA PANEL --}}
    <div
        class="w-1/2 flex items-center justify-center text-center px-10
               bg-[#fff2f2] dark:bg-[#1D0002]">

        <div class="space-y-4">
            <h3 class="text-xl font-semibold text-[#F53003] dark:text-[#FF4433]">
                {{ request()->routeIs('login')
                    ? __('Belum punya akun?')
                    : __('Sudah punya akun?') }}
            </h3>

            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                SIPENA – Sistem Pengaduan & Aspirasi Sekolah
            </p>

            @if (request()->routeIs('login'))
                <a href="{{ route('register') }}"
                   class="inline-block px-5 py-2 rounded-sm border
                          border-[#F53003] text-[#F53003]
                          dark:border-[#FF4433] dark:text-[#FF4433]">
                    Daftar
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-block px-5 py-2 rounded-sm border
                          border-[#F53003] text-[#F53003]
                          dark:border-[#FF4433] dark:text-[#FF4433]">
                    Masuk
                </a>
            @endif
        </div>
    </div>

</div>
</body>
</html>
