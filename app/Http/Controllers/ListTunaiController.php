<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Korel;
use Auth;
use DB;
use Illuminate\Http\Request;
use App\Traits\HasHierarchy;

class ListTunaiController extends Controller
{
    use HasHierarchy;

    public function __construct()
    {
        $this->middleware('permission:tunai-list');
    }

    public function index(Request $request)
    {
        $title = 'List Tunai';
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));

        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $accessibleIds = $this->getAccessiblePegawaiIds();

        $query = Nasabah::leftJoin('pegawai as p', 'nasabah.pegawai_id', '=', 'p.id')
            ->leftJoin('transaksi as t', function ($join) use ($bulan, $tahun) {
                $join->on('nasabah.id', '=', 't.nasabah_id')
                    ->whereIn('t.jenis_transaksi', ['cash', 'transfer', 'rek_ulama'])
                    ->whereMonth('t.tanggal', $bulan)
                    ->whereYear('t.tanggal', $tahun);
            })
            ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
            ->leftJoin('setoran_detail as sd', 'sd.transaksi_id', '=', 't.id')
            ->select([
                'nasabah.id', 'nasabah.nama', 'nasabah.no_telepon', 'nasabah.alamat',
                'p.nama as nama_penghimpun', 'nasabah.pegawai_id',
                DB::raw('COUNT(DISTINCT t.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as total_donasi'),
                DB::raw("COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'transfer' THEN td.nominal_donasi ELSE 0 END), 0) + COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'cash' AND sd.id IS NOT NULL THEN td.nominal_donasi ELSE 0 END), 0) as total_sudah_setor"),
                DB::raw("COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'cash' AND sd.id IS NULL THEN td.nominal_donasi ELSE 0 END), 0) as total_belum_setor"),
                DB::raw("COUNT(DISTINCT CASE WHEN t.jenis_transaksi = 'cash' AND sd.id IS NULL THEN t.id END) as cnt_cash_belum"),
                DB::raw("COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'rek_ulama' THEN td.nominal_donasi ELSE 0 END), 0) as total_rek_ulama"),
            ])
            ->groupBy(['nasabah.id', 'nasabah.nama', 'nasabah.no_telepon', 'nasabah.alamat', 'p.nama', 'nasabah.pegawai_id']);

        if ($accessibleIds !== null) {
            $query->whereIn('nasabah.pegawai_id', $accessibleIds);
        }

        $data = $query->orderBy('nasabah.nama', 'asc')->get();

        $totalSemua = $data->sum('total_donasi');
        $totalTransaksi = $data->sum('jumlah_transaksi');
        $totalSudahSetor = $data->sum('total_sudah_setor');
        $totalBelumSetor = $data->sum('total_belum_setor');

        // ===== Summary Setoran =====
        $totalRekUlama = $data->sum('total_rek_ulama');
        // Total Setoran Kasie = Setoran Transfer + Cash yang sudah disetor
        $totalKasie = $totalSudahSetor;
        // Total Setoran = Kasie + Rek Ulama
        $totalSetoran = $totalKasie + $totalRekUlama;

        return view('tunai.index', compact(
            'title', 'data', 'bulanList', 'bulan', 'tahun',
            'totalSemua', 'totalTransaksi', 'totalSudahSetor', 'totalBelumSetor',
            'totalRekUlama', 'totalKasie', 'totalSetoran'
        ));
    }
}
