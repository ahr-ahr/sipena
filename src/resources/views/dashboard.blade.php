<x-app-layout>

{{-- ================= PAGE HEADER ================= --}}
<div class="mb-8">
    <h1 class="text-2xl font-semibold">Dashboard</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Ringkasan aktivitas laporan dan tiket Anda
    </p>
</div>

{{-- ================= TOP STATS ================= --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

    {{-- CARD --}}
    <div class="bg-white dark:bg-[#161615]
                rounded-2xl shadow-sm
                border border-transparent dark:border-[#3E3E3A]
                p-6 flex items-center gap-4">
        <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600">
            <span class="material-icons-outlined">description</span>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Laporan Saya</p>
            <p class="text-2xl font-semibold">{{ $laporanTotal }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615]
                rounded-2xl shadow-sm
                border border-transparent dark:border-[#3E3E3A]
                p-6 flex items-center gap-4">
        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600">
            <span class="material-icons-outlined">confirmation_number</span>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tiket Saya</p>
            <p class="text-2xl font-semibold">{{ $ticketTotal }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615]
                rounded-2xl shadow-sm
                border border-transparent dark:border-[#3E3E3A]
                p-6 flex items-center gap-4">
        <div class="p-3 rounded-xl bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600">
            <span class="material-icons-outlined">hourglass_top</span>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Masih Diproses</p>
            <p class="text-2xl font-semibold">{{ $onProgress }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#161615]
                rounded-2xl shadow-sm
                border border-transparent dark:border-[#3E3E3A]
                p-6 flex items-center gap-4">
        <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/30 text-green-600">
            <span class="material-icons-outlined">check_circle</span>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Selesai</p>
            <p class="text-2xl font-semibold">{{ $completed }}</p>
        </div>
    </div>

</div>

{{-- ================= MAIN GRID ================= --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- LEFT --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- PROFILE PROGRESS --}}
        <div class="bg-white dark:bg-[#161615]
                    rounded-2xl shadow-sm
                    border border-transparent dark:border-[#3E3E3A]
                    p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="font-medium">Kelengkapan Akun</p>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $profileProgress }}%
                </span>
            </div>

            <div class="w-full bg-gray-200 dark:bg-[#2A2A28] rounded-full h-3">
                <div
                    class="h-3 rounded-full transition-all
                    {{ $profileProgress < 100 ? 'bg-yellow-500' : 'bg-green-600' }}"
                    style="width: {{ $profileProgress }}%">
                </div>
            </div>

            <ul class="mt-4 text-sm space-y-2">
                <li class="flex items-center gap-2">
                    <span class="material-icons-outlined text-sm {{ $profileChecks['email'] ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $profileChecks['email'] ? 'check_circle' : 'cancel' }}
                    </span>
                    Email terverifikasi
                </li>
                <li class="flex items-center gap-2">
                    <span class="material-icons-outlined text-sm {{ $profileChecks['nama'] ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $profileChecks['nama'] ? 'check_circle' : 'cancel' }}
                    </span>
                    Nama lengkap
                </li>
                <li class="flex items-center gap-2">
                    <span class="material-icons-outlined text-sm {{ $profileChecks['identitas'] ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $profileChecks['identitas'] ? 'check_circle' : 'cancel' }}
                    </span>
                    NIS / NIP
                </li>
            </ul>

            @if ($profileProgress < 100)
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center gap-1 mt-4 text-sm font-medium text-indigo-600 hover:underline">
                    Lengkapi profil
                    <span class="material-icons-outlined text-sm">arrow_forward</span>
                </a>
            @endif
        </div>

        {{-- MINI CHART --}}
        <div class="bg-white dark:bg-[#161615]
                    rounded-2xl shadow-sm
                    border border-transparent dark:border-[#3E3E3A]
                    p-6">
            <p class="font-medium mb-4">Perbandingan Laporan vs Tiket</p>
            <canvas id="miniChart" height="120"></canvas>
        </div>

    </div>

    {{-- RIGHT --}}
    <div class="space-y-6">

        {{-- NOTIFICATIONS --}}
        <div class="bg-white dark:bg-[#161615]
                    rounded-2xl shadow-sm
                    border border-transparent dark:border-[#3E3E3A]
                    p-6">
            <p class="font-medium mb-4 flex items-center gap-2">
                <span class="material-icons-outlined">notifications</span>
                Notifikasi Terakhir
            </p>

            <ul class="space-y-3 text-sm">
                @forelse ($notifications as $n)
                    <li class="border-b border-gray-200 dark:border-[#2A2A28] pb-3 last:border-b-0">
                        <p class="font-medium">
                            {{ $n->notification->judul }}
                        </p>
                        <p class="text-gray-500 dark:text-gray-400 text-xs">
                            {{ $n->notification->pesan }}
                        </p>
                    </li>
                @empty
                    <li class="text-gray-500 dark:text-gray-400 text-sm">
                        Tidak ada notifikasi
                    </li>
                @endforelse
            </ul>
        </div>

    </div>
</div>

{{-- ================= MODALS ================= --}}
<x-create-ticket-modal :ticket-categories="$ticketCategories ?? collect()" />
<x-create-laporan-modal
    :laporanCategories="$laporanCategories"
    :mapelList="$mapelList"
/>

{{-- ================= CHART ================= --}}
@push('scripts')
<script>
const isDark = document.documentElement.classList.contains('dark');

new Chart(document.getElementById('miniChart'), {
    type: 'bar',
    data: {
        labels: ['Laporan', 'Tiket'],
        datasets: [{
            data: [{{ $chartData['laporan'] }}, {{ $chartData['tiket'] }}],
            backgroundColor: ['#6366f1', '#22c55e'],
            borderRadius: 8
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: isDark ? '#A1A09A' : '#4B5563'
                },
                grid: {
                    color: isDark ? '#2A2A28' : '#E5E7EB'
                }
            },
            x: {
                ticks: {
                    color: isDark ? '#A1A09A' : '#4B5563'
                },
                grid: {
                    color: 'transparent'
                }
            }
        }
    }
});
</script>
@endpush

</x-app-layout>
