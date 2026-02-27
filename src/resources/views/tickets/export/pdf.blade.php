<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        /* === WATERMARK === */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 60px;
            color: rgba(0, 0, 0, 0.08);
            text-align: center;
            z-index: -1000;
            width: 100%;
            white-space: nowrap;
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

        h2 {
            text-align: center;
            margin-bottom: 4px;
        }

        .meta {
            text-align: center;
            font-size: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    {{-- WATERMARK --}}
    <div class="watermark">
        SIPENA<br>
        SMK 17 Agustus 1945 Surabaya
    </div>

    <h2>Laporan Tiket Helpdesk</h2>

    <div class="meta">
        Dicetak: {{ $printedAt }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No Tiket</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $t)
                <tr>
                    <td>{{ $t->ticket_number }}</td>
                    <td>{{ $t->title }}</td>
                    <td>{{ $t->category?->name }}</td>
                    <td>{{ strtoupper($t->priority) }}</td>
                    <td>{{ ucfirst($t->status) }}</td>
                    <td>{{ $t->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
