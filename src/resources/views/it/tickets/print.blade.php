<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Perintah Kerja</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
        }
        .subtitle {
            font-size: 12px;
        }
        .divider {
            border-top: 2px solid #000;
            margin: 10px 0 20px;
        }
        .section {
            margin-bottom: 15px;
        }
        .label {
            width: 140px;
            display: inline-block;
            font-weight: bold;
        }
        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 5px;
        }
        .signatures {
            margin-top: 50px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            padding-top: 40px;
        }
        .small {
            font-size: 10px;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="title">SURAT PERINTAH KERJA</div>
    <div class="subtitle">
        SMK 17 Agustus 1945 Surabaya
    </div>
</div>

<div class="divider"></div>

<div class="section">
    <div><span class="label">No Tiket</span>: {{ $ticket->ticket_number }}</div>
    <div><span class="label">Tanggal</span>: {{ now()->format('d F Y') }}</div>
    <div><span class="label">Kategori</span>: {{ $ticket->laporan?->kategori?->nama ?? '-' }}</div>
    <div><span class="label">Prioritas</span>: {{ ucfirst($ticket->priority) }}</div>
    <div><span class="label">Vendor</span>: {{ $ticket->external_vendor ?? '-' }}</div>
</div>

<div class="section">
    <strong>Judul Pekerjaan:</strong>
    <div class="box">
        {{ $ticket->title }}
    </div>
</div>

<div class="section">
    <strong>Deskripsi Pekerjaan:</strong>
    <div class="box">
        {{ $ticket->description }}
    </div>
</div>

<table class="signatures">
    <tr>
        <td>
            Vendor<br><br>
            ___________________________<br>
            Nama & Tanda Tangan
        </td>
        <td>
            Sarpras<br><br>
            ___________________________<br>
            Nama & Tanda Tangan
        </td>
    </tr>
</table>

<div class="small">
    Dicetak: {{ $printedAt }}
</div>

</body>
</html>
