@props([
    'laporanCategories' => collect(),
    'mapelList' => collect(),
])
<div
    x-show="createLaporanOpen"
    x-transition.opacity
    x-cloak
    @click="createLaporanOpen = false"
    class="fixed inset-0 z-50 flex items-center justify-center
           bg-black/50 backdrop-blur-sm"
>
    <div
        @click.stop
        class="w-full max-w-md rounded-xl
               bg-white dark:bg-[#161615]
               p-6 shadow-xl
               max-h-[85vh] overflow-y-auto"
    >
        <h3 class="text-lg font-semibold mb-4">
            Buat Laporan
        </h3>

        <form
            @submit.prevent="submitLaporan($event)"
            enctype="multipart/form-data"
            class="space-y-4"
        >

            {{-- KATEGORI --}}
            <div>
                <label class="block text-xs font-medium mb-1">
                    Kategori
                </label>
                <select name="kategori_id"
                @change="
                    const selected = $event.target.options[$event.target.selectedIndex];
                    selectedKategoriNama = selected.text;
                "
                class="w-full rounded-md px-3 py-2 text-sm
           bg-white dark:bg-[#161615]
           text-[#1b1b18] dark:text-[#EDEDEC]
           border border-[#19140035] dark:border-[#3E3E3A]
           focus:outline-none focus:ring-1
           focus:ring-black/20 dark:focus:ring-white/20">
                    <option value="">Pilih kategori</option>
                    @foreach ($laporanCategories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->nama }}
                        </option>
                    @endforeach
                </select>
                <p x-show="laporanErrors.kategori_id"
                   x-text="laporanErrors.kategori_id?.[0]"
                   class="text-xs text-red-500 mt-1"></p>
            </div>

            {{-- MAPEL (khusus akademik) --}}
            <div x-show="selectedKategoriNama === 'Pengaduan Akademik'" x-transition>
                <label class="block text-xs font-medium mb-1">
                    Mata Pelajaran
                </label>
                <select name="mapel_id"
                    class="w-full rounded-md px-3 py-2 text-sm
                        bg-white dark:bg-[#161615]
                        border border-[#19140035] dark:border-[#3E3E3A]">
                    <option value="">Pilih mata pelajaran</option>
                    @foreach ($mapelList as $mapel)
                        <option value="{{ $mapel->id }}">
                            {{ $mapel->nama }}
                        </option>
                    @endforeach
                </select>

                <p x-show="laporanErrors.mapel_id"
                x-text="laporanErrors.mapel_id?.[0]"
                class="text-xs text-red-500 mt-1"></p>
            </div>

            {{-- JUDUL --}}
            <div>
                <label class="block text-xs font-medium mb-1">
                    Judul
                </label>
                <input type="text" name="judul"
                       class="w-full rounded-md px-3 py-2 text-sm
           bg-white dark:bg-[#161615]
           text-[#1b1b18] dark:text-[#EDEDEC]
           border border-[#19140035] dark:border-[#3E3E3A]
           focus:outline-none focus:ring-1
           focus:ring-black/20 dark:focus:ring-white/20">
                <p x-show="laporanErrors.judul"
                   x-text="laporanErrors.judul?.[0]"
                   class="text-xs text-red-500 mt-1"></p>
            </div>

            {{-- DESKRIPSI --}}
            <div>
                <label class="block text-xs font-medium mb-1">
                    Deskripsi
                </label>
                <textarea name="deskripsi" rows="3"
                          class="w-full rounded-md px-3 py-2 text-sm
           bg-white dark:bg-[#161615]
           text-[#1b1b18] dark:text-[#EDEDEC]
           border border-[#19140035] dark:border-[#3E3E3A]
           focus:outline-none focus:ring-1
           focus:ring-black/20 dark:focus:ring-white/20"></textarea>
                <p x-show="laporanErrors.deskripsi"
                   x-text="laporanErrors.deskripsi?.[0]"
                   class="text-xs text-red-500 mt-1"></p>
            </div>

            {{-- LAMPIRAN --}}
            <div>
                <label class="block text-xs font-medium mb-1">
                    Lampiran
                </label>
                <input type="file" name="attachments[]" multiple
                       class="w-full text-xs
           text-[#1b1b18] dark:text-[#EDEDEC]
           file:mr-3 file:py-1.5 file:px-3
           file:rounded-md file:border-0
           file:bg-black file:text-white
           dark:file:bg-white dark:file:text-black
           hover:file:bg-black/80 dark:hover:file:bg-white/80">
                <p x-show="laporanErrors.attachments"
                   x-text="laporanErrors.attachments?.[0]"
                   class="text-xs text-red-500 mt-1"></p>
            </div>

            {{-- ACTION --}}
            <div class="flex justify-end gap-2 pt-3">
                <button type="button"
                        @click="createLaporanOpen = false"
                        class="px-3 py-1.5 text-sm border rounded-md">
                    Batal
                </button>

                <button type="submit"
                        :disabled="laporanLoading"
                        class="px-4 py-1.5 text-sm rounded-md
                               bg-black text-white
                               dark:bg-white dark:text-black">
                    <span x-show="!laporanLoading">Kirim</span>
                    <span x-show="laporanLoading">Mengirim...</span>
                </button>
            </div>

        </form>
    </div>
</div>