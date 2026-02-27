<x-app-layout>

{{-- HEADER --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Laporan Sarpras
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

{{-- TABLE --}}
<div id="table-view" class="mt-6 p-6 rounded-xl
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
<div id="card-view"
     class="hidden mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
</div>

{{-- MODAL --}}
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

{{-- MODAL ANGGARAN --}}
<div id="anggaran-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeAnggaranModal()">

    <div onclick="event.stopPropagation()"
         class="w-full max-w-lg rounded-2xl
                bg-white dark:bg-[#161615]
                p-5 shadow-xl space-y-4">

        <h3 class="text-lg font-semibold">
            Buat Anggaran
        </h3>

        <form onsubmit="submitAnggaran(event)" class="space-y-3">
            <input type="hidden" id="anggaran-laporan-id">

            <div id="anggaran-items" class="space-y-2"></div>

            <button type="button"
                onclick="addItemRow()"
                class="text-xs px-2 py-1 border rounded">
                + Tambah Item
            </button>

            <div class="text-right text-sm font-semibold">
                Total:
                <span id="anggaran-total">0</span>
            </div>

            <div class="flex gap-2">
                <button type="button"
                    onclick="closeAnggaranModal()"
                    class="flex-1 px-3 py-2 text-sm border rounded">
                    Batal
                </button>

                <button
                    class="flex-1 px-3 py-2 text-sm rounded
                           bg-black text-white
                           dark:bg-white dark:text-black">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let q = '', kategori = '', status = '', sortBy = 'created_at', sortDir = 'desc'
let currentView = 'table'

const tableView = document.getElementById('table-view')
const cardView = document.getElementById('card-view')
const btnTable = document.getElementById('btn-table')
const btnCard = document.getElementById('btn-card')

let lastData = []

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

    fetch(`/sarpras/laporan?${params.toString()}`, {
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

function renderActions(l) {
    let html = `
        <button onclick="openDetail('${l.uuid}')"
            class="px-2 py-1 text-xs rounded
                   border border-indigo-500
                   text-indigo-600
                   hover:bg-indigo-50
                   dark:hover:bg-indigo-900/30">
            Detail
        </button>
    `

    // jika status diterima wali → boleh proses
    if (l.status === 'diterima_wali') {
        html += `
            <button onclick="setProses('${l.uuid}')"
                class="px-2 py-1 text-xs rounded
                       border border-blue-500
                       text-blue-600
                       hover:bg-blue-50
                       dark:hover:bg-blue-900/30">
                Proses
            </button>
        `
    }

    // jika status sudah diproses → boleh setujui
    if (l.status === 'diproses_sarpras') {
        html += `
            <button onclick="openAnggaranModal('${l.uuid}')"
                class="px-2 py-1 text-xs rounded
                       border border-green-500
                       text-green-600
                       hover:bg-green-50
                       dark:hover:bg-green-900/30">
                Setujui
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
            <tr class="border-b
                       border-[#19140035] dark:border-[#3E3E3A]
                       hover:bg-black/5 dark:hover:bg-white/5">

                <td class="py-3 px-3 font-mono text-xs">
                    ${l.kode_laporan}
                </td>

                <td class="py-3 px-3 font-medium">
                    ${l.judul}
                </td>

                <td class="py-3 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
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
            <div class="p-4 rounded-xl border
                        bg-white dark:bg-[#161615]
                        border-[#19140035] dark:border-[#3E3E3A]
                        space-y-2">

                <div class="text-xs font-mono text-gray-500">
                    ${l.kode_laporan}
                </div>

                <div class="font-semibold">
                    ${l.judul}
                </div>

                <div class="text-sm text-gray-600">
                    ${l.pelapor?.siswa_profile?.nama ?? '-'}
                </div>

                <div>
                    ${statusBadge(l.status)}
                </div>

                <div class="flex gap-2 pt-2">
                    ${renderCardActions(l)}
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
                class="px-3 py-1 border rounded
                       ${i === meta.current_page ? 'bg-black text-white' : ''}">
                ${i}
            </button>`
    }
}

function renderCardActions(l) {
    let html = `
        <button onclick="openDetail('${l.uuid}')"
            class="text-xs text-indigo-600">
            Detail
        </button>
    `

    if (l.status === 'diterima_wali') {
        html += `
            <button onclick="setProses('${l.uuid}')"
                class="text-xs text-blue-600">
                Proses
            </button>
        `
    }

    if (l.status === 'diproses_sarpras') {
        html += `
            <button onclick="openAnggaranModal('${l.uuid}')"
                class="text-xs text-green-600">
                Setujui
            </button>
        `
    }

    return html
}

function openDetail(uuid) {
    fetch(`/sarpras/laporan/${uuid}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const l = res.data

        let attachmentsHtml = `
            <div>
                <strong>Attachment:</strong>
                <div class="text-gray-400 text-sm mt-1">
                    Tidak ada lampiran
                </div>
            </div>
        `

        if (l.attachments && l.attachments.length) {
            attachmentsHtml = `
                <div>
                    <strong>Attachment:</strong>
                    <ul class="mt-1 space-y-1">
                        ${l.attachments.map(a => `
                            <li>
                                <a href="/storage/${a.file_path}"
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

        document.getElementById('detail-content').innerHTML = `
            <div><strong>Kode:</strong> ${l.kode_laporan}</div>
            <div><strong>Judul:</strong> ${l.judul}</div>
            <div><strong>Pelapor:</strong>
                ${l.pelapor?.siswa_profile?.nama ?? '-'}
            </div>
            <div><strong>Kelas:</strong>
                ${l.pelapor?.siswa_profile?.kelas.nama ?? '-'}
            </div>
            <div><strong>Kategori:</strong> ${l.kategori?.nama ?? '-'}</div>
            <div><strong>Status:</strong> ${statusBadge(l.status)}</div>

            <div class="pt-2">
                <strong>Deskripsi:</strong>
                <p class="mt-1 text-gray-600 dark:text-gray-300">
                    ${l.deskripsi}
                </p>
            </div>

            ${attachmentsHtml}
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

function setProses(id) {
    fetch(`/sarpras/laporan/${id}/proses`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(() => loadData())
}

async function submitAnggaran(e) {
    e.preventDefault()

    const uuid = document.getElementById('anggaran-laporan-id').value

    const items = []

    document.querySelectorAll('.item-row').forEach(row => {
        const nama = row.querySelector('.item-nama').value
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0
        const harga = parseFloat(row.querySelector('.item-harga').value) || 0

        if (nama && qty > 0 && harga > 0) {
            items.push({
                nama_item: nama,
                qty: qty,
                harga_satuan: harga
            })
        }
    })

    if (items.length === 0) {
        alert('Minimal satu item anggaran')
        return
    }

    try {
        const res = await fetch(`/sarpras/laporan/${uuid}/selesai`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                items: items
            })
        })

        const data = await res.json()

        if (!res.ok) {
            console.error(data)
            alert('Gagal menyimpan anggaran')
            return
        }

        closeAnggaranModal()
        loadData()

    } catch (err) {
        console.error(err)
        alert('Terjadi kesalahan')
    }
}

function openAnggaranModal(uuid) {
    document.getElementById('anggaran-laporan-id').value = uuid

    const box = document.getElementById('anggaran-items')
    box.innerHTML = ''
    addItemRow()

    const modal = document.getElementById('anggaran-modal')
    modal.classList.remove('hidden')
    modal.classList.add('flex')
}

function closeAnggaranModal() {
    const m = document.getElementById('anggaran-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}

function addItemRow() {
    const box = document.getElementById('anggaran-items')

    const row = document.createElement('div')
    row.className = 'grid grid-cols-4 gap-2 item-row'

    row.innerHTML = `
        <input type="text" placeholder="Nama item"
            class="px-2 py-1 border rounded item-nama">

        <input type="number" placeholder="Qty"
            class="px-2 py-1 border rounded item-qty"
            oninput="calcTotal()">

        <input type="number" placeholder="Harga"
            class="px-2 py-1 border rounded item-harga"
            oninput="calcTotal()">

        <button type="button"
            onclick="this.parentElement.remove(); calcTotal()"
            class="text-red-600 text-xs">
            Hapus
        </button>
    `

    box.appendChild(row)
}

function calcTotal() {
    let total = 0

    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0
        const harga = parseFloat(row.querySelector('.item-harga').value) || 0
        total += qty * harga
    })

    document.getElementById('anggaran-total').textContent =
        total.toLocaleString('id-ID')
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
    window.location = buildExportUrl('/sarpras/laporan/export/excel')
}

function exportPdf() {
    window.location = buildExportUrl('/sarpras/laporan/export/pdf')
}

loadData()
</script>

</x-app-layout>
