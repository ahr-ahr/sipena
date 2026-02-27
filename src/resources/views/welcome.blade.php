<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        {{ app_setting()?->app_name ?? 'SIPENA' }}
        {{ app_setting()?->school_name ? ' | ' . app_setting()->school_name : 'SMK 17 Agustus 1945 Surabaya' }}
    </title>

    <link rel="icon" type="image/png"
      href="{{ env('MINIO_PUBLIC_URL') . '/sipena/' . app_setting()->school_logo }}">

    <meta name="description" content="{{ setting('seo_description', app_setting()?->app_name ?? 'SIPENA') }}">

    <meta name="keywords" content="{{ setting('seo_keywords', '') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-background text-foreground dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC]
             min-h-screen flex flex-col items-center justify-center p-6 lg:p-8">

{{-- ================= HEADER ================= --}}
<header class="w-full max-w-5xl mb-8 flex justify-between items-center text-sm">
    <div class="flex items-center gap-3 font-medium">
        <img
            src="{{ env('MINIO_PUBLIC_URL') . '/sipena/' . app_setting()->school_logo }}"
            class="w-8 h-8 object-contain"
            alt="{{ app_setting()?->school_name ?? 'Logo' }}"
        >
        <span>
    {{ __('welcome.app_name') }}
        </span>
    </div>

    @if (Route::has('login'))
        <nav class="flex gap-3">
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="px-4 py-1.5 rounded-sm border border-[#19140035]
                          dark:border-[#3E3E3A] hover:border-black dark:hover:border-white">
                    {{ __('welcome.dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-sm
                          hover:bg-black/5 dark:hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10 17l5-5-5-5"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12H3"/>
                    </svg>
                    <span>{{ __('welcome.login') }}</span>
                </a>
            @endauth

            <button
                id="theme-toggle"
                class="flex items-center gap-2 px-3 py-1.5 rounded-sm
                       hover:bg-black/5 dark:hover:bg-white/10 transition text-xs">
                <svg class="w-4 h-4 dark:hidden" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707"/>
                    <circle cx="12" cy="12" r="5"/>
                </svg>

                <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 12.79A9 9 0 1111.21 3
                             7 7 0 0021 12.79z"/>
                </svg>

                <span class="hidden sm:inline">{{ __('welcome.theme') }}</span>
            </button>

            <a href="{{ route('lang.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}"
   class="flex items-center gap-1 border rounded-sm px-2 py-1
          text-xs hover:bg-black/5 dark:hover:bg-white/10 transition">

    <!-- Globe -->
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-4 h-4 text-[#706f6c] dark:text-[#A1A09A]"
         fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3a9 9 0 100 18 9 9 0 000-18z"/>
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.05 12h19.9"/>
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 2.05c2.5 2.5 4 6 4 9.95s-1.5 7.45-4 9.95
                 c-2.5-2.5-4-6-4-9.95s1.5-7.45 4-9.95z"/>
    </svg>

    <span class="font-medium uppercase">
        {{ app()->getLocale() }}
    </span>
</a>

        </nav>
    @endif
</header>

{{-- ================= MAIN ================= --}}
<main class="w-full max-w-5xl grid lg:grid-cols-2 rounded-lg overflow-hidden
             shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]
             dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">

    {{-- LEFT CONTENT --}}
    <section class="p-8 lg:p-14 bg-white dark:bg-[#161615] space-y-6">

        <h1 class="text-2xl lg:text-3xl font-semibold leading-tight">
    {{ __('welcome.title') }}
        </h1>

        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] leading-relaxed">
    {{ __('welcome.description_before') }}
    <strong>{{ __('welcome.school_name') }}</strong>
    {{ __('welcome.description_after') }}
</p>

        <ul class="space-y-3 text-sm">
    @foreach (__('welcome.steps') as $step)
        <li class="flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 text-green-600 dark:text-green-400"
                 fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ $step }}</span>
        </li>
    @endforeach
</ul>

        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] leading-relaxed">
            {{ __('welcome.note_revision') }}
        </p>

        <div class="pt-4 flex gap-3">
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="px-5 py-2 rounded-sm bg-[#1b1b18] text-white
                          hover:bg-black dark:bg-white dark:text-black dark:hover:bg-slate-200 transition">
                    {{ __('welcome.cta_dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-5 py-2 rounded-sm bg-[#1b1b18] text-white
                          hover:bg-black dark:bg-white dark:text-black dark:hover:bg-slate-200 transition">
                {{ __('welcome.cta_login') }}
                </a>
            @endauth
        </div>

        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] pt-6">
            © {{ date('Y') }} SIPENA | SMK 17 Agustus 1945 Surabaya<br>
            {{ __('welcome.copyright') }}
        </p>
    </section>

    {{-- RIGHT PANEL --}}
    <section class="relative bg-[#fff2f2] dark:bg-[#1D0002] flex items-center justify-center">
        <div class="text-center p-10 space-y-6 max-w-sm">
            <h2 class="text-xl font-semibold text-[#F53003] dark:text-[#FF4433]">
    {{ __('welcome.flow_title') }}
            </h2>

            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                {{ __('welcome.flow_desc') }}
            </p>
        </div>

        <div class="absolute inset-0
                    shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]
                    dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
        </div>
    </section>

</main>

</body>
</html>
