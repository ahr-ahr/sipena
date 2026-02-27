<x-app-layout>

<h1 class="text-2xl font-semibold mb-6">Riwayat Aktivitas</h1>

{{-- FILTER --}}
<div class="flex flex-wrap gap-3 mb-6">

    <input id="search"
        placeholder="Cari laporan / tiket..."
        class="border border-gray-300 dark:border-[#3E3E3A]
               bg-white dark:bg-[#161615]
               text-sm
               px-4 py-2 w-56
               rounded-md">

    <select id="period"
        class="border border-gray-300 dark:border-[#3E3E3A]
               bg-white dark:bg-[#161615]
               text-sm
               px-4 py-2
               rounded-md">
        <option value="month">Bulanan</option>
        <option value="week">Mingguan</option>
        <option value="day">Harian</option>
    </select>

    <button onclick="exportExcel()"
        class="px-4 py-2 border
               border-gray-300 dark:border-[#3E3E3A]
               text-sm
               rounded-md
               hover:bg-gray-100 dark:hover:bg-white/10">
        Excel
    </button>

    <button onclick="exportPdf()"
        class="px-4 py-2 border
               border-gray-300 dark:border-[#3E3E3A]
               text-sm
               rounded-md
               hover:bg-gray-100 dark:hover:bg-white/10">
        PDF
    </button>

</div>

{{-- DONUT --}}
<div class="bg-white dark:bg-[#161615]
            border border-transparent dark:border-[#3E3E3A]
            p-6 rounded-2xl shadow-sm mb-6">
    <h3 class="font-semibold mb-4">Ringkasan Status</h3>
    <div class="relative h-[280px]">
        <canvas id="donutChart"></canvas>
    </div>
</div>

{{-- TIMELINE + STACKED --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    {{-- TIMELINE --}}
    <div class="bg-white dark:bg-[#161615]
                border border-transparent dark:border-[#3E3E3A]
                p-6 rounded-2xl shadow-sm">
        <h3 class="font-semibold mb-4">Timeline Aktivitas</h3>
        <div class="relative h-[260px]">
            <canvas id="timelineChart"></canvas>
        </div>
    </div>

    {{-- STACKED --}}
    <div class="bg-white dark:bg-[#161615]
                border border-transparent dark:border-[#3E3E3A]
                p-6 rounded-2xl shadow-sm">
        <h3 class="font-semibold mb-4">Perbandingan Laporan vs Tiket</h3>
        <div class="relative h-[260px]">
            <canvas id="stackedChart"></canvas>
        </div>
    </div>

</div>

{{-- TABLE --}}
<div class="bg-white dark:bg-[#161615]
            border border-transparent dark:border-[#3E3E3A]
            rounded-2xl shadow-sm overflow-hidden">

    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-[#1F1F1D] border-b
                      border-gray-200 dark:border-[#2A2A28]">
            <tr class="text-left text-gray-600 dark:text-gray-400">
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Judul</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Tanggal</th>
            </tr>
        </thead>

        <tbody id="history-body"
            class="divide-y divide-gray-200 dark:divide-[#2A2A28]">
        </tbody>
    </table>

</div>

<x-create-ticket-modal :ticket-categories="$ticketCategories ?? collect()" />
<x-create-laporan-modal :laporan-categories="$laporanCategories ?? collect()" />

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
let donut, timeline, stacked

function isDark(){
    return document.documentElement.classList.contains('dark')
}

function axisColor(){
    return isDark() ? '#A1A09A' : '#4B5563'
}

function gridColor(){
    return isDark() ? '#2A2A28' : '#E5E7EB'
}

function load(){
    fetch(`/history?q=${search.value}&period=${period.value}`, {
        headers:{Accept:'application/json'}
    })
    .then(r=>r.json())
    .then(res=>{
        renderTable(res)
        renderDonut(res.stats)
        renderTimeline(res.timeline)
        renderStacked(res.stats)
    })
}

/* ================= TABLE ================= */

function badge(status){
    const map = {
        selesai: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        ditolak_wali: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        closed: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        resolved: 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300'
    }
    return `<span class="px-2 py-1 rounded-full text-xs ${map[status]||'bg-gray-100 dark:bg-gray-800'}">${status}</span>`
}

function renderTable(res){
    const body = document.getElementById('history-body')
    body.innerHTML=''

    res.laporan.data.concat(res.tickets.data).forEach(i=>{
        body.innerHTML += `
        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
            <td class="px-4 py-3 font-medium">
                ${i.kode_laporan ? 'Laporan' : 'Tiket'}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                ${i.kode_laporan || i.ticket_number}
            </td>
            <td class="px-4 py-3">
                ${i.judul || i.title}
            </td>
            <td class="px-4 py-3">
                ${badge(i.status)}
            </td>
            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                ${new Date(i.updated_at || i.closed_at).toLocaleDateString()}
            </td>
        </tr>`
    })
}

/* ================= CHARTS ================= */

function renderDonut(s){
    donut?.destroy()
    donut = new Chart(donutChart,{
        type:'doughnut',
        data:{
            labels:[
                'Laporan Selesai',
                'Laporan Ditolak',
                'Tiket Closed',
                'Tiket Resolved'
            ],
            datasets:[{
                data:[
                    s.laporan.selesai,
                    s.laporan.ditolak,
                    s.ticket.closed,
                    s.ticket.resolved
                ]
            }]
        },
        options:{
            maintainAspectRatio:false,
            plugins:{
                legend:{
                    position:'bottom',
                    labels:{color:axisColor()}
                }
            }
        }
    })
}

function renderTimeline(t){
    timeline?.destroy()
    timeline = new Chart(timelineChart,{
        type:'line',
        data:{
            labels:t.labels,
            datasets:[
                {label:'Laporan',data:t.laporan},
                {label:'Tiket',data:t.ticket},
            ]
        },
        options:{
            maintainAspectRatio:false,
            tension:0.4,
            scales:{
                x:{ticks:{color:axisColor()},grid:{color:gridColor()}},
                y:{ticks:{color:axisColor()},grid:{color:gridColor()}}
            }
        }
    })
}

function renderStacked(s){
    stacked?.destroy()
    stacked = new Chart(stackedChart,{
        type:'bar',
        data:{
            labels:['Laporan','Tiket'],
            datasets:[
                {label:'Selesai / Closed',data:[s.laporan.selesai,s.ticket.closed]},
                {label:'Ditolak / Resolved',data:[s.laporan.ditolak,s.ticket.resolved]},
            ]
        },
        options:{
            maintainAspectRatio:false,
            scales:{
                x:{stacked:true,ticks:{color:axisColor()},grid:{color:gridColor()}},
                y:{stacked:true,ticks:{color:axisColor()},grid:{color:gridColor()}}
            }
        }
    })
}

search.oninput = load
period.onchange = load
load()

function exportExcel(){location='/history/export/excel'}
function exportPdf(){
    fetch('/history/export/pdf', {
        method: 'POST',
        headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({
            donut: donut?.toBase64Image(),
            timeline: timeline?.toBase64Image(),
            stacked: stacked?.toBase64Image()
        })
    })
    .then(r=>r.blob())
    .then(b=>{
        const url = URL.createObjectURL(b)
        window.open(url)
    })
}
</script>

</x-app-layout>
