<x-app-layout>

{{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-4">
    <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
        Pengaturan Sistem
    </h1>
</div>

{{-- ================= CONTENT ================= --}}
<div class="mt-6 p-6 rounded-xl
            bg-white dark:bg-[#161615]
            border border-[#19140035] dark:border-[#3E3E3A]">

    @if (session('success'))
        <div class="mb-4 text-sm text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ route('it.settings.update') }}"
          class="space-y-6">
        @csrf

        {{-- APP NAME --}}
        <div>
            <label class="text-sm font-medium">Nama Aplikasi</label>
            <input
                name="app_name"
                value="{{ old('app_name', $app->app_name) }}"
                class="mt-1 w-full rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">
        </div>

        {{-- SHORT NAME --}}
        <div>
            <label class="text-sm font-medium">Nama Singkat</label>
            <input
                name="app_short_name"
                value="{{ old('app_short_name', $app->app_short_name) }}"
                class="mt-1 w-full rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">
        </div>

        {{-- LOGO --}}
        <div>
            <label class="text-sm font-medium">Logo Sekolah</label>
            <input type="file" name="school_logo" class="mt-1 text-sm">
            @if ($app->school_logo)
                <img src="{{ env('MINIO_PUBLIC_URL') . '/sipena/' . app_setting()->school_logo }}"
                     class="mt-2 h-12">
            @endif
        </div>

        {{-- SEO --}}
        <div class="pt-4 border-t border-[#19140035] dark:border-[#3E3E3A]">
            <h2 class="text-sm font-semibold mb-2">SEO</h2>

            <div class="space-y-3">
                <input
                    name="seo_description"
                    placeholder="Meta Description"
                    value="{{ setting('seo_description') }}"
                    class="w-full rounded-sm px-3 py-2 text-sm
                           bg-white dark:bg-[#161615]
                           border border-[#19140035] dark:border-[#3E3E3A]">

                <input
                    name="seo_keywords"
                    placeholder="Meta Keywords"
                    value="{{ setting('seo_keywords') }}"
                    class="w-full rounded-sm px-3 py-2 text-sm
                           bg-white dark:bg-[#161615]
                           border border-[#19140035] dark:border-[#3E3E3A]">
            </div>
        </div>

        <div class="pt-4">
            <button
                class="px-5 py-2 rounded-sm
                       bg-[#1b1b18] text-white
                       dark:bg-white dark:text-black">
                Simpan Pengaturan
            </button>
        </div>

    </form>
</div>

</x-app-layout>
