@php
    $user = auth()->user();

    $isIT = $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->jabatan->contains('nama_jabatan', 'IT');

    $isWaliKelas = $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->jabatan->contains('nama_jabatan', 'Wali Kelas');

    $isSarpras = $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->jabatan->contains('nama_jabatan', 'Sarpras');

    $isTU = $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->jabatan->contains('nama_jabatan', 'TU');

    $isKepalaSekolah = $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->jabatan->contains('nama_jabatan', 'Kepala Sekolah');

    $isBK = $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->jabatan->contains('nama_jabatan', 'BK');

    $isKesiswaan = $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->jabatan->contains('nama_jabatan', 'Kesiswaan');
@endphp


<div
    x-data="{ open: true }"
    class="fixed inset-x-0 bottom-6 z-40 flex flex-col items-center"
>

    {{-- CHEVRON TOGGLE --}}
    <button
        @click="open = !open"
        class="mb-2 flex items-center justify-center
               w-8 h-8 rounded-full
               bg-white dark:bg-[#161615]
               border border-[#19140035] dark:border-[#3E3E3A]
               shadow hover:bg-black/5 dark:hover:bg-white/10"
    >
        <span class="material-symbols-outlined text-[20px]"
              x-text="open ? 'expand_more' : 'expand_less'"></span>
    </button>

    {{-- MENU CONTAINER --}}
    <div
        x-show="open"
        x-transition
        x-cloak
        class="flex items-center gap-2
               px-3 py-2 rounded-2xl
               bg-white dark:bg-[#161615]
               border border-[#19140035] dark:border-[#3E3E3A]
               shadow-xl"
    >

        {{-- DASHBOARD (SEMUA) --}}
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center gap-1 px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">dashboard</span>
            <span class="text-[11px]">Dashboard</span>
        </a>

        {{-- LAPORAN (SISWA ONLY) --}}
        @if ($user->tipe_user === \App\Enums\UserType::SISWA)
        <a href="{{ route('laporan.index') }}"
           class="flex flex-col items-center gap-1 px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">description</span>
            <span class="text-[11px]">Laporan</span>
        </a>
        @endif

        {{-- BUAT LAPORAN (SISWA ONLY) --}}
        @if ($user->tipe_user === \App\Enums\UserType::SISWA)
<button
    @click="createLaporanOpen = true"
    class="flex flex-col items-center gap-1
           px-4 py-3 rounded-xl
           bg-black text-white
           hover:bg-black/80
           dark:bg-white dark:text-black
           dark:hover:bg-white/80
           shadow-md transition"
>
    <span class="material-symbols-outlined text-[26px]">add_circle</span>
    <span class="text-[11px] font-medium">Buat</span>
</button>
@endif

        {{-- TIKET (SISWA & IT) --}}
        @if (
    $user->tipe_user === \App\Enums\UserType::SISWA
    || (
        $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->hasAnyJabatan([
            'Wali Kelas',
            'TU',
            'Kepala Sekolah'
        ])
    )
)
    <a href="{{ route('tickets.index') }}"
       class="flex flex-col items-center gap-1 px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined text-[22px]">
            confirmation_number
        </span>
        <span class="text-[11px]">Tiket</span>
    </a>
@endif

        {{-- TIKET KHUSUS IT --}}
        @if (
        $user->tipe_user === \App\Enums\UserType::PEGAWAI
        && $user->hasAnyJabatan([
            'IT',
            'Sarpras',
        ]))
        <a href="{{ route('it.tickets.index') }}"
        class="flex flex-col items-center gap-1 px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">support_agent</span>
            <span class="text-[11px]">Helpdesk</span>
        </a>
        @endif

        {{-- RIWAYAT (SISWA & IT) --}}
        <a href="{{ route('history.index') }}"
           class="flex flex-col items-center gap-1 px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">history</span>
            <span class="text-[11px]">Riwayat</span>
        </a>

        {{-- ================= MENU WALI KELAS ================= --}}
@if ($isWaliKelas)

    <a href="{{ route('wali.laporan.index') }}"
       class="flex flex-col items-center gap-1
              px-3 py-2 rounded-xl
              hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined text-[22px]">
            assignment_turned_in
        </span>
        <span class="text-[11px]">
            Laporan Murid
        </span>
    </a>

@endif
{{-- ================= END WALI KELAS ================= --}}

        {{-- ================= MENU KHUSUS IT ================= --}}
    @if ($isIT)

        {{-- USER MANAGEMENT --}}
        <a href="{{ route('it.users.index') }}"
        class="flex flex-col items-center gap-1
                px-3 py-2 rounded-xl
                hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">
                group
            </span>
            <span class="text-[11px]">
                Users
            </span>
        </a>

        {{-- KELAS WALI --}}
        <a href="{{ route('it.kelas-wali.index') }}"
        class="flex flex-col items-center gap-1
                px-3 py-2 rounded-xl
                hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">school</span>
            <span class="text-[11px]">Kelas Wali</span>
        </a>

        {{-- SYSTEM SETTINGS --}}
        <a href="{{ route('it.settings.edit') }}"
        class="flex flex-col items-center gap-1
                px-3 py-2 rounded-xl
                hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">
                settings
            </span>
            <span class="text-[11px]">
                Settings
            </span>
        </a>

        {{-- AUDIT LOGS --}}
        <a href="{{ route('it.audit-logs.index') }}"
        class="flex flex-col items-center gap-1
                px-3 py-2 rounded-xl
                hover:bg-black/5 dark:hover:bg-white/10">
            <span class="material-symbols-outlined text-[22px]">
                history_edu
            </span>
            <span class="text-[11px]">
                Audit
            </span>
        </a>

    @endif
    {{-- ================= END IT MENU ================= --}}
    
    {{-- ================= MENU SARPRAS ================= --}}
@if ($isSarpras)

    <a href="{{ route('sarpras.laporan.index') }}"
       class="flex flex-col items-center gap-1
              px-3 py-2 rounded-xl
              hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined text-[22px]">
            construction
        </span>
        <span class="text-[11px]">
            Laporan Sarpras
        </span>
    </a>

@endif

@if ($isTU)

    <a href="{{ route('tu.laporan.index') }}"
       class="flex flex-col items-center gap-1
              px-3 py-2 rounded-xl
              hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined text-[22px]">
            receipt_long
        </span>
        <span class="text-[11px]">
            Laporan TU
        </span>
    </a>

@endif

@if ($isKepalaSekolah)

    <a href="{{ route('kepsek.laporan.index') }}"
       class="flex flex-col items-center gap-1
              px-3 py-2 rounded-xl
              hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined text-[22px]">
            workspace_premium
        </span>
        <span class="text-[11px]">
            Laporan Kepsek
        </span>
    </a>

@endif

@if ($isBK)

    <a href="{{ route('bk.laporan.index') }}"
       class="flex flex-col items-center gap-1
              px-3 py-2 rounded-xl
              hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined text-[22px]">
            psychology
        </span>
        <span class="text-[11px]">
            Laporan BK
        </span>
    </a>

@endif

@if ($isKesiswaan)

    <a href="{{ route('kesiswaan.laporan.index') }}"
       class="flex flex-col items-center gap-1
              px-3 py-2 rounded-xl
              hover:bg-black/5 dark:hover:bg-white/10">
        <span class="material-symbols-outlined text-[22px]">
            groups
        </span>
        <span class="text-[11px]">
            Laporan Kesiswaan
        </span>
    </a>

@endif
{{-- ================= END SARPRAS ================= --}}


    </div>
</div>
