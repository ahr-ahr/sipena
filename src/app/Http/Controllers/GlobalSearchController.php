<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\Ticket;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim($request->q);

        if (!$q) {
            return response()->json([
                'laporan' => [],
                'tickets' => [],
            ]);
        }

        // ================= LAPORAN =================
        $laporan = Laporan::query()
            ->with('kategori')
            ->where(function ($query) use ($q) {
                $query->where('judul', 'like', "%{$q}%")
                      ->orWhere('kode_laporan', 'like', "%{$q}%");
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($l) {
                return [
                    'id'     => $l->id,
                    'uuid'   => $l->uuid,
                    'type'   => 'laporan',
                    'kode'   => $l->kode_laporan,
                    'judul'  => $l->judul,
                    'status' => $l->status,
                ];
            });

        // ================= TICKETS =================
        $tickets = Ticket::query()
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('ticket_number', 'like', "%{$q}%");
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id'     => $t->id,
                    'type'   => 'ticket',
                    'kode'   => $t->ticket_number,
                    'judul'  => $t->title,
                    'status' => $t->status,
                ];
            });

        return response()->json([
            'laporan' => $laporan,
            'tickets' => $tickets,
        ]);
    }
}
