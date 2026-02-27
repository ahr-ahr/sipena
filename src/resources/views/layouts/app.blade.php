

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SIPENA | Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="icon" type="image/png"
      href="{{ env('MINIO_PUBLIC_URL') . '/sipena/' . app_setting()->school_logo }}">

      <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
x-data="{
    helpdeskOpen : false,
    createTicketOpen: false,
    ticketLoading: false,
    ticketErrors: {},
    ticketSuccess: false,
    selectedKategoriNama: '',

    createLaporanOpen: false,
    laporanLoading: false,
    laporanErrors: {},
    laporanSuccess: false,

    async submitLaporan(e) {
        this.laporanLoading = true
        this.laporanErrors = {}

        const form = e.target
        const formData = new FormData(form)

        try {
            const res = await fetch('{{ route('laporan.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })

            const data = await res.json()

            if (!res.ok) {
                if (res.status === 422) {
                    this.laporanErrors = data.errors

                    // tampilkan warning AI jika ada
                    if (data.errors?.deskripsi) {
                        this.showAiWarning(data.errors.deskripsi[0])
                    }

                    return
                }
                throw new Error('Submit gagal')
            }

            this.laporanSuccess = true
            this.createLaporanOpen = false
            form.reset()

            setTimeout(() => this.laporanSuccess = false, 3000)

        } catch (err) {
            console.error(err)
        } finally {
            this.laporanLoading = false
        }
    },

    showAiWarning(message) {
        document.getElementById('ai-warning-text').innerText = message
        const m = document.getElementById('ai-warning-modal')
        m.classList.remove('hidden')
        m.classList.add('flex')
    },

    closeAiWarning() {
        const m = document.getElementById('ai-warning-modal')
        m.classList.add('hidden')
        m.classList.remove('flex')
    },
}"
class="bg-background text-foreground dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen flex flex-col"
>

@include('components.navbar')

{{-- ================= MAIN ================= --}}
<main class="flex-1 max-w-7xl mx-auto px-6 py-10 space-y-6">

    {{ $slot }}

</main>

@include('components.footer')

@include('components.floating-menu')

<button
    @click="helpdeskOpen = true"
    class="fixed bottom-6 right-6 z-40
           flex items-center gap-3
           bg-green-600 hover:bg-green-700
           text-white px-4 py-3
           rounded-full shadow-lg transition"
    title="Helpdesk IT"
>
    <span class="material-symbols-outlined">support_agent</span>
    <span class="text-sm font-medium hidden sm:inline">
        Helpdesk
    </span>
</button>

<div
    x-show="helpdeskOpen"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center
           bg-black/50 backdrop-blur-sm"
