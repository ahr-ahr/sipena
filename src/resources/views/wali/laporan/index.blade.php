<x-app-layout>

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-wrap gap-2 items-center justify-between mb-6">

        <h1 class="text-2xl font-semibold">
            Laporan Murid
        </h1>

        <div class="flex flex-wrap gap-2">

            {{-- SEARCH --}}
            <input id="search" type="text"
                placeholder="Cari judul atau kode..."
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">

            {{-- FILTER STATUS --}}
            <select id="filter-status"
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $s)
                    <option value="{{ $s->value }}">
                        {{ $s->label() }}
                    </option>
                @endforeach
            </select>

            {{-- FILTER KATEGORI --}}
            <select id="filter-kategori"
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriLaporan as $k)
                    <option value="{{ $k->id }}">
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>

            {{-- SORT --}}
            <select id="sort"
            <option value="created_at|desc"
    @selected(request('sort_by')=='created_at' && request('sort_dir')=='desc')>

                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="created_at|desc">Terbaru</option>
                <option value="created_at|asc">Terlama</option>
                <option value="judul|asc">Judul A–Z</option>
                <option value="judul|desc">Judul Z–A</option>
            </select>

            <button onclick="exportExcel()"
                class="px-3 py-2 text-sm border rounded">
                Excel
            </button>

            <button onclick="exportPdf()"
                class="px-3 py-2 text-sm border rounded">
                PDF
            </button>

            <div class="flex gap-1 border rounded overflow-hidden">
                <button id="btn-table"
                    class="px-3 py-2 text-sm bg-gray-100">
                    Table
                </button>
                <button id="btn-card"
                    class="px-3 py-2 text-sm">
                    Card
                </button>
            </div>

        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div id="table-view" class="bg-white dark:bg-[#161615]
                border border-[#19140035] dark:border-[#3E3E3A]
                rounded-xl overflow-hidden">

        <table class="w-full text-sm">
            <thead class="border-b text-[#706f6c]">
                <tr>
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Pelapor</th>
                    <th class="text-left px-4 py-3">Kelas</th>
                    <th class="text-left px-4 py-3">Judul</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>

            <tbody id="laporan-tbody">
                @forelse ($laporan as $l)
                    <tr onclick="openModal({{ $l->id }})"
                        class="border-b hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer">

                        <td class="px-4 py-3 font-mono">
                            {{ $l->kode_laporan }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $l->pelapor->siswaProfile->nama }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $l->pelapor->siswaProfile->kelas->nama }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $l->judul }}
                        </td>

                        <td class="px-4 py-3">
                            <span id="status-{{ $l->id }}"></span>
                        </td>

                        <td class="px-4 py-3 text-right space-x-2">
                            @if ($l->status->value === 'menunggu')
                                <button
                                    onclick="event.stopPropagation(); approveLaporan('{{ $l->uuid }}', {{ $l->id }})"
                                    class="text-green-600 hover:underline text-xs">
                                    Setujui
                                </button>

                                <button
                                    onclick="event.stopPropagation(); rejectLaporan('{{ $l->uuid }}', {{ $l->id }})"
                                    class="text-red-600 hover:underline text-xs">
                                    Tolak
                                </button>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            Tidak ada laporan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="card-view"
        class="hidden grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    </div>

    {{-- ================= PAGINATION (fallback) ================= --}}
    <div id="pagination" class="mt-4">
    {{ $laporan->links() }}
</div>

