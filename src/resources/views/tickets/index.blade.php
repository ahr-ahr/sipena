<x-app-layout>

{{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Daftar Tiket
        </h1>

        <div class="flex flex-wrap gap-2">

            {{-- SEARCH --}}
            <input id="search" type="text"
                placeholder="Cari judul atau nomor tiket…"
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">

            {{-- FILTER STATUS --}}
            <div class="relative">
            <select id="filter-status"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                        bg-white dark:bg-[#161615]
                        text-[#1b1b18] dark:text-[#EDEDEC]
                        border border-[#19140035] dark:border-[#3E3E3A]
                        focus:outline-none">
                <option value="">Semua Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="closed">Closed</option>
                <option value="rejected">Rejected</option>
                <option value="resolved">Resolved</option>
                <option value="waiting">Waiting</option>
            </select>
            </div>

            <div class="relative">
                <select id="filter-category"
                    class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                        bg-white dark:bg-[#161615]
                        text-[#1b1b18] dark:text-[#EDEDEC]
                        border border-[#19140035] dark:border-[#3E3E3A]
                        focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriLaporan as $c)
                        <option value="{{ $c->id }}">{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- SORT --}}
            <div class="relative">
            <select id="sort"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                        bg-white dark:bg-[#161615]
                        text-[#1b1b18] dark:text-[#EDEDEC]
                        border border-[#19140035] dark:border-[#3E3E3A]
                        focus:outline-none">
                <option value="created_at|desc">Terbaru</option>
                <option value="created_at|asc">Terlama</option>
                <option value="title|asc">Judul A–Z</option>
                <option value="title|desc">Judul Z–A</option>
                <option value="priority|asc">Prioritas Rendah → Tinggi</option>
                <option value="priority|desc">Prioritas Tinggi → Rendah</option>
            </select>
            </div>

            <button onclick="exportExcel()"
                class="px-3 py-2 text-sm rounded-sm border">
                Excel
            </button>

            <button onclick="exportPdf()"
                class="px-3 py-2 text-sm rounded-sm border">
                PDF
            </button>

            {{-- VIEW MODE --}}
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

<div class="mt-6 p-6 rounded-xl
            bg-white dark:bg-[#161615]
            border border-[#19140035] dark:border-[#3E3E3A]">

    <div id="table-view">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-[#706f6c] dark:text-[#A1A09A]">
                    <th class="text-left py-2">No Tiket</th>
                    <th class="text-left py-2">Judul</th>
                    <th class="text-left py-2">Prioritas</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-left py-2">Tanggal</th>
                </tr>
            </thead>
            <tbody id="ticket-body"></tbody>
        </table>
    </div>

    <div id="card-view" class="hidden space-y-3"></div>

    <div id="pagination" class="mt-4 flex gap-2 justify-end"></div>
</div>

<x-create-laporan-modal
    :laporan-categories="$kategoriLaporan ?? collect()"
/>

{{-- ================= MODAL DETAIL TIKET ================= --}}
<div id="ticket-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeModal()">

    <div
        onclick="event.stopPropagation()"
        class="w-full max-w-sm rounded-2xl
               bg-white dark:bg-[#161615]
               p-6 shadow-xl space-y-4">

        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Detail Tiket
        </h3>

        <div id="ticket-detail-content"
     class="space-y-3 text-sm">
</div>

{{-- COMMENTS --}}
<div>
    <h4 class="font-medium">Komentar</h4>

    <div id="ticket-comments"
         class="space-y-2 max-h-48 overflow-y-auto text-sm mt-2"></div>

    <form onsubmit="submitComment(event)" class="mt-3 space-y-2">

    <div id="comment-error"
         class="hidden text-sm text-red-600 dark:text-red-400"></div>

    <div class="flex gap-2">
        <div class="flex-1 space-y-2">
            <input id="comment-input"
                type="text"
                placeholder="Tulis komentar..."
                class="w-full px-3 py-2 border rounded
                    bg-white dark:bg-[#0f0f0f]">

            <input id="comment-file"
                type="file"
                class="text-xs">
        </div>

        <button
            class="px-4 py-2 bg-indigo-600 text-white rounded">
            Kirim
        </button>
    </div>
</form>
</div>

        <button
            onclick="closeModal()"
            class="w-full mt-2 text-sm
                   px-4 py-3 rounded-lg
                   border border-[#19140035] dark:border-[#3E3E3A]
                   hover:bg-black/5 dark:hover:bg-white/10 transition">
            Tutup
        </button>

    </div>
</div>

<x-create-ticket-modal
    :ticket-categories="$ticketCategories ?? collect()"
/>

<x-create-laporan-modal
    :laporan-categories="$kategoriLaporan ?? collect()"
/>

<script>
let viewMode = localStorage.getItem('ticket_view') || 'table'

if (window.innerWidth < 768) {
    viewMode = 'card'
}

function setViewMode(mode) {
    viewMode = mode
    localStorage.setItem('ticket_view', mode)
    renderView()
}

function renderView() {
    document.getElementById('table-view')
        .classList.toggle('hidden', viewMode !== 'table')

    document.getElementById('card-view')
        .classList.toggle('hidden', viewMode !== 'card')
}

let q = '',
    status = '',
    category = '',
    sortBy = 'created_at',
    sortDir = 'desc'

// SEARCH
document.getElementById('search').oninput = e => {
    q = e.target.value
    loadData()
}

// FILTER STATUS
document.getElementById('filter-status').onchange = e => {
    status = e.target.value
    loadData()
}

// FILTER CATEGORY
document.getElementById('filter-category').onchange = e => {
    category = e.target.value
    loadData()
}

// SORT
document.getElementById('sort').onchange = e => {
    [sortBy, sortDir] = e.target.value.split('|')
    loadData()
}

function loadData(page = 1) {
    fetch(
        `/tickets?q=${q}` +
        `&status=${status}` +
        `&category_id=${category}` +
        `&sort_by=${sortBy}` +
        `&sort_dir=${sortDir}` +
        `&page=${page}`,
        {
            headers: { Accept: 'application/json' }
        }
    )
    .then(r => r.json())
    .then(res => {
        renderTable(res.data)
        renderCards(res.data)
        renderPagination(res)
        renderView()
    })
}

function statusBadge(status) {
    const map = {
        open: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        in_progress: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
        waiting: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
        closed: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        resolved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    }

    const cls = map[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'

    return `<span class="px-2 py-1 rounded-full text-xs font-medium ${cls}">
                ${status}
            </span>`
}

function renderTable(data) {
    const tbody = document.getElementById('ticket-body')
    tbody.innerHTML = ''

    data.forEach(t => {
        tbody.innerHTML += `
            <tr onclick="openDetail(${t.id})"
                class="cursor-pointer border-b hover:bg-black/5 dark:hover:bg-white/10">
                <td class="py-2">${t.ticket_number}</td>
                <td>${t.title}</td>
                <td>${t.priority}</td>
                <td>${statusBadge(t.status)}</td>
                <td>${new Date(t.created_at).toLocaleDateString()}</td>
            </tr>`
    })
}

function renderCards(data) {
    const box = document.getElementById('card-view')
    box.innerHTML = ''

    data.forEach(t => {
        box.innerHTML += `
            <div onclick="openDetail(${t.id})" class="p-4 rounded-xl border
                        border-[#19140035] dark:border-[#3E3E3A]
                        bg-white dark:bg-[#161615]
                        hover:bg-black/5 dark:hover:bg-white/10">

                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    ${t.ticket_number}
                </div>

                <div class="font-semibold mt-1">
                    ${t.title}
                </div>

                <div class="text-sm mt-1">
                    Prioritas: <b>${t.priority}</b>
                </div>

                <div class="flex justify-between items-center mt-3 text-xs">
                    ${statusBadge(t.status)}
                    <span>${new Date(t.created_at).toLocaleDateString()}</span>
                </div>
            </div>
        `
    })
}

function renderPagination(res) {
    const p = document.getElementById('pagination')
    p.innerHTML = ''

    for (let i = 1; i <= res.last_page; i++) {
        p.innerHTML += `
            <button onclick="loadData(${i})"
                class="px-3 py-1 border rounded
                       ${i === res.current_page ? 'bg-black text-white dark:bg-white dark:text-black' : ''}">
                ${i}
            </button>`
    }
}

function buildExportQuery() {
    return new URLSearchParams({
        q,
        status,
        category_id: category,
        sort_by: sortBy,
        sort_dir: sortDir,
    }).toString()
}

function exportExcel() {
    window.location.href =
        `/tickets/export/excel?${buildExportQuery()}`
}

function exportPdf() {
    window.location.href =
        `/tickets/export/pdf?${buildExportQuery()}`
}

let currentTicketId = null

function openDetail(id) {
    currentTicketId = id

    fetch(`/tickets/${id}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const t = res.data
        const box = document.getElementById('ticket-detail-content')

        box.innerHTML = `
            <div><strong>No:</strong> ${t.ticket_number}</div>
            <div><strong>Judul:</strong> ${t.title}</div>
            <div><strong>Prioritas:</strong> ${t.priority}</div>
            <div><strong>Status:</strong> ${t.status}</div>

            <div class="pt-2">
                <strong>Deskripsi:</strong>
                <p class="mt-1 text-[#706f6c] dark:text-[#A1A09A]">
                    ${t.description}
                </p>
            </div>
            <strong>Attachment:</strong>
                    <ul class="mt-1 space-y-1">
                        ${t.attachments.map(a => `
                            <li>
                                <a href="${a.url}"
                                   target="_blank"
                                   class="text-indigo-600 hover:underline">
                                   ${a.file_name}
                                </a>
                            </li>
                        `).join('')}
                    </ul>
        `

        renderComments(t.comments || [])

        const modal = document.getElementById('ticket-modal')
        modal.classList.remove('hidden')
        modal.classList.add('flex')
    })
}

function renderComments(list) {
    const el = document.getElementById('ticket-comments')
    el.innerHTML = ''

    if (list.length === 0) {
        el.innerHTML = '<div class="text-gray-500">Belum ada komentar</div>'
        return
    }

    list.forEach(c => {
        el.innerHTML += `
            <div class="p-2 rounded bg-gray-100 dark:bg-[#0f0f0f]">
                <div class="text-xs text-gray-500">
                    ${c.user?.email ?? 'User'}
                </div>
                <div>${c.message}</div>
            </div>
        `
    })
}

function submitComment(e) {
    e.preventDefault()

    const errorBox = document.getElementById('comment-error')
    errorBox.classList.add('hidden')
    errorBox.textContent = ''

    const message = document.getElementById('comment-input').value.trim()
    const fileInput = document.getElementById('comment-file')
    const file = fileInput.files[0]

    const formData = new FormData()
    formData.append('message', message)

    if (file) {
        formData.append('attachment', file)
    }

    fetch(`/tickets/${currentTicketId}/comments`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async r => {
        if (!r.ok) {
            // tangkap error server
            let msg = 'Terjadi kesalahan.'

            if (r.status === 413) {
                msg = 'Ukuran file terlalu besar.'
            } else {
                try {
                    const data = await r.json()
                    msg = data.message || msg
                } catch {
                    msg = 'Upload gagal. Coba file yang lebih kecil.'
                }
            }

            throw new Error(msg)
        }

        return r.json()
    })
    .then(() => {
        document.getElementById('comment-input').value = ''
        fileInput.value = ''
        openDetail(currentTicketId)
    })
    .catch(err => {
        errorBox.textContent = err.message
        errorBox.classList.remove('hidden')
    })
}

function closeModal() {
    const m = document.getElementById('ticket-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}


loadData()
</script>

</x-app-layout>
