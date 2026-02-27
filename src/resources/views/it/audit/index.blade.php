<x-app-layout>

{{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">

        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Audit Log
        </h1>

        <div class="flex flex-wrap gap-2">

            <input id="from" type="date"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]">

            <input id="to" type="date"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]">

            <select id="filter-user"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="">Semua User</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->email }}</option>
                @endforeach
            </select>

            <select id="filter-action"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="">Semua Aksi</option>
                <option value="UPDATE_ROLE">Update Role</option>
                <option value="DELETE_USER">Delete User</option>
            </select>

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
            <th class="py-3 px-2 text-left w-40">Tanggal</th>
            <th class="py-3 px-2 text-left w-48">User</th>
            <th class="py-3 px-2 text-left w-36">Aksi</th>
            <th class="py-3 px-2 text-left w-32">Target</th>
            <th class="py-3 px-2 text-left">Deskripsi</th>
            <th class="py-3 px-2 text-left w-32">IP</th>
        </tr>
    </thead>

    <tbody id="audit-body"
        class="text-[#1b1b18] dark:text-[#EDEDEC]
               divide-y divide-[#19140035] dark:divide-[#3E3E3A]">
    </tbody>
</table>

    <div id="pagination" class="mt-4 flex gap-2 justify-end"></div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
let userId='', action='', from='', to=''

document.getElementById('filter-user').onchange = e => {
    userId = e.target.value
    loadData()
}
document.getElementById('filter-action').onchange = e => {
    action = e.target.value
    loadData()
}
document.getElementById('from').onchange = e => {
    from = e.target.value
    loadData()
}
document.getElementById('to').onchange = e => {
    to = e.target.value
    loadData()
}

function loadData(page = 1) {
    fetch(`/it/audit-logs?user_id=${userId}&action=${action}&from=${from}&to=${to}&page=${page}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        renderTable(res.data)
        renderPagination(res.meta)
    })
}

function renderTable(data) {
    const tbody = document.getElementById('audit-body')
    tbody.innerHTML = ''

    data.forEach(l => {
        const actionColor =
            l.action === 'DELETE_USER'
                ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'

        tbody.innerHTML += `
            <tr class="hover:bg-black/5 dark:hover:bg-white/5">

                <td class="px-2 py-3 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    ${new Date(l.created_at).toLocaleString()}
                </td>

                <td class="px-2 py-3 truncate">
                    ${l.user?.email ?? '-'}
                </td>

                <td class="px-2 py-3">
                    <span class="inline-flex items-center
                                 px-2 py-1 rounded-full text-xs font-medium
                                 ${actionColor}">
                        ${l.action}
                    </span>
                </td>

                <td class="px-2 py-3 text-xs">
                    ${l.target_type}
                    ${l.target_id ? `#${l.target_id}` : ''}
                </td>

                <td class="px-2 py-3 text-sm leading-relaxed">
                    ${l.description ?? '-'}
                </td>

                <td class="px-2 py-3 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    ${l.ip_address ?? '-'}
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
                class="px-3 py-1 rounded-sm border
                       border-[#19140035] dark:border-[#3E3E3A]
                       ${i === meta.current_page
                        ? 'bg-[#1b1b18] text-white dark:bg-white dark:text-black'
                        : 'hover:bg-black/5 dark:hover:bg-white/10'}">
                ${i}
            </button>
        `
    }
}

loadData()
</script>

</x-app-layout>
