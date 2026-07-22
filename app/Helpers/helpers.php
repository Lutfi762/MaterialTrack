<?php

if (! function_exists('formatQty')) {
    /**
     * formatQty
     * Format angka stock/qty dengan pemisah ribuan + satuan.
     *
     * @param  mixed $qty
     * @param  string $unit
     * @return string
     */
    function formatQty($qty, $unit = '') {
        $formatted = number_format($qty, 0, '', '.');
        return trim($formatted . ' ' . $unit);
    }
}

if (! function_exists('formatDateIndo')) {
    /**
     * formatDateIndo
     * Format tanggal ke format Indonesia, contoh: 21 Juli 2026
     *
     * @param  mixed $date
     * @return string
     */
    function formatDateIndo($date) {
        if (empty($date)) {
            return '-';
        }

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $timestamp = is_numeric($date) ? $date : strtotime($date);

        $tanggal = date('d', $timestamp);
        $bulanAngka = (int) date('n', $timestamp);
        $tahun = date('Y', $timestamp);

        return $tanggal . ' ' . $bulan[$bulanAngka] . ' ' . $tahun;
    }
}

if (! function_exists('generateReferenceNo')) {
    /**
     * generateReferenceNo
     * Generate nomor referensi untuk transaksi stock in/out.
     * Contoh: IN/20260721/0001 atau OUT/20260721/0001
     *
     * @param  string $type   'IN' atau 'OUT'
     * @param  int    $number Urutan nomor pada hari itu
     * @return string
     */
    function generateReferenceNo($type = 'IN', $number = 1) {
        $type = strtoupper($type);
        $date = date('Ymd');
        $sequence = str_pad($number, 4, '0', STR_PAD_LEFT);

        return "{$type}/{$date}/{$sequence}";
    }
}

if (! function_exists('stockStatusLabel')) {
    /**
     * stockStatusLabel
     * Menentukan label status stock berdasarkan current stock vs minimal stock.
     * Berguna untuk badge di halaman index barang.
     *
     * @param  int $currentStock
     * @param  int $minimalStock
     * @return string  'Habis' | 'Menipis' | 'Aman'
     */
    function stockStatusLabel($currentStock, $minimalStock) {
        if ($currentStock <= 0) {
            return 'Habis';
        }

        if ($currentStock <= $minimalStock) {
            return 'Menipis';
        }

        return 'Aman';
    }
}