>
    <div
        @click.outside="helpdeskOpen = false"
        class="w-full max-w-sm rounded-2xl
               bg-white dark:bg-[#161615]
               p-6 shadow-xl space-y-4"
    >
        <h3 class="text-lg font-semibold">Helpdesk IT</h3>

        <a href="https://wa.me/6282331422421"
           target="_blank"
           class="w-full flex items-center gap-3 px-4 py-3
                  rounded-lg border">
            <span class="material-symbols-outlined">chat</span>
            Chat WhatsApp IT
        </a>

        <button
            @click="helpdeskOpen = false"
            class="w-full text-sm text-gray-500 hover:text-black">
            Tutup
        </button>
    </div>
</div>

<div id="ai-warning-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     @click="closeAiWarning()">

    <div onclick="event.stopPropagation()"
         class="w-full max-w-sm rounded-2xl
                bg-white dark:bg-[#161615]
                p-6 shadow-xl space-y-4 text-center">

        <h3 class="text-lg font-semibold">
            Laporan Ditolak
        </h3>

        <p id="ai-warning-text"
           class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
        </p>

        <button @click="closeAiWarning()"
            class="w-full px-4 py-3 rounded-lg
                   bg-black text-white
                   dark:bg-white dark:text-black">
            Mengerti
        </>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
<script>
window.authUser = {
    tipe: '{{ auth()->user()->tipe_user }}',
    jabatan: '{{ auth()->user()->jabatan->first()?->nama_jabatan }}'
}

function notificationBell() {
    return {
        open: false,
        unreadCount: 0,
        notifications: [],

        sound: new Audio('{{ env('MINIO_PUBLIC_URL') }}/sipena/public/audio/cihuyy-wielino-ino_kO92s4H.mp3'),
        audioUnlocked: false,

        init() {
            document.addEventListener('click', () => {
                this.sound.play().then(() => {
                    this.sound.pause()
                    this.sound.currentTime = 0
                    this.audioUnlocked = true
                }).catch(() => {})
            }, { once: true })

            this.fetchUnreadCount()

            Echo.private('users.{{ auth()->id() }}')
                .subscribed(() => {
                    this.fetchUnreadCount()
                })
                .listen('.notification.created', () => {

                    if (this.audioUnlocked) {
                        this.sound.currentTime = 0
                        this.sound.play().catch(() => {})
                    }

                    this.fetchUnreadCount()
                })
                .listen('.notification.unread.count', (e) => {
                    this.unreadCount = e.count
                })
        },

        toggle() {
            this.open = !this.open
            if (this.open) {
                this.fetchNotifications()
            }
        },

        async fetchNotifications() {
            const res = await fetch('/notifications', {
                headers: { 'Accept': 'application/json' }
            })
            const data = await res.json()
            this.notifications = data.data
        },

        async fetchUnreadCount() {
            const res = await fetch('/notifications/unread-count', {
                headers: { 'Accept': 'application/json' }
            })
            const data = await res.json()
            this.unreadCount = data.count
        },

        async read(notif) {
    // tandai sudah dibaca
    if (!notif.is_read) {
        await fetch(`/notifications/${notif.id}/read`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })

        notif.is_read = true
    }

    const data = notif.notification
    const user = window.authUser

    function hasJabatan(name) {
    if (!user.jabatan) return false
    console.log('user jabatan:', user.jabatan)
    console.log('compare to:', name)
    return user.jabatan.toLowerCase() === name.toLowerCase()
}

// ================= LAPORAN =================
if (data.tipe === 'laporan' && data.laporan_id) {

    if (user.tipe === 'siswa') {
        window.location.href = '/laporan'
        return
    }

    if (user.tipe === 'pegawai') {

        if (hasJabatan('Wali Kelas')) {
            window.location.href = '/wali-kelas/laporan'
            return
        }

        if (hasJabatan('Sarpras')) {
            window.location.href = '/sarpras/laporan'
            return
        }

        if (hasJabatan('TU')) {
            window.location.href = '/tu/laporan'
            return
        }

        if (hasJabatan('Kepala Sekolah')) {
            window.location.href = '/kepsek/laporan'
            return
        }
    }
}

    // ================= TICKET =================
    if (data.tipe === 'ticket' && data.ticket_id) {

    // siswa
    if (user.tipe === 'siswa') {
        window.location.href = '/tickets'
        return
    }

    // pegawai IT
    if (user.tipe === 'pegawai' && user.jabatan === 'IT') {
        window.location.href = '/it/tickets'
        return
    }

    // pegawai selain IT
    if (user.tipe === 'pegawai') {
        window.location.href = '/tickets'
        return
    }
}

    // fallback
    window.location.href = '/history'
},

        async markAll() {
            await fetch('/notifications/read-all', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })

            this.notifications.forEach(n => n.is_read = true)
        }
    }
}
</script>
<script>
function globalSearch() {
    return {
        open: false,
        results: {
            laporan: [],
            tickets: []
        },

        async search(q) {
            if (!q || q.length < 2) {
                this.results = { laporan: [], tickets: [] }
                return
            }

            try {
                const res = await fetch(`/search?q=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json' }
                })

                if (!res.ok) throw new Error('Search failed')

                const data = await res.json()
                this.results = data
            } catch (e) {
                console.error('Search error:', e)
                this.results = { laporan: [], tickets: [] }
            }
        },

        openResult(item) {
            this.open = false

            // buka modal laporan
            if (item.type === 'laporan') {
                if (typeof openDetail === 'function') {
                    openDetail(item.uuid)
                } else {
                    window.location.href = '/laporan'
                }
                return
            }

            // buka modal ticket
            if (item.type === 'ticket') {
                if (typeof openDetail === 'function') {
                    openDetail(item.id)
                } else {
                    window.location.href = '/tickets'
                }
            }
        }
    }
}
</script>
</body>
</html>