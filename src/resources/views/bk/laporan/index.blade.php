<x-app-layout>

<div class="flex flex-col gap-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold
                   text-[#1b1b18] dark:text-[#EDEDEC]">
            Laporan BK
        </h1>

        <div class="flex flex-wrap gap-2">

            <input id="search"
                type="text"
                placeholder="Cari laporan..."
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border border-[#19140035] dark:border-[#3E3E3A]">

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

            <button onclick="exportExcel()"
                class="px-3 py-2 text-sm rounded
                       border border-[#19140035] dark:border-[#3E3E3A]">
                Excel
            </button>

            <button onclick="exportPdf()"
                class="px-3 py-2 text-sm rounded
                       border border-[#19140035] dark:border-[#3E3E3A]">
                PDF
            </button>

        </div>
    </div>
</div>

<div class="bg-white dark:bg-[#161615]
            border border-[#19140035] dark:border-[#3E3E3A]
            rounded-xl overflow-hidden">

    <table class="w-full text-sm">
        <thead>
            <tr class="border-b
                       border-[#19140035] dark:border-[#3E3E3A]">
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

<div id="detail-modal"
     class="fixed inset-0 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeModal()">

    <div onclick="event.stopPropagation()"
         class="bg-white dark:bg-[#161615]
                p-6 rounded-xl w-full max-w-md space-y-3
                border border-[#19140035] dark:border-[#3E3E3A]">

        <h3 class="font-semibold text-lg">
            Detail Laporan
        </h3>

        <div id="detail-content"
             class="text-sm space-y-2">
        </div>

        <button onclick="closeModal()"
            class="w-full border px-3 py-2 rounded">
            Tutup
        </button>
    </div>
</div>

<script>
let q = '', status = '', sortBy = 'created_at', sortDir = 'desc'

document.getElementById('search').oninput = e => {
    q = e.target.value
    loadData()
}

document.getElementById('filter-status').onchange = e => {
    status = e.target.value
    loadData()
}

function loadData(page = 1) {
    const params = new URLSearchParams({
        q, status, sort_by: sortBy, sort_dir: sortDir, page
    })

    fetch(`/bk/laporan?${params.toString()}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        renderTable(res.data)
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
                <td>${l.pelapor?.email ?? '-'}</td>
                <td>${l.status}</td>
                <td class="px-4 py-2 text-right space-x-2">
                <button onclick="openDetail('${l.uuid}')"
                    class="text-indigo-600 text-xs">
                    Detail
                </button>

                    ${l.status === 'menunggu_bk' ? `
                        <button onclick="proses('${l.uuid}')"
                            class="text-indigo-600 text-xs">
                            Proses
                        </button>
                    ` : ''}

                    ${l.status === 'diproses_bk' ? `
                        <button onclick="selesai('${l.uuid}')"
                            class="text-green-600 text-xs">
                            Selesai
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

function proses(uuid) {
    fetch(`/bk/laporan/${uuid}/proses`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(() => loadData())
}

function selesai(uuid) {
    fetch(`/bk/laporan/${uuid}/selesai`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(() => loadData())
}

function tolak(uuid) {
    fetch(`/bk/laporan/${uuid}/tolak`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(() => loadData())
}

function openDetail(uuid) {
    fetch(`/bk/laporan/${uuid}`, {
        headers: { Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        const l = res.data
        const box = document.getElementById('detail-content')

        let attachmentsHtml = ''
        if (l.attachments?.length) {
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
            <div><strong>Judul:</strong> ${l.judul}</div>
            <div><strong>Pelapor:</strong> ${l.pelapor?.email ?? '-'}</div>
            <div><strong>Status:</strong> ${l.status}</div>

            <div>
                <strong>Deskripsi:</strong>
                <p class="text-gray-600">
                    ${l.deskripsi}
                </p>
            </div>

            ${attachmentsHtml}
        `

        const modal = document.getElementById('detail-modal')
        modal.classList.remove('hidden')
        modal.classList.add('flex')
    })
}

function closeModal() {
    const modal = document.getElementById('detail-modal')
    modal.classList.add('hidden')
    modal.classList.remove('flex')
}


function exportExcel() {
    window.location = '/bk/laporan/export/excel'
}

function exportPdf() {
    window.location = '/bk/laporan/export/pdf'
}

loadData()
</script>

</x-app-layout>