<div id="laporan-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeModal()">

    <div
        onclick="event.stopPropagation()"
        class="w-full max-w-sm rounded-2xl
               bg-white dark:bg-[#161615]
               p-6 shadow-xl space-y-4">

        <h3 class="text-lg font-semibold">
            Detail Laporan
        </h3>

        <div id="modal-content" class="space-y-3 text-sm"></div>
        <div id="modal-attachments" class="space-y-1 text-sm"></div>

        {{-- ACTION BUTTONS --}}
        <div id="modal-actions" class="flex gap-2 pt-3"></div>

        <button
            onclick="closeModal()"
            class="w-full mt-2 text-sm
                   px-4 py-3 rounded-lg
                   border border-[#19140035] dark:border-[#3E3E3A]">
            Tutup
        </button>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const search = document.getElementById('search')
    const status = document.getElementById('filter-status')
    const kategori = document.getElementById('filter-kategori')
    const sort = document.getElementById('sort')
    const tbody = document.getElementById('laporan-tbody')

    let laporanData = @json($laporan->items())

    const tableView = document.getElementById('table-view')
    const cardView = document.getElementById('card-view')
    const btnTable = document.getElementById('btn-table')
    const btnCard = document.getElementById('btn-card')

    let currentView = 'table'

    laporanData.forEach(l => {
        const el = document.getElementById(`status-${l.id}`)
        if (el) el.innerHTML = statusBadgeLaporan(l.status)
    })

        renderCurrentView()

function setView(view) {
    currentView = view

    if (view === 'table') {
        tableView.classList.remove('hidden')
        cardView.classList.add('hidden')

        btnTable.classList.add('bg-gray-100')
        btnCard.classList.remove('bg-gray-100')
    } else {
        tableView.classList.add('hidden')
        cardView.classList.remove('hidden')

        btnCard.classList.add('bg-gray-100')
        btnTable.classList.remove('bg-gray-100')
    }

    renderCurrentView()
}

btnTable.addEventListener('click', () => setView('table'))
btnCard.addEventListener('click', () => setView('card'))

function renderTable(data) {
    if (!data.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                    Tidak ada laporan
                </td>
            </tr>
        `
        return
    }

    tbody.innerHTML = data.map(l => `
        <tr onclick="openModal(${l.id})"
            class="border-b hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer">

            <td class="px-4 py-3 font-mono">
                ${l.kode_laporan}
            </td>

            <td class="px-4 py-3">
                ${l.pelapor.siswa_profile.nama}
            </td>

            <td class="px-4 py-3">
                ${l.pelapor.siswa_profile.kelas.nama}
            </td>

            <td class="px-4 py-3">
                ${l.judul}
            </td>

            <td class="px-4 py-3">
                ${statusBadgeLaporan(l.status)}
            </td>

            <td class="px-4 py-3">
                ${formatDate(l.created_at)}
            </td>

            <td class="px-4 py-3 text-right space-x-2">
                ${l.status === 'menunggu' ? `
                    <button
                        onclick="event.stopPropagation(); approveLaporan('${l.uuid}', ${l.id})"
                        class="text-green-600 hover:underline text-xs">
                        Setujui
                    </button>

                    <button
                        onclick="event.stopPropagation(); rejectLaporan('${l.uuid}', ${l.id})"
                        class="text-red-600 hover:underline text-xs">
                        Tolak
                    </button>
                ` : `<span class="text-xs text-gray-400">—</span>`}
            </td>
        </tr>
    `).join('')
}

function csrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content')
}

// ================= APPROVE =================
window.approveLaporan = function(uuid, id) {
    fetch(`/wali-kelas/laporan/${uuid}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        // update status di memory
        const item = laporanData.find(l => l.id === id)
        if (item) item.status = data.status

        renderCurrentView()
        closeModal()
    })
}

// ================= REJECT =================
window.rejectLaporan = function(uuid, id) { 
    fetch(`/wali-kelas/laporan/${uuid}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
    })
    .then(res => res.json())
    .then(data => {
        const item = laporanData.find(l => l.id === id)
        if (item) item.status = data.status

        renderCurrentView()
        closeModal()
    })
}

function renderCards(data) {
    if (!data.length) {
        cardView.innerHTML = `
            <div class="col-span-full text-center text-gray-500 py-10">
                Tidak ada laporan
            </div>
        `
        return
    }

    cardView.innerHTML = data.map(l => `
        <div onclick="openModal(${l.id})"
            class="cursor-pointer p-5 rounded-xl border
                   bg-white dark:bg-[#161615]
                   border-[#19140035] dark:border-[#3E3E3A]
                   hover:shadow transition">

            <div class="flex justify-between items-start">
                <div>
                    <div class="text-xs text-gray-500 font-mono mb-1">
                        ${l.kode_laporan}
                    </div>

                    <div class="font-semibold text-base mb-1">
                        ${l.judul}
                    </div>

                    <div class="text-sm text-gray-600 mb-3">
                        ${l.kategori?.nama ?? '-'}
                    </div>

                    ${statusBadgeLaporan(l.status)}
                </div>

                <div class="text-sm text-gray-500 whitespace-nowrap">
                    ${formatDate(l.created_at)}
                </div>
            </div>
        </div>
    `).join('')
}

function formatDate(dateStr) {
    const d = new Date(dateStr)
    return d.toLocaleDateString('id-ID')
}

    // ================= BADGE =================
    function statusBadgeLaporan(status) {
        const map = {
            menunggu: 'bg-yellow-100 text-yellow-700',
            ditolak_wali: 'bg-red-100 text-red-700',
            diterima_wali: 'bg-blue-100 text-blue-700',
            selesai: 'bg-green-100 text-green-700',
        }

        const cls = map[status] || 'bg-gray-100 text-gray-700'

        return `
            <span class="px-2 py-1 rounded-full text-xs font-medium ${cls}">
                ${status.replaceAll('_', ' ')}
            </span>
        `
    }

    // ================= FILTER =================
    function getFilters() {
        const q = search.value
        const s = status.value
        const k = kategori.value
        const sortVal = sort.value

        let [sortBy, sortDir] = sortVal.split('|')

        return { q, s, k, sortBy, sortDir }
    }

    function buildQuery() {
        const { q, s, k, sortBy, sortDir } = getFilters()

        const params = new URLSearchParams({
            q: q || '',
            status: s || '',
            kategori_id: k || '',
            sort_by: sortBy,
            sort_dir: sortDir,
        })

        return params.toString()
    }

    // ================= EXPORT =================
    window.exportExcel = function() {
        window.location.href =
            `/wali-kelas/laporan/export/excel?${buildQuery()}`
    }

    window.exportPdf = function() {
        window.location.href =
            `/wali-kelas/laporan/export/pdf?${buildQuery()}`
    }

    function renderCurrentView() {
    if (currentView === 'table') {
        renderTable(laporanData)
    } else {
        renderCards(laporanData)
    }
}

    function loadData(url = null) {
    const endpoint = url || `?${buildQuery()}`

    fetch(endpoint, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(res => {
        laporanData = res.data
        renderCurrentView()

        // update pagination
        document.getElementById('pagination').innerHTML = res.links
    })
}

document.addEventListener('click', function(e) {
    if (e.target.closest('#pagination a')) {
        e.preventDefault()
        const url = e.target.closest('a').href
        loadData(url)
    }
})

    // ================= EVENTS =================
    search.addEventListener('keypress', e => {
        if (e.key === 'Enter') loadData()
    })

    status.addEventListener('change', loadData)
    kategori.addEventListener('change', loadData)
    sort.addEventListener('change', loadData)

    // ================= MODAL =================
    window.openModal = function(id) {
        const data = laporanData.find(l => l.id === id)
        if (!data) return

        const modal = document.getElementById('laporan-modal')
        const content = document.getElementById('modal-content')
        const attachBox = document.getElementById('modal-attachments')
        const actionBox = document.getElementById('modal-actions')

        content.innerHTML = `
            <div><strong>Kode:</strong> ${data.kode_laporan}</div>
            <div><strong>Siswa:</strong> ${data.pelapor.siswa_profile.nama}</div>
            <div><strong>Kelas:</strong> ${data.pelapor.siswa_profile.kelas.nama}</div>
            <div><strong>Judul:</strong> ${data.judul}</div>
            <div><strong>Status:</strong> ${statusBadgeLaporan(data.status)}</div>
            <div class="pt-2">
                <strong>Deskripsi:</strong>
                <p>${data.deskripsi}</p>
            </div>
        `

        attachBox.innerHTML = '<strong>Attachment:</strong>'
        if (data.attachments && data.attachments.length) {
            data.attachments.forEach(a => {
                attachBox.innerHTML += `
                    <div>
                        <a href="${a.url}" target="_blank"
                           class="text-indigo-600 hover:underline">
                           ${a.file_name}
                        </a>
                    </div>
                `
            })
        } else {
            attachBox.innerHTML += `<div class="text-gray-400">Tidak ada lampiran</div>`
        }

        actionBox.innerHTML = ''
        if (data.status === 'menunggu') {
            actionBox.innerHTML = `
                <button
                    onclick="approveLaporan('${data.uuid}', ${data.id})"
                    class="px-4 py-2 bg-green-600 text-white rounded text-sm">
                    Setujui
                </button>

                <button
                    onclick="rejectLaporan('${data.uuid}', ${data.id})"
                    class="px-4 py-2 bg-red-600 text-white rounded text-sm">
                    Tolak
                </button>
            `
        }

        modal.classList.remove('hidden')
        modal.classList.add('flex')
    }

    window.closeModal = function() {
        const modal = document.getElementById('laporan-modal')
        modal.classList.add('hidden')
        modal.classList.remove('flex')
    }

})
</script>

</x-app-layout>
