<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Pegawai;
use Auth;
use DB;
use Illuminate\Http\Request;

class ListTunaiController extends Controller
{
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

        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;

        $query = Donatur::leftJoin('pegawai as p', 'donatur.pegawai_id', '=', 'p.id')
            ->leftJoin('transaksi as t', function ($join) use ($bulan, $tahun) {
                $join->on('donatur.id', '=', 't.donatur_id')
                    ->where('t.jenis_transaksi', 'cash')
                    ->whereMonth('t.tanggal', $bulan)
                    ->whereYear('t.tanggal', $tahun);
            })
            ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
            ->select([
                'donatur.id',
                'donatur.nama',
                'donatur.no_telepon',
                'donatur.alamat',
                'p.nama as nama_penghimpun',
                'donatur.pegawai_id',
                DB::raw('COUNT(DISTINCT t.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as total_donasi'),
            ])
            ->groupBy([
                'donatur.id', 'donatur.nama', 'donatur.no_telepon',
                'donatur.alamat', 'p.nama', 'donatur.pegawai_id'
            ]);

        if ($role == 'penghimpun') {
            $query->where('donatur.pegawai_id', $pegawai_id);
        }

        $data = $query->orderBy('donatur.nama', 'asc')->get();

        $totalSemua = $data->sum('total_donasi');
        $totalTransaksi = $data->sum('jumlah_transaksi');

        return view('tunai.index', compact(
            'title', 'data', 'bulanList', 'bulan', 'tahun',
            'totalSemua', 'totalTransaksi'
        ));
    }
}
