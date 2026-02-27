<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans; font-size: 11px; }

.watermark{
    position:fixed;
    top:50%; left:50%;
    transform:translate(-50%,-50%) rotate(-30deg);
    font-size:60px;
    color:rgba(0,0,0,.08);
    z-index:-1;
    text-align:center;
}

img { max-width:100%; }

table {
    width:100%;
    border-collapse:collapse;
}
th,td {
    border:1px solid #333;
    padding:6px;
}
th { background:#f2f2f2; }
</style>
</head>

<body>

<div class="watermark">
    SIPENA<br>
    SMK 17 Agustus 1945 Surabaya
</div>

<h2 style="text-align:center">History Aktivitas SIPENA</h2>
<p>Dicetak: {{ $printedAt }}</p>

{{-- CHART --}}
@if(!empty($charts))
    <h3>Ringkasan & Statistik</h3>

    <table style="border:none">
        <tr>
            <td style="border:none">
                <img src="{{ $charts['donut'] }}">
            </td>
        </tr>
        <tr>
            <td style="border:none">
                <img src="{{ $charts['timeline'] }}">
            </td>
        </tr>
        <tr>
            <td style="border:none">
                <img src="{{ $charts['stacked'] }}">
            </td>
        </tr>
    </table>
@endif

{{-- DATA --}}
<h3>Detail Riwayat</h3>

<table>
<thead>
<tr>
    <th>Jenis</th>
    <th>Kode</th>
    <th>Judul</th>
    <th>Status</th>
    <th>Tanggal</th>
</tr>
</thead>
<tbody>
@foreach($rows as $r)
<tr>
    <td>{{ $r['jenis'] }}</td>
    <td>{{ $r['kode'] }}</td>
    <td>{{ $r['judul'] }}</td>
    <td>{{ $r['status'] }}</td>
    <td>{{ $r['tanggal'] }}</td>
</tr>
@endforeach
</tbody>
</table>

</body>
</html>
