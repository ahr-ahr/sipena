<x-app-layout>

{{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Daftar Laporan
        </h1>

        <div class="flex flex-wrap gap-2">

            {{-- SEARCH --}}
            <input id="search" type="text" placeholder="Cari judul atau kode laporan…"
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]
                       placeholder:text-[#706f6c] dark:placeholder:text-[#A1A09A]
                       focus:outline-none focus:ring-0">

            <select id="filter-status" class="...">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $status)
                    <option value="{{ $status->value }}">
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <div class="relative">
            <select id="filter-kategori"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                        bg-white dark:bg-[#161615]
                        text-[#1b1b18] dark:text-[#EDEDEC]
                        border border-[#19140035] dark:border-[#3E3E3A]
                        focus:outline-none">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriLaporan as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
            </select>
            </div>

            <div class="relative">
            <select id="sort"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                        bg-white dark:bg-[#161615]
                        text-[#1b1b18] dark:text-[#EDEDEC]
                        border border-[#19140035] dark:border-[#3E3E3A]
                        focus:outline-none">
                <option value="created_at|desc">Terbaru</option>
                <option value="created_at|asc">Terlama</option>
                <option value="judul|asc">Judul A-Z</option>
                <option value="judul|desc">Judul Z-A</option>
            </select>
            </div>

            <button onclick="exportExcel()"
                class="px-3 py-2 text-sm rounded-sm
                    border border-[#19140035] dark:border-[#3E3E3A]
                    hover:bg-black/5 dark:hover:bg-white/10 transition">
                Excel
            </button>

            <button onclick="exportPdf()"
                class="px-3 py-2 text-sm rounded-sm
                    border border-[#19140035] dark:border-[#3E3E3A]
                    hover:bg-black/5 dark:hover:bg-white/10 transition">
                PDF
            </button>

            <button onclick="setViewMode('table')"
                class="px-3 py-2 text-sm rounded-sm border
                    border-[#19140035] dark:border-[#3E3E3A]">
                Table
            </button>

            <button onclick="setViewMode('card')"
                class="px-3 py-2 text-sm rounded-sm border
                    border-[#19140035] dark:border-[#3E3E3A]">
                Card
            </button>

        </div>
    </div>
</div>

{{-- ================= TABLE ================= --}}
<div class="mt-6 p-6 rounded-xl
            bg-white dark:bg-[#161615]
            border border-[#19140035] dark:border-[#3E3E3A]">

    <div id="table-view">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#19140035] dark:border-[#3E3E3A]
                        text-[#706f6c] dark:text-[#A1A09A]">
                    <th class="text-left py-2">Kode</th>
                    <th class="text-left py-2">Judul</th>
                    <th class="text-left py-2">Kategori</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-left py-2">Tanggal</th>
                </tr>
            </thead>
            <tbody id="laporan-body"
                class="text-[#1b1b18] dark:text-[#EDEDEC]">
            </tbody>
        </table>
    </div>

    <div id="card-view" class="hidden space-y-3"></div>

    <div id="pagination" class="mt-4 flex gap-2 justify-end"></div>
</div>

{{-- ================= MODALS ================= --}}

<x-create-laporan-modal
    :laporan-categories="$kategoriLaporan ?? collect()"
/>

<div id="detail-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeDetailModal()">

    <div
        onclick="event.stopPropagation()"
        class="w-full max-w-sm rounded-2xl
               bg-white dark:bg-[#161615]
               p-6 shadow-xl space-y-4">

        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Detail Laporan
        </h3>

        <div id="detail-content"
             class="space-y-3 text-sm
                    text-[#1b1b18] dark:text-[#EDEDEC]">
        </div>

        <button
            onclick="closeDetailModal()"
            class="w-full mt-2 text-sm
                   px-4 py-3 rounded-lg
                   border border-[#19140035] dark:border-[#3E3E3A]
                   hover:bg-black/5 dark:hover:bg-white/10 transition">
            Tutup
        </button>

    </div>
</div>

{{-- ================= EDIT LAPORAN MODAL ================= --}}
<div id="edit-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeEditModal()">

    <div
        onclick="event.stopPropagation()"
        class="w-full max-w-sm rounded-2xl
               bg-white dark:bg-[#161615]
               p-6 shadow-xl space-y-4">

        <h3 class="text-lg font-semibold">
            Edit Laporan
        </h3>

        <form id="edit-form" onsubmit="submitEditLaporan(event)" class="space-y-3">
            <input type="hidden" id="edit-uuid">

            <div>
                <label class="text-sm">Kategori</label>
                <select id="edit-kategori"
                    class="w-full rounded-sm px-3 py-2 text-sm
                        bg-white dark:bg-[#161615]
                        border border-[#19140035] dark:border-[#3E3E3A]">
                    @foreach ($kategoriLaporan as $k)
                        <option value="{{ $k->id }}">
                            {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm">Judul</label>
                <input id="edit-judul"
                       class="w-full rounded-sm px-3 py-2 text-sm
                              bg-white dark:bg-[#161615]
                              border border-[#19140035] dark:border-[#3E3E3A]">
            </div>

            <div>
                <label class="text-sm">Deskripsi</label>
                <textarea id="edit-deskripsi"
                          class="w-full rounded-sm px-3 py-2 text-sm
                                 bg-white dark:bg-[#161615]
                                 border border-[#19140035] dark:border-[#3E3E3A]"></textarea>
            </div>

            <div>
    <label class="text-sm">Attachment</label>
    <input id="edit-attachments"
           type="file"
           multiple
           class="w-full rounded-sm px-3 py-2 text-sm
                  bg-white dark:bg-[#161615]
                  border border-[#19140035] dark:border-[#3E3E3A]">
</div>


            <button type="submit"
                class="w-full px-4 py-3 rounded-lg
                       bg-black text-white
                       dark:bg-white dark:text-black">
                Simpan Perubahan
            </button>
        </form>

        <button onclick="closeEditModal()"
            class="w-full text-sm text-[#706f6c]">
            Batal
        </button>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>

let query='', status='', kategori='', sortBy='created_at', sortDir='desc'

document.getElementById('search').oninput = e => {
    query = e.target.value
    loadData()
}
document.getElementById('filter-status').onchange = e => {
    status = e.target.value
    loadData()
}
document.getElementById('filter-kategori').onchange = e => {
    kategori = e.target.value
    loadData()
}
document.getElementById('sort').onchange = e => {
    [sortBy, sortDir] = e.target.value.split('|')
    loadData()
}

function loadData(page = 1) {
    fetch(`/laporan?q=${query}&status=${status}&kategori_id=${kategori}&sort_by=${sortBy}&sort_dir=${sortDir}&page=${page}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        renderTable(res.data)
        renderCards(res.data)
        renderPagination(res)
    })
}

function renderTable(data) {
    const tbody = document.getElementById('laporan-body')
    tbody.innerHTML = ''

    data.forEach(l => {
        tbody.innerHTML += `
            <tr onclick="openDetail('${l.uuid}')"
                class="border-b cursor-pointer hover:bg-black/5 dark:hover:bg-white/5">
                <td class="py-2">${l.kode_laporan}</td>
                <td class="py-2">${l.judul}</td>
                <td class="py-2">${l.kategori?.nama ?? '-'}</td>
                <td class="py-2">${statusBadgeLaporan(l.status)}</td>
                <td class="py-2">${new Date(l.created_at).toLocaleDateString()}</td>
            </tr>`
    })
}

function renderCards(data) {
    const box = document.getElementById('card-view')
    box.innerHTML = ''

    data.forEach(l => {
        box.innerHTML += `
            <div onclick="openDetail('${l.uuid}')"
                 class="p-4 rounded-xl border
                        border-[#19140035] dark:border-[#3E3E3A]
                        bg-white dark:bg-[#161615]
                        cursor-pointer hover:bg-black/5 dark:hover:bg-white/5">

                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    ${l.kode_laporan}
                </div>

                <div class="font-semibold mt-1">
                    ${l.judul}
                </div>

                <div class="text-sm mt-1">
                    ${l.kategori?.nama ?? '-'}
                </div>

                <div class="flex justify-between items-center mt-3 text-xs">
                    ${statusBadgeLaporan(l.status)}
                    <span>${new Date(l.created_at).toLocaleDateString()}</span>
                </div>
            </div>
        `
    })
}

function renderPagination(res) {
    const p = document.getElementById('pagination')
    p.innerHTML = ''
    for (let i=1;i<=res.last_page;i++) {
        p.innerHTML += `
            <button onclick="loadData(${i})"
                class="px-3 py-1 rounded-sm border
                       border-[#19140035] dark:border-[#3E3E3A]
                       ${i===res.current_page
                        ? 'bg-[#1b1b18] text-white dark:bg-white dark:text-black'
                        : 'hover:bg-black/5 dark:hover:bg-white/10'}">
                ${i}
            </button>`
    }
}

let viewMode = localStorage.getItem('laporan_view') || 'table'

function setViewMode(mode) {
    viewMode = mode
    localStorage.setItem('laporan_view', mode)
    renderView()
}

function renderView() {
    document.getElementById('table-view')
        .classList.toggle('hidden', viewMode !== 'table')

    document.getElementById('card-view')
        .classList.toggle('hidden', viewMode !== 'card')
}

renderView()

function buildExportQuery() {
    const params = new URLSearchParams({
        q: query,
        status: status,
        kategori_id: kategori,
        sort_by: sortBy,
        sort_dir: sortDir,
    })

    return params.toString()
}

function exportExcel() {
    window.location.href =
        `/laporan/export/excel?${buildExportQuery()}`
}

function exportPdf() {
    window.location.href =
        `/laporan/export/pdf?${buildExportQuery()}`
}

function openDetail(uuid) {
    fetch(`/laporan/${uuid}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const l = res.data
        console.log('STATUS:', l.status)
        const box = document.getElementById('detail-content')

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

box.innerHTML = `
    <div><strong>Kode:</strong> ${l.kode_laporan}</div>

    <div data-judul="${l.judul}">
        <strong>Judul:</strong>
        <span>${l.judul}</span>
    </div>

    <div data-kategori-id="${l.kategori_id}">
        <strong>Kategori:</strong>
        <span>${l.kategori?.nama ?? '-'}</span>
    </div>

    <div data-status="${l.status.value}">
        <strong>Status:</strong>
        ${statusBadgeLaporan(l.status)}
    </div>

    <div>
        <strong>Ditangani oleh:</strong>
        ${l.current_role?.label ?? '-'}
    </div>

    <div data-deskripsi="${l.deskripsi}" class="pt-2">
        <strong>Deskripsi:</strong>
        <p class="mt-1 text-[#706f6c] dark:text-[#A1A09A]">
            ${l.deskripsi}
        </p>
    </div>

    ${attachmentsHtml}
`

        if (res.can_update) {
            box.innerHTML += `
                <button
                    onclick="openEditModal('${l.uuid}')"
                    class="w-full mt-3 px-4 py-3 rounded-lg
                           bg-black text-white
                           dark:bg-white dark:text-black">
                    Edit Laporan
                </button>
            `
        }

        const modal = document.getElementById('detail-modal')
        modal.classList.remove('hidden')
        modal.classList.add('flex')
    })
}

function closeDetailModal() {
    const m = document.getElementById('detail-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}

async function submitEditLaporan(e) {
    e.preventDefault()

    const uuid = document.getElementById('edit-uuid').value

    const formData = new FormData()
    formData.append('judul',
        document.getElementById('edit-judul').value)
    formData.append('kategori_id',
        document.getElementById('edit-kategori').value)
    formData.append('deskripsi',
        document.getElementById('edit-deskripsi').value)

    const files =
        document.getElementById('edit-attachments').files

    for (let i = 0; i < files.length; i++) {
        formData.append('attachments[]', files[i])
    }

    try {
        const res = await fetch(`/laporan/${uuid}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT',
            },
            body: formData,
        })

        const data = await res.json()

        if (!res.ok) {
            console.error(data)
            alert('Gagal update laporan')
            return
        }

        closeEditModal()
        closeDetailModal()
        loadData()

    } catch (err) {
        console.error(err)
        alert('Terjadi kesalahan')
    }
}

function openEditModal(uuid) {
    const content = document.getElementById('detail-content')

    document.getElementById('edit-uuid').value = uuid

    document.getElementById('edit-judul').value =
        content.querySelector('[data-judul]')?.dataset.judul || ''

    document.getElementById('edit-deskripsi').value =
        content.querySelector('[data-deskripsi]')?.dataset.deskripsi || ''

    const kategoriId =
        content.querySelector('[data-kategori-id]')?.dataset.kategoriId

    if (kategoriId) {
        document.getElementById('edit-kategori').value = kategoriId
    }

    document.getElementById('edit-modal').classList.remove('hidden')
    document.getElementById('edit-modal').classList.add('flex')
}

function closeEditModal() {
    const m = document.getElementById('edit-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}

if (window.innerWidth < 768) {
    setViewMode('card')
} else {
    renderView()
}

function statusBadgeLaporan(status) {
    if (!status) return '';

    return `
        <span class="px-2 py-1 text-xs rounded
            bg-${status.color}-100
            text-${status.color}-700">
            ${status.label}
        </span>
    `;
}

function showAiWarning(message) {
    document.getElementById('ai-warning-text').innerText = message
    const m = document.getElementById('ai-warning-modal')
    m.classList.remove('hidden')
    m.classList.add('flex')
}

function closeAiWarning() {
    const m = document.getElementById('ai-warning-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}

loadData()
</script>

</x-app-layout>
