<x-app-layout>

{{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">

        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Manajemen Tiket IT
        </h1>

        <div class="flex flex-wrap gap-2">

            <input id="search" type="text" placeholder="Cari tiket..."
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]">

            <select id="filter-status" class="...">
    <option value="">Semua Status</option>
    @foreach ($statusOptions as $s)
        <option value="{{ $s->value }}">
            {{ $s->label() }}
        </option>
    @endforeach
</select>

            @can('create-ticket')
            <button
                onclick="openCreateModal()"
                class="px-4 py-2 rounded-lg
                    bg-black text-white
                    dark:bg-white dark:text-black
                    text-sm font-medium">
                Buat Tiket
            </button>
            @endcan
        </div>
    </div>
</div>

{{-- ================= TABLE ================= --}}
<div class="mt-6 p-6 rounded-xl
            bg-white dark:bg-[#161615]
            border border-[#19140035] dark:border-[#3E3E3A]">

    <table class="w-full text-sm table-fixed">
        <thead>
            <tr class="border-b border-[#19140035] dark:border-[#3E3E3A]
                       text-[#706f6c] dark:text-[#A1A09A]">
                <th class="py-3 px-2 text-left w-40">No Tiket</th>
                <th class="py-3 px-2 text-left">Judul</th>
                <th class="py-3 px-2 text-left w-48">Pelapor</th>
                <th class="py-3 px-2 text-left w-40">Kategori</th>
                <th class="py-3 px-2 text-left w-28">Prioritas</th>
                <th class="py-3 px-2 text-left w-32">Status</th>
                <th class="py-3 px-2 text-right w-32">Aksi</th>
            </tr>
        </thead>

        <tbody id="ticket-body"
            class="text-[#1b1b18] dark:text-[#EDEDEC]
                   divide-y divide-[#19140035] dark:divide-[#3E3E3A]">
        </tbody>
    </table>

    <div id="pagination" class="mt-4 flex gap-2 justify-end"></div>
</div>

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
    :itUsers="$itUsers"
    :laporanSelesai="$laporanSelesai" />

{{-- ================= SCRIPT ================= --}}
<script>
let search = ''
let status = ''

document.getElementById('search').oninput = e => {
    search = e.target.value
    loadData()
}

document.getElementById('filter-status').onchange = e => {
    status = e.target.value
    loadData()
}

function loadData(page = 1) {
    fetch(`/it/tickets?q=${search}&status=${status}&page=${page}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        renderTable(res.data)
        renderPagination(res)
    })
}

function renderTable(data) {
    const tbody = document.getElementById('ticket-body')
    tbody.innerHTML = ''

    data.forEach(t => {

        const statusColor =
            t.status === 'resolved'
                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
            : t.status === 'open'
                ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'
            : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'

        const priorityColor =
            t.priority === 'urgent'
                ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
            : t.priority === 'high'
                ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'

        tbody.innerHTML += `
            <tr class="hover:bg-black/5 dark:hover:bg-white/5">

                <td class="px-2 py-3 font-medium">
                    ${t.ticket_number}
                </td>

                <td class="px-2 py-3 truncate">
                    ${t.title}
                </td>

                <td class="px-2 py-3 truncate">
                    ${t.user?.email ?? '-'}
                </td>

                <td class="px-2 py-3 truncate">
                    ${t.laporan?.kategori?.nama ?? '-'}
                </td>

                <td class="px-2 py-3">
                    <span class="inline-flex px-2 py-1 rounded-full text-xs ${priorityColor}">
                        ${t.priority}
                    </span>
                </td>

                <td class="px-2 py-3">
                    <span class="inline-flex px-2 py-1 rounded-full text-xs ${statusColor}">
                        ${t.status}
                    </span>
                </td>

                <td class="px-2 py-3 text-right space-x-2">
                    <button onclick="openDetail(${t.id})"
                        class="text-indigo-600 hover:underline text-xs">
                        Detail
                    </button>
                    <button onclick="resolveTicket(${t.id})"
                        class="text-green-600 hover:underline text-xs">
                        Selesaikan
                    </button>
                </td>

            </tr>
        `
    })
}

function renderPagination(res) {
    const p = document.getElementById('pagination')
    p.innerHTML = ''

    if (!res.last_page) return

    for (let i = 1; i <= res.last_page; i++) {
        p.innerHTML += `
            <button onclick="loadData(${i})"
                class="px-3 py-1 rounded-sm border
                       border-[#19140035] dark:border-[#3E3E3A]
                       ${i === res.current_page
                        ? 'bg-[#1b1b18] text-white dark:bg-white dark:text-black'
                        : 'hover:bg-black/5 dark:hover:bg-white/10'}">
                ${i}
            </button>
        `
    }
}

function resolveTicket(id) {
    if (!confirm('Selesaikan tiket ini?')) return

    fetch(`/it/tickets/${id}/resolve`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(() => loadData())
}

let currentTicketId = null

function openDetail(id) {
    currentTicketId = id

    fetch(`/it/tickets/${id}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const t = res.data
        const box = document.getElementById('ticket-detail-content')

        let attachmentsHtml = ''
        if (t.attachments?.length) {
            attachmentsHtml = `
                <div>
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
                </div>
            `
        }

        let printButton = ''

if (t.external_vendor) {
    printButton = `
        <button onclick="printTicket(${t.id})"
            class="w-full mt-3 px-4 py-2 rounded-lg
                   bg-black text-white
                   dark:bg-white dark:text-black
                   text-sm font-medium">
            Print Surat Kerja Vendor
        </button>
    `
}

        box.innerHTML = `
            <div><strong>No:</strong> ${t.ticket_number}</div>
            <div><strong>Judul:</strong> ${t.title}</div>
            <div><strong>Vendor:</strong> ${t.external_vendor ?? '-'}</div>
            <div><strong>Pelapor:</strong> ${t.user?.email ?? '-'}</div>
            <div><strong>Kategori:</strong> ${t.laporan?.kategori?.nama ?? '-'}</div>
            <div><strong>Status:</strong> ${t.status}</div>

            <div class="pt-2">
                <strong>Deskripsi:</strong>
                <p class="mt-1 text-[#706f6c] dark:text-[#A1A09A]">
                    ${t.description}
                </p>
            </div>

            ${attachmentsHtml}
            ${printButton}
        `

        renderComments(t.comments || [])

        const modal = document.getElementById('ticket-modal')
        modal.classList.remove('hidden')
        modal.classList.add('flex')
    })
}

function closeModal() {
    document.getElementById('ticket-modal').classList.add('hidden')
    document.getElementById('ticket-modal').classList.remove('flex')
}

function renderAttachments(list) {
    const el = document.getElementById('modal-attachments')
    el.innerHTML = ''

    if (list.length === 0) {
        el.innerHTML = '<li class="text-gray-500">Tidak ada attachment</li>'
        return
    }

    list.forEach(a => {
        el.innerHTML += `
            <li>
                <a href="/storage/${a.file_path}"
                   target="_blank"
                   class="text-indigo-600 hover:underline">
                    ${a.file_name}
                </a>
            </li>
        `
    })
}

function renderComments(list) {
    const el = document.getElementById('ticket-comments')

    if (!el) return

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

    const input = document.getElementById('comment-input')
    const message = input.value.trim()
    if (!message) return

    fetch(`/it/tickets/${currentTicketId}/comments`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message })
    })
    .then(() => {
        input.value = ''
        openDetail(currentTicketId)
    })
}

loadData()

function openCreateModal() {
    const m = document.getElementById('create-ticket-modal')
    m.classList.remove('hidden')
    m.classList.add('flex')
}

function closeCreateModal() {
    const m = document.getElementById('create-ticket-modal')
    m.classList.add('hidden')
    m.classList.remove('flex')
}

document.getElementById('assign-type')?.addEventListener('change', e => {
    const type = e.target.value
    document.getElementById('internal-box').classList.toggle('hidden', type !== 'internal')
    document.getElementById('external-box').classList.toggle('hidden', type !== 'external')
})

function submitCreateTicket(e) {
    e.preventDefault()

    const form = document.getElementById('create-ticket-form')
    const data = new FormData(form)

    fetch('/tickets', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: data
    })
    .then(r => r.json())
    .then(() => {
        closeCreateModal()
        loadData()
    })
}

function printTicket(id) {
    window.open(`/it/tickets/${id}/print`, '_blank')
}
</script>

</x-app-layout>
