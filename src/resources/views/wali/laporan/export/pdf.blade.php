<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        /* === WATERMARK === */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 60px;
            color: rgba(0, 0, 0, 0.08);
            text-align: center;
            z-index: -1000;
            width: 100%;
            white-space: nowrap;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }
    </style>
</head>
<body>

    {{-- WATERMARK --}}
    <div class="watermark">
        SIPENA<br>
        SMK 17 Agustus 1945 Surabaya
    </div>

    <h2>
        Laporan Pengaduan & Aspirasi
    </h2>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporan as $l)
                <tr>
                    <td>{{ $l->kode_laporan }}</td>
                    <td>{{ $l->judul }}</td>
                    <td>{{ $l->kategori?->nama ?? '-' }}</td>
                    <td>
                        {{ $l->status?->label() ?? $l->status }}
                    </td>
                    <td>
                        {{ optional($l->created_at)->format('d/m/Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">
                        Tidak ada data laporan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
