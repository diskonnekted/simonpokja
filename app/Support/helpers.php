<?php

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka, $prefix = 'Rp ')
    {
        return $prefix . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('format_rupiah_singkat')) {
    function format_rupiah_singkat($angka)
    {
        $angka = (float) $angka;
        if ($angka >= 1_000_000_000_000) {
            return 'Rp ' . rtrim(rtrim(number_format($angka / 1_000_000_000_000, 1, ',', '.'), '0'), ',') . ' T';
        }
        if ($angka >= 1_000_000_000) {
            return 'Rp ' . rtrim(rtrim(number_format($angka / 1_000_000_000, 1, ',', '.'), '0'), ',') . ' M';
        }
        if ($angka >= 1_000_000) {
            return 'Rp ' . rtrim(rtrim(number_format($angka / 1_000_000, 1, ',', '.'), '0'), ',') . ' jt';
        }
        if ($angka >= 1_000) {
            return 'Rp ' . rtrim(rtrim(number_format($angka / 1_000, 1, ',', '.'), '0'), ',') . ' rb';
        }
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('format_tanggal_id')) {
    function format_tanggal_id($tanggal, $dengan_jam = false)
    {
        if (!$tanggal) return '-';
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $ts = strtotime($tanggal);
        $hasil = date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        if ($dengan_jam) {
            $hasil .= ' ' . date('H:i', $ts) . ' WIB';
        }
        return $hasil;
    }
}

if (!function_exists('status_badge_class')) {
    function status_badge_class($status)
    {
        return match ($status) {
            'draft' => 'secondary status-draft',
            'persiapan' => 'info status-persiapan',
            'pemilihan' => 'warning status-pemilihan',
            'kontrak' => 'primary status-kontrak',
            'pelaksanaan' => 'purple status-pelaksanaan',
            'selesai' => 'success status-selesai',
            'batal' => 'danger status-batal',
            default => 'secondary status-draft',
        };
    }
}
