@props([
    'laporanSelesai' => collect(),
    'itUsers'  => collect(),
])
<div id="create-ticket-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center
            bg-black/50 backdrop-blur-sm"
     onclick="closeCreateModal()">

    <div
        onclick="event.stopPropagation()"
        class="w-full max-w-xl rounded-2xl
       bg-white dark:bg-[#161615]
       p-6 shadow-xl space-y-5
       max-h-[95vh] overflow-y-auto">

        <h3 class="text-xl font-semibold">
            Buat Tiket Baru
        </h3>

        <form id="create-ticket-form"
              class="space-y-4"
              onsubmit="submitCreateTicket(event)">

            {{-- LAPORAN --}}
<div>
    <label class="text-sm font-medium">Laporan</label>
    <select name="laporan_id"
        class="w-full mt-1 px-3 py-2 rounded-lg border
               bg-white dark:bg-[#0f0f0f]
               border-[#19140035] dark:border-[#3E3E3A]">

        <option value="">Pilih Laporan</option>

        @foreach ($laporanSelesai as $l)
            <option value="{{ $l->id }}">
                {{ $l->kode_laporan }} — {{ $l->judul }}
            </option>
        @endforeach
    </select>
</div>

            {{-- PRIORITY --}}
            <div>
                <label class="text-sm font-medium">Prioritas</label>
                <select name="priority"
                    class="w-full mt-1 px-3 py-2 rounded-lg border
                           bg-white dark:bg-[#0f0f0f]
                           border-[#19140035] dark:border-[#3E3E3A]">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>

            {{-- TITLE --}}
            <div>
                <label class="text-sm font-medium">Judul</label>
                <input name="title" type="text"
                    class="w-full mt-1 px-3 py-2 rounded-lg border
                           bg-white dark:bg-[#0f0f0f]
                           border-[#19140035] dark:border-[#3E3E3A]">
            </div>

            {{-- DESCRIPTION --}}
            <div>
                <label class="text-sm font-medium">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full mt-1 px-3 py-2 rounded-lg border
                           bg-white dark:bg-[#0f0f0f]
                           border-[#19140035] dark:border-[#3E3E3A]"></textarea>
            </div>

            {{-- ASSIGN TYPE --}}
            <div>
                <label class="text-sm font-medium">Penugasan</label>
                <select id="assign-type"
                        class="w-full mt-1 px-3 py-2 rounded-lg border
                               bg-white dark:bg-[#0f0f0f]
                               border-[#19140035] dark:border-[#3E3E3A]">
                    <option value="internal">Teknisi Internal</option>
                    <option value="external">Vendor Eksternal</option>
                </select>
            </div>

            {{-- INTERNAL ASSIGNEE --}}
            <div id="internal-box">
                <label class="text-sm font-medium">Teknisi</label>
                <select name="assigned_to"
                    class="w-full mt-1 px-3 py-2 rounded-lg border
                           bg-white dark:bg-[#0f0f0f]
                           border-[#19140035] dark:border-[#3E3E3A]">
                    <option value="">Pilih Teknisi</option>
                    @foreach ($itUsers as $it)
    <option value="{{ $it->id }}">
        {{ $it->pegawaiProfile->nama ?? $it->email }}
    </option>
@endforeach
                </select>
            </div>

            {{-- EXTERNAL VENDOR --}}
            <div id="external-box" class="hidden space-y-2">
                <div>
                    <label class="text-sm font-medium">Nama Vendor</label>
                    <input name="external_vendor" type="text"
                        class="w-full mt-1 px-3 py-2 rounded-lg border
                               bg-white dark:bg-[#0f0f0f]
                               border-[#19140035] dark:border-[#3E3E3A]">
                </div>

                <div>
                    <label class="text-sm font-medium">Catatan Vendor</label>
                    <textarea name="external_notes" rows="2"
                        class="w-full mt-1 px-3 py-2 rounded-lg border
                               bg-white dark:bg-[#0f0f0f]
                               border-[#19140035] dark:border-[#3E3E3A]"></textarea>
                </div>
            </div>

            {{-- ATTACHMENTS --}}
            <div>
                <label class="text-sm font-medium">Lampiran</label>
                <input type="file" name="attachments[]" multiple
                    class="w-full mt-1 text-sm">
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-2 pt-4">
                <button type="button"
                        onclick="closeCreateModal()"
                        class="px-4 py-2 rounded-lg border
                               border-[#19140035] dark:border-[#3E3E3A]">
                    Batal
                </button>

                <button type="submit"
                        class="px-4 py-2 rounded-lg
                               bg-black text-white
                               dark:bg-white dark:text-black">
                    Simpan Tiket
                </button>
            </div>

        </form>
    </div>
</div>