<?php

namespace App\Http\Controllers;

use App\Exports\MuzakkiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MuzakkiExportController extends Controller
{
    /**
     * GET /api/muzakki/export
     * Export daftar Muzakki ke Excel (.xlsx)
     */
    public function export(Request $request)
    {
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $filename = 'Rekap_Daftar_Muzakki_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new MuzakkiExport($search, $kategori), $filename);
    }
}
