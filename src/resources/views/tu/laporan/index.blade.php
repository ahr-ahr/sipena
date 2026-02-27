<x-app-layout>

<div class="flex flex-col gap-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">
            Laporan TU
        </h1>

        <div class="flex flex-wrap gap-2">

            <input id="search"
                type="text"
                placeholder="Cari laporan..."
                class="rounded-sm px-3 py-2 text-sm border">

            <select id="filter-kategori"
                class="rounded-sm px-3 py-2 text-sm border">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriLaporan as $k)
                    <option value="{{ $k->id }}">
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>

            <select id="filter-status"
                class="rounded-sm px-3 py-2 text-sm border">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $s)
                    <option value="{{ $s->value }}">
                        {{ $s->label() }}
                    </option>
                @endforeach
            </select>

            <select id="sort"
                class="rounded-sm px-3 py-2 text-sm border">
                <option value="created_at|desc">Terbaru</option>
                <option value="created_at|asc">Terlama</option>
                <option value="judul|asc">Judul A–Z</option>
                <option value="judul|desc">Judul Z–A</option>
            </select>

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

            <button onclick="exportExcel()"
    class="px-3 py-2 text-sm border rounded">
    Excel
</button>

<button onclick="exportPdf()"
    class="px-3 py-2 text-sm border rounded">
    PDF
</button>

        </div>
    </div>
</div>

<div class="bg-white dark:bg-[#161615]
            border rounded-xl overflow-hidden">

    <table class="w-full text-sm">
        <thead class="border-b">
            <tr>
                <th class="px-4 py-3 text-left">Kode</th>
                <th class="px-4 py-3 text-left">Judul</th>
                <th class="px-4 py-3 text-left">Pelapor</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>

        <tbody id="laporan-body"></tbody>
    </table>

    <div id="pagination" class="p-4 flex justify-end gap-2"></div>
</div>

<div id="card-view"
     class="hidden mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
</div>

{{-- MODAL DETAIL --}}
<div id="detail-modal"
     class="fixed inset-0 hidden items-center justify-center
            bg-black/50"
     onclick="closeModal()">

    <div onclick="event.stopPropagation()"
         class="bg-white dark:bg-[#161615]
                p-6 rounded-xl w-full max-w-md space-y-3">

        <h3 class="font-semibold text-lg">Detail Laporan</h3>
        <div id="detail-content"></div>

        <button onclick="closeModal()"
            class="w-full border px-3 py-2 rounded">
            Tutup
        </button>
    </div>
</div>

<script>
let q = '', kategori = '', status = '', sortBy = 'created_at', sortDir = 'desc'
let currentView = 'table'

const tableView = document.querySelector('table').closest('div')
const cardView = document.getElementById('card-view')
const btnTable = document.getElementById('btn-table')
const btnCard = document.getElementById('btn-card')

let lastData = []

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

function setView(view) {
    currentView = view

    if (view === 'table') {
        tableView.classList.remove('hidden')
        cardView.classList.add('hidden')
        btnTable.classList.add('bg-gray-100')
        btnCard.classList.remove('bg-gray-100')
        renderTable(lastData)
    } else {
        tableView.classList.add('hidden')
        cardView.classList.remove('hidden')
        btnCard.classList.add('bg-gray-100')
        btnTable.classList.remove('bg-gray-100')
        renderCards(lastData)
    }
}

btnTable.addEventListener('click', () => setView('table'))
btnCard.addEventListener('click', () => setView('card'))

