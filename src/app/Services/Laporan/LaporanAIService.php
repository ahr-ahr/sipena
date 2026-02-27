<?php

namespace App\Services\Laporan;

use GuzzleHttp\Client;

class LaporanAIService
{
    protected function client()
    {
        return new Client([
            'base_uri' => config('services.ollama.url'),
            'timeout' => 60,
            'connect_timeout' => 5,
        ]);
    }

    public function classify(string $kategori, string $judul, string $deskripsi): array
{
    $prompt = <<<PROMPT
Kamu adalah sistem validasi laporan sekolah.

Kategori yang dipilih siswa:
"$kategori"

Daftar kategori resmi yang boleh dipakai:
- Pengaduan Fasilitas
- Pengaduan Akademik
- Pengaduan Disiplin
- Aspirasi Siswa
- Laporan Keamanan
- Lainnya

Arti masing-masing kategori:

1. Pengaduan Fasilitas
   - Kerusakan kursi, meja, toilet, listrik, AC, proyektor, dll.
   - Kebersihan atau sarana sekolah.

2. Pengaduan Akademik
   - Masalah nilai, tugas, materi, guru, jadwal, ujian, dll.

3. Pengaduan Disiplin
   - Bullying, perkelahian, pelanggaran aturan, konflik antar siswa.

4. Aspirasi Siswa
   - Saran, ide, masukan, permintaan kegiatan, usulan perubahan.

5. Laporan Keamanan
   - Ancaman, orang mencurigakan, bahaya fisik, keamanan lingkungan.

6. Lainnya
   - Laporan yang tidak cocok dengan kategori di atas.

Aturan penting:
- Anggap laporan sebagai "valid" jika berisi keluhan, masalah, atau saran.
- Keluhan tentang nilai, tugas, guru, atau pelajaran SELALU dianggap valid.
- Jangan menganggap laporan sebagai bercanda atau spam hanya karena pendek.
- Gunakan kategori HANYA dari daftar kategori resmi di atas.

Label yang tersedia:
- valid
- bercanda
- spam
- bullying
- ancaman

Tugas kamu:
1. Tentukan label laporan.
2. Tentukan apakah isi laporan SESUAI dengan kategori yang dipilih.
3. Jika tidak sesuai:
   - pilih satu kategori dari daftar resmi
   - isi sebagai "saran_kategori"
4. Jika sudah sesuai:
   - set "saran_kategori" menjadi null

Jawab HANYA dalam format JSON berikut:
{
  "label": "valid",
  "score": 0.95,
  "kategori_sesuai": true,
  "saran_kategori": null
}

Judul:
$judul

Deskripsi:
$deskripsi
PROMPT;

    $response = $this->client()->post('/api/generate', [
        'json' => [
            'model' => env('OLLAMA_MODEL', 'llama3.2:3b'),
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0,
                'num_predict' => 64,
            ],
        ]
    ]);

    $body = json_decode($response->getBody(), true);
    $content = trim($body['response'] ?? '');

    preg_match('/\{.*\}/s', $content, $matches);
    $json = $matches[0] ?? '{}';

    $data = json_decode($json, true);

    return [
        'label' => $data['label'] ?? 'valid',
        'score' => $data['score'] ?? 0.5,
        'kategori_sesuai' => $data['kategori_sesuai'] ?? true,
        'saran_kategori' => $data['saran_kategori'] ?? $kategori,
    ];

    }
}
