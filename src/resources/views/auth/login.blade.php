<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SIPENA | Authentication</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

    <!-- Prevent Alpine flash -->
    <style>[x-cloak]{display:none!important}</style>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
    .grid-login {
        grid-template-areas:
            "form info";
    }

    .grid-register {
        grid-template-areas:
            "info form";
    }

    @media (max-width: 1023px) {
        .grid-login,
        .grid-register {
            grid-template-areas:
                "form"
                "info";
        }
    }
</style>
</head>

<body class="min-h-screen flex items-center justify-center
             bg-background text-[#1b1b18]
             dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">

<main
    x-data="{ mode: 'login', isLogin() { return this.mode === 'login' } }"
    :class="mode === 'login' ? 'grid-login' : 'grid-register'"
    class="w-full max-w-5xl h-[540px]
           grid grid-cols-1 lg:grid-cols-2
           overflow-hidden rounded-lg">

    {{-- ======================================================
        LEFT PANEL : FORM (COMPACT)
    ======================================================= --}}
<section
    style="grid-area: form"
    class="bg-white dark:bg-[#161615]
           px-12 py-10 flex items-center justify-center transition-[grid-area] duration-500 ease-in-out">

        <!-- LOGIN -->
        <div
    x-show="mode === 'login'"
    x-transition:enter="transition-all duration-500 ease-out"
    x-transition:enter-start="opacity-0 -translate-x-16 scale-95"
    x-transition:enter-end="opacity-100 translate-x-0 scale-100"

    x-transition:leave="transition-all duration-400 ease-in"
    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
    x-transition:leave-end="opacity-0 translate-x-10 scale-95"

    class="w-full max-w-sm mx-auto space-y-5"
>

            <div class="text-center space-y-2">
            
                <img
                    src="{{ env('MINIO_PUBLIC_URL') }}/sipena/public/logo-smktag.png"
                    class="w-12 h-16 mx-auto object-contain"
                    alt="Logo SIPENA">

                <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Log in
                </h1>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    SIPENA – Sistem Pengaduan & Aspirasi Sekolah
                </p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1
                                  text-[#2a2a28] dark:text-[#E4E4E0]">
                        Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="w-full h-9 px-3 text-sm rounded-sm
       bg-white text-[#1b1b18]
       placeholder:text-[#9a9a96]
       border border-[#19140035]
       focus:border-black focus:ring-0
       dark:bg-white dark:text-[#1b1b18]
       dark:placeholder:text-[#9a9a96]
       dark:border-[#3E3E3A]">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1
                                  text-[#2a2a28] dark:text-[#E4E4E0]">
                        Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full h-9 px-3 text-sm rounded-sm
       bg-white text-[#1b1b18]
       placeholder:text-[#9a9a96]
       border border-[#19140035]
       focus:border-black focus:ring-0
       dark:bg-white dark:text-[#1b1b18]
       dark:placeholder:text-[#9a9a96]
       dark:border-[#3E3E3A]">
                </div>

                <div class="flex items-center justify-between pt-3">
                    <label class="flex items-center gap-2 text-xs
                                  text-[#706f6c] dark:text-[#A1A09A]">
                        <input type="checkbox" name="remember" class="rounded">
                        Remember me
                    </label>

                    <button
                        class="px-4 py-1.5 rounded-sm text-sm
                               bg-[#1b1b18] text-white
                               hover:bg-black
                               dark:bg-white dark:text-black
                               dark:hover:bg-slate-200 transition">
                        Log in
                    </button>
                </div>
            </form>

            <p class="text-xs text-center
                      text-[#706f6c] dark:text-[#A1A09A]">
                Belum punya akun?
                <button @click="mode='register'"
                        class="text-[#F53003] font-medium hover:underline">
                    Daftar
                </button>
            </p>
        </div>

        <!-- REGISTER -->
        <div
    x-show="mode === 'register'"
    x-transition:enter="transition-all duration-500 ease-out"
    x-transition:enter-start="opacity-0 translate-x-16 scale-95"
    x-transition:enter-end="opacity-100 translate-x-0 scale-100"

    x-transition:leave="transition-all duration-400 ease-in"
    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-x-10 scale-95"

    class="w-full max-w-sm mx-auto space-y-5"
>

            <div class="text-center space-y-2">
                <img
                    src="{{ env('MINIO_PUBLIC_URL') }}/sipena/public/logo-smktag.png"
                    class="w-12 h-16 mx-auto object-contain"
                    alt="Logo SIPENA">

                <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Register
                </h1>

                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    SIPENA – Sistem Pengaduan & Aspirasi Sekolah
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-3">
                @csrf

                <input type="email" name="email" placeholder="Email" required
                    class="w-full h-9 px-3 text-sm rounded-sm
       bg-white text-[#1b1b18]
       placeholder:text-[#9a9a96]
       border border-[#19140035]
       focus:border-black focus:ring-0
       dark:bg-white dark:text-[#1b1b18]
       dark:placeholder:text-[#9a9a96]
       dark:border-[#3E3E3A]">

                <input type="password" name="password" placeholder="Password" required autocomplete="new-password"
                    class="w-full h-9 px-3 text-sm rounded-sm
       bg-white text-[#1b1b18]
       placeholder:text-[#9a9a96]
       border border-[#19140035]
       focus:border-black focus:ring-0
       dark:bg-white dark:text-[#1b1b18]
       dark:placeholder:text-[#9a9a96]
       dark:border-[#3E3E3A]">

                <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required
                    class="w-full h-9 px-3 text-sm rounded-sm
       bg-white text-[#1b1b18]
       placeholder:text-[#9a9a96]
       border border-[#19140035]
       focus:border-black focus:ring-0
       dark:bg-white dark:text-[#1b1b18]
       dark:placeholder:text-[#9a9a96]
       dark:border-[#3E3E3A]">

                <div class="flex gap-4 text-sm">
    <label class="flex items-center gap-2">
        <input type="radio" name="tipe_user" value="siswa" required>
        Siswa
    </label>

    <label class="flex items-center gap-2">
        <input type="radio" name="tipe_user" value="pegawai" required>
        Pegawai
    </label>
</div>

                <button
                    class="w-full mt-2 px-4 py-1.5 rounded-sm text-sm
                           border border-[#F53003] text-[#F53003]
                           hover:bg-[#F53003]/5 transition">
                    Daftar
                </button>
            </form>

            <p class="text-xs text-center
                      text-[#706f6c] dark:text-[#A1A09A]">
                Sudah punya akun?
                <button @click="mode='login'"
                        class="text-[#F53003] font-medium hover:underline">
                    Masuk
                </button>
            </p>
        </div>

    </section>

    {{-- ======================================================
        RIGHT PANEL : LANDING INFO
    ======================================================= --}}
<section
    style="grid-area: info"
    class="relative bg-[#fff2f2] dark:bg-[#1D0002]
           flex items-center justify-center">

        <div
            x-show="mode === 'login'"
            x-transition
            class="text-center p-10 max-w-sm">

            <h2 class="text-xl font-semibold text-[#F53003] dark:text-[#FF4433]">
                Alur Jelas • Berjenjang • Bertanggung Jawab
            </h2>

            <p class="mt-4 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Setiap laporan murid diproses dari Wali Kelas
                hingga Kepala Sekolah secara transparan dan terdokumentasi.
            </p>
        </div>

        <div
            x-show="mode === 'register'"
            x-transition
            class="text-center p-10 max-w-sm">

            <h2 class="text-xl font-semibold text-[#F53003] dark:text-[#FF4433]">
                Satu Akun untuk Aspirasi Sekolah
            </h2>

            <p class="mt-4 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Sampaikan aspirasi dan pengaduan sekolah secara resmi dan aman.
            </p>
        </div>

        <div class="absolute inset-0
                    shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]
                    dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"></div>
    </section>

</main>

</body>
</html>
