<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center">
    <div class="bg-white rounded w-1/2 p-6">
        <h2 class="text-lg font-semibold mb-2" id="modal-title"></h2>
        <p id="modal-desc" class="mb-4"></p>

        <div id="modal-attachments" class="grid grid-cols-3 gap-2"></div>

        <button onclick="closeModal()"
            class="mt-4 rounded bg-gray-700 px-4 py-2 text-white">
            Tutup
        </button>
    </div>
</div>

<script>
function openModal(data) {
    document.getElementById('modal-title').innerText = data.judul
    document.getElementById('modal-desc').innerText = data.deskripsi

    document.getElementById('modal').classList.remove('hidden')
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden')
}
</script>
