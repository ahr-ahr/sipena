<x-app-layout>

{{-- HEADER --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Laporan Kesiswaan
        </h1>

        <div class="flex flex-wrap gap-2">

            <input id="search" type="text"
                placeholder="Cari laporan..."
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">

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

            <select id="sort"
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="created_at|desc">Terbaru</option>
                <option value="created_at|asc">Terlama</option>
                <option value="judul|asc">Judul A–Z</option>
                <option value="judul|desc">Judul Z–A</option>
            </select>

        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="mt-6 p-6 rounded-xl
            bg-white dark:bg-[#161615]
            border border-[#19140035] dark:border-[#3E3E3A]
            overflow-hidden">

    <table class="w-full text-sm">
        <thead>
            <tr class="border-b
                       border-[#19140035] dark:border-[#3E3E3A]
                       text-[#706f6c] dark:text-[#A1A09A]">
                <th class="text-left py-3 px-3">Kode</th>
                <th class="text-left py-3 px-3">Judul</th>
                <th class="text-left py-3 px-3">Pelapor</th>
                <th class="text-left py-3 px-3">Status</th>
                <th class="text-right py-3 px-3">Aksi</th>
            </tr>
        </thead>
        <tbody id="laporan-body"
               class="text-[#1b1b18] dark:text-[#EDEDEC]">
        </tbody>
    </table>

    <div id="pagination"
         class="mt-4 flex gap-2 justify-end"></div>
</div>

{{-- MODAL DETAIL --}}
<div id="detail-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeModal()">

    <div onclick="event.stopPropagation()"
         class="w-full max-w-sm rounded-2xl
                bg-white dark:bg-[#161615]
                p-6 shadow-xl space-y-4">

        <h3 class="text-lg font-semibold">
            Detail Laporan
        </h3>

        <div id="detail-content" class="text-sm space-y-2"></div>

        <button onclick="closeModal()"
            class="w-full px-4 py-3 rounded-lg border">
            Tutup
        </button>
    </div>
</div>

<script>
let q = '', kategori = '', status = '', sortBy = 'created_at', sortDir = 'desc'

document.getElementById('search').oninput = e => {
    q = e.target.value
    loadData()
}

document.getElementById('filter-kategori').onchange = e => {
    kategori = e.target.value
    loadData()
}

document.getElementById('filter-status').onchange = e => {
    status = e.target.value
    loadData()
}

document.getElementById('sort').onchange = e => {
    const [by, dir] = e.target.value.split('|')
    sortBy = by
    sortDir = dir
    loadData()
}

function loadData(page = 1) {
    const params = new URLSearchParams({
        q: q,
        kategori_id: kategori,
        status: status,
        sort_by: sortBy,
        sort_dir: sortDir,
        page: page
    })

    fetch(`/kesiswaan/laporan?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        renderTable(res.data)
        renderPagination(res.meta)
    })
}

function statusBadge(status) {
    const map = {
        menunggu: 'bg-yellow-100 text-yellow-700',
        diproses: 'bg-blue-100 text-blue-700',
        ditolak: 'bg-red-100 text-red-700',
        disetujui: 'bg-green-100 text-green-700',
        selesai: 'bg-emerald-100 text-emerald-700',
    }

    const cls = map[status] || 'bg-gray-100 text-gray-700'

    return `
        <span class="px-2 py-1 rounded-full text-xs font-medium ${cls}">
            ${status}
        </span>
    `
}

function renderActions(l) {
    let html = `
        <button onclick="openDetail('${l.uuid}')"
            class="px-2 py-1 text-xs border rounded">
            Detail
        </button>
    `

    if (l.status === 'menunggu') {
        html += `
            <button onclick="tindak('${l.uuid}')"
                class="px-2 py-1 text-xs border rounded text-green-600">
                Tindak
            </button>

            <button onclick="teruskan('${l.uuid}')"
                class="px-2 py-1 text-xs border rounded text-blue-600">
                Ke Sarpras
            </button>

            <button onclick="tolak('${l.uuid}')"
                class="px-2 py-1 text-xs border rounded text-red-600">
                Tolak
            </button>
        `
    }

    return html
}

function renderTable(data) {
    const tbody = document.getElementById('laporan-body')
    tbody.innerHTML = ''

    data.forEach(l => {
        tbody.innerHTML += `
            <tr class="border-b">
                <td class="py-3 px-3 font-mono text-xs">
                    ${l.kode_laporan}
                </td>

                <td class="py-3 px-3 font-medium">
                    ${l.judul}
                </td>

                <td class="py-3 px-3">
                    ${l.pelapor?.siswa_profile?.nama ?? '-'}
                </td>

                <td class="py-3 px-3">
                    ${statusBadge(l.status)}
                </td>

                <td class="py-3 px-3 text-right space-x-2">
                    ${renderActions(l)}
                </td>
            </tr>
        `
    })
}

function renderPagination(meta) {
    const p = document.getElementById('pagination')
    p.innerHTML = ''

    for (let i = 1; i <= meta.last_page; i++) {
        p.innerHTML += `
            <button onclick="loadData(${i})"
                class="px-3 py-1 border rounded
                       ${i === meta.current_page ? 'bg-black text-white' : ''}">
                ${i}
            </button>`
    }
}

function openDetail(uuid) {
    fetch(`/kesiswaan/laporan/${uuid}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const l = res.data

        document.getElementById('detail-content').innerHTML = `
            <div><strong>Kode:</strong> ${l.kode_laporan}</div>
            <div><strong>Judul:</strong> ${l.judul}</div>
            <div><strong>Status:</strong> ${l.status}</div>
            <div class="pt-2">
                <strong>Deskripsi:</strong>
                <p>${l.deskripsi}</p>
            </div>
        `

        document.getElementById('detail-modal')
            .classList.remove('hidden')
        document.getElementById('detail-modal')
            .classList.add('flex')
    })
}

function closeModal() {
    const m = document.getElementById('detail-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}

function tindak(id) {
    fetch(`/kesiswaan/laporan/${id}/tindak-langsung`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => loadData())
}

function teruskan(id) {
    fetch(`/kesiswaan/laporan/${id}/teruskan-sarpras`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => loadData())
}

function tolak(id) {
    fetch(`/kesiswaan/laporan/${id}/tolak`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => loadData())
}

loadData()
</script>

</x-app-layout>
