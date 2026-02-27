<x-app-layout>

<h1 class="text-2xl font-semibold mb-6
    text-[#1b1b18] dark:text-[#EDEDEC]">
    Penugasan Wali Kelas
</h1>

<div class="rounded-xl border
    border-[#19140035] dark:border-[#3E3E3A]
    bg-white dark:bg-[#161615]
    shadow overflow-hidden">

    <table class="w-full text-sm">
        <thead
            class="border-b
                   border-[#19140035] dark:border-[#3E3E3A]
                   text-[#706f6c] dark:text-[#A1A09A]">
            <tr>
                <th class="px-4 py-3 text-left">Kelas</th>
                <th class="px-4 py-3 text-left">Wali Kelas</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>

        <tbody
            class="divide-y
                   divide-[#19140035] dark:divide-[#3E3E3A]
                   text-[#1b1b18] dark:text-[#EDEDEC]">

            @foreach ($kelas as $k)
                @php
                    $currentWali = $k->wali?->wali;
                @endphp

                <tr class="hover:bg-black/5 dark:hover:bg-white/5">
                    <td class="px-4 py-3 font-medium">
                        {{ $k->nama }}
                    </td>

                    <td class="px-4 py-3">
                        <select
                            class="appearance-none rounded-sm px-3 py-2 pr-10 text-sm
                                   bg-white dark:bg-[#161615]
                                   text-[#1b1b18] dark:text-[#EDEDEC]
                                   border border-[#19140035] dark:border-[#3E3E3A]"
                            onchange="assignWali({{ $k->id }}, this.value)"
                        >
                            <option value="">— Pilih Wali —</option>

                            @foreach ($waliList as $wali)
                                <option value="{{ $wali->id }}"
                                    @selected($currentWali?->id === $wali->id)>
                                    {{ $wali->pegawaiProfile?->nama ?? $wali->email }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td class="px-4 py-3 text-right space-x-2">
                        @if ($currentWali)
                            <button
                                onclick="removeWali({{ $k->id }})"
                                class="text-xs px-3 py-1 rounded
                                       bg-red-100 text-red-700
                                       dark:bg-red-900/30 dark:text-red-300
                                       hover:opacity-80">
                                Hapus
                            </button>
                        @else
                            <span class="text-xs text-gray-400">
                                —
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function assignWali(kelasId, userId) {
    if (!userId) return

    fetch("{{ route('it.kelas-wali.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            kelas_id: kelasId,
            user_id: userId,
        })
    })
    .then(r => r.json())
    .then(res => {
        console.log(res)
    })
}

function removeWali(kelasId) {
    if (!confirm('Hapus wali kelas ini?')) return

    fetch("{{ route('it.kelas-wali.destroy') }}", {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            kelas_id: kelasId,
        })
    })
    .then(r => r.json())
    .then(res => {
        console.log(res)
        location.reload()
    })
}
</script>

</x-app-layout>