function loadData(page = 1) {
    const params = new URLSearchParams({
        q: q,
        kategori_id: kategori,
        status: status,
        sort_by: sortBy,
        sort_dir: sortDir,
        page: page
    })

    fetch(`/tu/laporan?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        lastData = res.data

        if (currentView === 'table') {
            renderTable(lastData)
        } else {
            renderCards(lastData)
        }

        renderPagination(res.meta)
    })
}

function renderTable(data) {
    const tbody = document.getElementById('laporan-body')
    tbody.innerHTML = ''

    data.forEach(l => {
        tbody.innerHTML += `
            <tr class="border-b">
                <td class="px-4 py-2">${l.kode_laporan}</td>
                <td>${l.judul}</td>
                <td>${l.pelapor?.siswa_profile?.nama ?? '-'}</td>
                <td>${statusBadge(l.status)}</td>
                <td class="px-4 py-2 text-right space-x-2">
                    <button onclick="openDetail('${l.uuid}')"
                        class="text-indigo-600 text-xs">
                        Detail
                    </button>

                    ${l.status === 'disetujui_sarpras' ? `
                        <button onclick="setujui('${l.uuid}')"
                            class="text-green-600 text-xs">
                            Setujui
                        </button>

                        <button onclick="tolak('${l.uuid}')"
                            class="text-red-600 text-xs">
                            Tolak
                        </button>
                    ` : ''}
                </td>
            </tr>
        `
    })
}

function renderCards(data) {
    const card = document.getElementById('card-view')
    card.innerHTML = ''

    if (!data.length) {
        card.innerHTML = `
            <div class="col-span-full text-center text-gray-500 py-10">
                Tidak ada laporan
            </div>
        `
        return
    }

    data.forEach(l => {
        card.innerHTML += `
            <div class="p-4 rounded-xl border space-y-2">
                <div class="text-xs font-mono text-gray-500">
                    ${l.kode_laporan}
                </div>

                <div class="font-semibold">
                    ${l.judul}
                </div>

                <div class="text-sm text-gray-600">
                    ${l.pelapor?.email ?? '-'}
                </div>

                <div class="text-xs">
                    ${statusBadge(l.status)}
                </div>

                <div class="flex gap-2 pt-2">
                    <button onclick="openDetail('${l.uuid}')"
                        class="text-indigo-600 text-xs">
                        Detail
                    </button>

                    ${l.status === 'disetujui_sarpras' ? `
                        <button onclick="setujui('${l.uuid}')"
                            class="text-green-600 text-xs">
                            Setujui
                        </button>

                        <button onclick="tolak('${l.uuid}')"
                            class="text-red-600 text-xs">
                            Tolak
                        </button>
                    ` : ''}
                </div>
            </div>
        `
    })
}

function renderPagination(meta) {
    const p = document.getElementById('pagination')
    p.innerHTML = ''

    for (let i = 1; i <= meta.last_page; i++) {
        p.innerHTML += `
            <button onclick="loadData(${i})"
                class="px-3 py-1 border rounded">
                ${i}
            </button>
        `
    }
}

function openDetail(uuid) {
    fetch(`/tu/laporan/${uuid}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const l = res.data

        // ================= ATTACHMENTS =================
        let attachmentsHtml = `
            <div>
                <strong>Lampiran:</strong>
                <div class="text-gray-400 text-sm mt-1">
                    Tidak ada lampiran
                </div>
            </div>
        `

        if (l.attachments && l.attachments.length) {
            attachmentsHtml = `
                <div>
                    <strong>Lampiran:</strong>
                    <ul class="mt-1 space-y-1">
                        ${l.attachments.map(a => `
                            <li>
                                <a href="${a.url}"
                                   target="_blank"
                                   class="text-indigo-600 hover:underline">
                                    ${a.file_name}
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `
        }

        // ================= ANGGARAN =================
        let anggaranHtml = `
            <div>
                <strong>Anggaran:</strong>
                <div class="text-gray-400 text-sm mt-1">
                    Belum ada anggaran
                </div>
            </div>
        `

        if (l.anggaran) {
            let items = `
                <div class="text-gray-400 text-sm">
                    Tidak ada item
                </div>
            `

            if (l.anggaran.details && l.anggaran.details.length) {
                items = `
                    <ul class="mt-1 space-y-1">
                        ${l.anggaran.details.map(d => `
                            <li class="flex justify-between text-sm">
                                <span>${d.nama_item} (${d.qty}x)</span>
                                <span>Rp ${Number(d.subtotal).toLocaleString('id-ID')}</span>
                            </li>
                        `).join('')}
                    </ul>
                `
            }

            anggaranHtml = `
                <div class="pt-2">
                    <strong>Anggaran:</strong>
                    <div class="text-sm mt-1">
                        <div>Kode: ${l.anggaran.kode_anggaran}</div>
                        <div>Total: Rp ${Number(l.anggaran.total_biaya).toLocaleString('id-ID')}</div>
                    </div>
                    ${items}
                </div>
            `
        }

        // ================= FINAL HTML =================
        document.getElementById('detail-content').innerHTML = `
            <div><strong>Kode:</strong> ${l.kode_laporan}</div>
            <div><strong>Judul:</strong> ${l.judul}</div>
            <div><strong>Pelapor:</strong> ${l.pelapor?.siswa_profile?.nama ?? '-'}</div>
            <div><strong>Kelas:</strong> ${l.pelapor?.siswa_profile?.kelas.nama ?? '-'}</div>
            <div><strong>Status:</strong> ${statusBadge(l.status)}</div>

            <div class="pt-2">
                <strong>Deskripsi:</strong>
                <p>${l.deskripsi}</p>
            </div>

            ${attachmentsHtml}
            ${anggaranHtml}
        `

        const m = document.getElementById('detail-modal')
        m.classList.remove('hidden')
        m.classList.add('flex')
    })
}

function closeModal() {
    const m = document.getElementById('detail-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}

function statusBadge(status) {
    const map = {
        menunggu: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
        diterima_wali: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        diproses_sarpras: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        disetujui_sarpras: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
        selesai: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    }

    const cls = map[status] ||
        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'

    return `
        <span class="px-2 py-1 rounded-full text-xs font-medium ${cls}">
            ${status.replaceAll('_', ' ')}
        </span>
    `
}

function setujui(uuid) {
    fetch(`/tu/laporan/${uuid}/setujui`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(() => loadData())
}

function tolak(uuid) {
    fetch(`/tu/laporan/${uuid}/tolak`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(() => loadData())
}

function buildExportUrl(base) {
    const params = new URLSearchParams({
        q: q,
        kategori_id: kategori,
        status: status,
        sort_by: sortBy,
        sort_dir: sortDir,
    })

    return `${base}?${params.toString()}`
}

function exportExcel() {
    window.location = buildExportUrl('/tu/laporan/export/excel')
}

function exportPdf() {
    window.location = buildExportUrl('/tu/laporan/export/pdf')
}

loadData()
</script>

</x-app-layout>
