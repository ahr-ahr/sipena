<x-app-layout>

{{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">

        <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
            Manajemen Pengguna
        </h1>

        <div class="flex flex-wrap gap-2">

            <input id="search" type="text"
                placeholder="Cari email atau nama…"
                class="rounded-sm px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]
                       placeholder:text-[#706f6c]">

            <select id="filter-tipe"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="">Semua Tipe</option>
                <option value="siswa">Siswa</option>
                <option value="pegawai">Pegawai</option>
            </select>

            <select id="sort"
                class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                       bg-white dark:bg-[#161615]
                       text-[#1b1b18] dark:text-[#EDEDEC]
                       border border-[#19140035] dark:border-[#3E3E3A]">
                <option value="created_at|desc">Terbaru</option>
                <option value="created_at|asc">Terlama</option>
                <option value="email|asc">Email A-Z</option>
                <option value="email|desc">Email Z-A</option>
            </select>

        </div>
    </div>
</div>

{{-- ================= TABLE ================= --}}
<div class="mt-6 p-6 rounded-xl
            bg-white dark:bg-[#161615]
            border border-[#19140035] dark:border-[#3E3E3A]">

    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-[#19140035] dark:border-[#3E3E3A]
                       text-[#706f6c] dark:text-[#A1A09A]">
                <th class="text-left py-2">Email</th>
                <th class="text-center py-2">Tipe</th>
                <th class="text-left py-2">Nama</th>
                <th class="text-right py-2">Aksi</th>
            </tr>
        </thead>

        <tbody id="user-body"
            class="text-[#1b1b18] dark:text-[#EDEDEC]">
        </tbody>
    </table>

    <div id="pagination" class="mt-4 flex gap-2 justify-end"></div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
let q = '', tipe = '', sortBy = 'created_at', sortDir = 'desc'

document.getElementById('search').oninput = e => {
    q = e.target.value
    loadData()
}

document.getElementById('filter-tipe').onchange = e => {
    tipe = e.target.value
    loadData()
}

document.getElementById('sort').onchange = e => {
    [sortBy, sortDir] = e.target.value.split('|')
    loadData()
}

function loadData(page = 1) {
    fetch(`/it/users?q=${q}&tipe=${tipe}&sort=${sortBy}&dir=${sortDir}&page=${page}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        renderTable(res.data)
        renderPagination(res.meta)
    })
}

function renderTable(data) {
    const tbody = document.getElementById('user-body')
    tbody.innerHTML = ''

    data.forEach(u => {
        const nama =
            u.siswa_profile?.nama ??
            u.pegawai_profile?.nama ??
            '-'

        tbody.innerHTML += `
            <tr class="border-b hover:bg-black/5 dark:hover:bg-white/5">
                <td class="py-2">${u.email}</td>

                <td class="py-2 text-center">
                    <select
                        onchange="updateRole(${u.id}, this.value)"
                        class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                        bg-white dark:bg-[#161615]
                        text-[#1b1b18] dark:text-[#EDEDEC]
                        border border-[#19140035] dark:border-[#3E3E3A]
                        focus:outline-none">
                        <option value="siswa" ${u.tipe_user === 'siswa' ? 'selected' : ''}>
                            Siswa
                        </option>
                        <option value="pegawai" ${u.tipe_user === 'pegawai' ? 'selected' : ''}>
                            Pegawai
                        </option>
                    </select>
                </td>

                <td class="py-2">${nama}</td>

                <td class="py-2 text-right">
                    <button
                        onclick="deleteUser(${u.id})"
                        class="inline-flex items-center gap-1
                            text-xs text-red-600 hover:text-red-700
                            hover:underline">

                        <span class="material-icons text-base leading-none">
                            delete
                        </span>

                        <span>Hapus</span>
                    </button>
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

/* ================= AJAX ACTIONS ================= */

async function deleteUser(id) {
    if (!confirm('Hapus user ini?')) return

    try {
        const res = await fetch(`/it/users/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })

        if (!res.ok) {
            alert('Gagal menghapus user')
            return
        }

        loadData()
    } catch (err) {
        console.error(err)
        alert('Terjadi kesalahan')
    }
}

async function updateRole(id, tipe_user) {
    try {
        const res = await fetch(`/it/users/${id}/role`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ tipe_user })
        })

        if (!res.ok) {
            alert('Gagal update role')
            loadData()
        }
    } catch (err) {
        console.error(err)
        alert('Terjadi kesalahan')
        loadData()
    }
}

loadData()
</script>

</x-app-layout>
