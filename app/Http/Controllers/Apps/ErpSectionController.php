<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ErpSectionController extends Controller
{
    /**
     * Landing page per grup ERP (PENGADAAN & PENJUALAN, KEUANGAN & HRM, SISTEM & ALUR)
     * — supaya grup sidebar tidak lagi terbuka kosong/putih.
     */
    public function show(Request $request, string $section)
    {
        return Inertia::render('Apps/ERP/Index', [
            'section' => $section,
        ]);
    }
}