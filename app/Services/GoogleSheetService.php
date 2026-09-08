<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Setoran;
use App\Models\SetoranDetail;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use DB;
use Sheets;

class GoogleSheetService
{
    /**
     * Get programs ordered by input order (transaksi_detail min id),
     * programs with donations first, programs with 0/no donation last.
     */
    protected function getOrderedPrograms()
    {
        // Get program order based on first appearance in transaksi_detail (input order)
        $programOrder = TransaksiDetail::select('program_id', DB::raw('MIN(id) as first_input'))
            ->groupBy('program_id')
            ->orderBy('first_input', 'asc')
            ->pluck('first_input', 'program_id');

        // Get all programs
        $programs = Program::orderBy('id', 'asc')->get();

        // Sort: programs with donation (in transaksi_detail) first by input order,
        // then programs without donation (0) last by id
        $sorted = $programs->sort(function ($a, $b) use ($programOrder) {
            $aHas = $programOrder->has($a->id);
            $bHas = $programOrder->has($b->id);
            if ($aHas && $bHas) {
                return $programOrder[$a->id] <=> $programOrder[$b->id];
            }
            if ($aHas && !$bHas) {
                return -1;
            }
            if (!$aHas && $bHas) {
                return 1;
            }
            return $a->id <=> $b->id;
        })->values();

        return $sorted;
    }

    public function storeSheet()
    {
        $transaksi = Transaksi::leftJoin('transaksi_detail as td', 'transaksi.id', '=', 'td.transaksi_id')
            ->leftJoin('pegawai as p', 'transaksi.pegawai_id', '=', 'p.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->select([
                'transaksi.tanggal',
                'p.nama as relawan',
                'd.nama as donatur',
                'd.alamat',
                'd.no_telepon',
                'transaksi.jenis_transaksi',
                'transaksi.keterangan',
                DB::raw('json_agg(td.*) as donasi'),
            ])
            ->groupBy([
                'transaksi.tanggal',
                'p.nama',
                'd.nama',
                'd.alamat',
                'd.no_telepon',
                'transaksi.keterangan',
                'transaksi.jenis_transaksi',
            ])
            ->orderBy('transaksi.tanggal', 'asc')
            ->get();

        $program = $this->getOrderedPrograms();
        $data = [];
        $header = [];
        $header[] = 'Tanggal';
        $header[] = 'Penghimpun';
        $header[] = 'Nasabah';
        $header[] = 'Alamat Nasabah';
        $header[] = 'Nomor HP Nasabah';
        foreach ($program as $row) {
            $header[] = $row->nama;
        }
        $header[] = 'Keterangan';
        $header[] = 'Jenis Pembayaran';
        $data[] = $header;

        foreach ($transaksi as $item) {
            $list = [];
            $list[] = date('Y-m-d', strtotime($item->tanggal));
            $list[] = $item->relawan;
            $list[] = $item->donatur;
            $list[] = isset($item->alamat) ? $item->alamat : '';
            $list[] = isset($item->no_telepon) ? $item->no_telepon : '';
            $donasi = json_decode($item->donasi);
            $donasiProgram = array_reduce($donasi, function ($acc, $d) {
                $acc[$d->program_id] = $d;

                return $acc;
            }, []);
            foreach ($program as $row) {
                $list[] = isset($donasiProgram[$row->id]) ? $donasiProgram[$row->id]->nominal_donasi : 0;
            }
            $list[] = isset($item->keterangan) ? $item->keterangan : '';
            $jenis_pembayaran = $item->jenis_transaksi;
            if ($jenis_pembayaran == 'cash') {
                $list[] = 'Titip di Penghimpun';
            } else {
                $list[] = 'Transfer ke Rek ULAMA';
            }
            $data[] = $list;
        }

        Sheets::spreadsheet(config('google.spread_sheet_id'))->sheet('report')->append($data);
    }

    public function storeTransaksi($idTransaksi, $donasi)
    {
        $transaksi = Transaksi::leftJoin('pegawai as p', 'transaksi.pegawai_id', '=', 'p.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->select([
                'transaksi.tanggal',
                'p.nama as relawan',
                'd.nama as donatur',
                'd.alamat',
                'd.no_telepon',
                'transaksi.keterangan',
                'transaksi.jenis_transaksi',
            ])
            ->where('transaksi.id', $idTransaksi)
            ->first();

        $program = $this->getOrderedPrograms();

        $list[] = date('Y-m-d', strtotime($transaksi->tanggal));
        $list[] = $transaksi->relawan;
        $list[] = $transaksi->donatur;
        $list[] = isset($transaksi->alamat) ? $transaksi->alamat : '';
        $list[] = isset($transaksi->no_telepon) ? $transaksi->no_telepon : '';

        foreach ($program as $row) {
            $list[] = isset($donasi[$row->id]) ? $donasi[$row->id] : 0;
        }
        $list[] = isset($transaksi->keterangan) ? $transaksi->keterangan : '';
        $jenis_pembayaran = $transaksi->jenis_transaksi;
        if ($jenis_pembayaran == 'cash') {
            $list[] = 'Titip di Penghimpun';
        } else {
            $list[] = 'Transfer ke Rek ULAMA';
        }
        // dd($list);
        $data[] = $list;
        Sheets::spreadsheet(config('google.spread_sheet_id'))->sheet('report')->append($data);
    }

    public function firstStore()
    {
        $setor = Setoran::leftJoin('file as f', 'setoran.file_id', '=', 'f.id')
            ->leftJoin('pegawai as p', 'setoran.pegawai_id', '=', 'p.id')
            ->select([
                'setoran.created_at',
                'setoran.id',
                'p.nama as relawan',
                'f.path',
                'f.nama as nama_file',
                'setoran.total_setoran',
            ])
            ->get();
        $data = [];
        foreach ($setor as $item) {
            $header = ['Tanggal', 'Nama Penghimpun', 'Total Setoran', 'Bukti Setoran'];
            $dataSetor = [];
            $dataSetor[] = date('Y-m-d', strtotime($item->created_at));
            $dataSetor[] = $item->relawan;
            $dataSetor[] = $item->total_setoran;
            $dataSetor[] = asset($item->path.$item->nama_file);
            $data[] = $header;
            $data[] = $dataSetor;
            $detail = SetoranDetail::leftJoin('transaksi as t', 'setoran_detail.transaksi_id', '=', 't.id')
                ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
                ->leftJoin('donatur as d', 't.donatur_id', '=', 'd.id')
                ->where('setoran_detail.setoran_id', $item->id)
                ->select([
                    't.tanggal',
                    'd.nama as donatur',
                    'd.alamat',
                    'd.no_telepon',
                    DB::raw('sum(nominal_donasi) as total_donasi'),
                ])
                ->groupBy([
                    't.tanggal',
                    'd.nama',
                    'd.alamat',
                    'd.no_telepon',
                ])->get();
            foreach ($detail as $row) {
                $detailSetor = [];
                $detailSetor[] = date('Y-m-d', strtotime($row->tanggal));
                $detailSetor[] = $item->relawan;
                $detailSetor[] = $row->donatur;
                $detailSetor[] = $row->alamat;
                $detailSetor[] = $row->no_telepon;
                $detailSetor[] = $row->total_donasi;
                $data[] = $detailSetor;
            }
        }
        Sheets::spreadsheet(config('google.spread_sheet_id'))->sheet('setoran')->append($data);
    }

    public function storeSetoran($setor_id)
    {
        $setor = Setoran::leftJoin('file as f', 'setoran.file_id', '=', 'f.id')
            ->leftJoin('pegawai as p', 'setoran.pegawai_id', '=', 'p.id')
            ->where('setoran.id', $setor_id)
            ->select([
                'p.nama as relawan',
                'f.path',
                'f.nama as nama_file',
                'setoran.total_setoran',
            ])
            ->first();
        $detail = SetoranDetail::leftJoin('transaksi as t', 'setoran_detail.transaksi_id', '=', 't.id')
            ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
            ->leftJoin('donatur as d', 't.donatur_id', '=', 'd.id')
            ->where('setoran_detail.setoran_id', $setor_id)
            ->select([
                't.tanggal',
                'd.nama as donatur',
                'd.alamat',
                'd.no_telepon',
                DB::raw('sum(nominal_donasi) as total_donasi'),
            ])
            ->groupBy([
                't.tanggal',
                'd.nama',
                'd.alamat',
                'd.no_telepon',
            ])->get();
        $data = [];
        $header = [];
        $header[] = 'Tanggal';
        $header[] = 'Nama Penghimpun';
        $header[] = 'Total Setoran';
        $header[] = 'Bukti Setoran';
        $data[] = $header;
        $dataSetor = [];
        $dataSetor[] = date('Y-m-d');
        $dataSetor[] = $setor->relawan;
        $dataSetor[] = $setor->total_setoran;
        $dataSetor[] = asset($setor->path.$setor->nama_file);
        $data[] = $dataSetor;
        foreach ($detail as $item) {
            $detailSetor = [];
            $detailSetor[] = date('Y-m-d', strtotime($item->tanggal));
            $detailSetor[] = $setor->relawan;
            $detailSetor[] = $item->donatur;
            $detailSetor[] = isset($item->alamat) ? $item->alamat : '';
            $detailSetor[] = isset($item->no_telepon) ? $item->no_telepon : '';
            $detailSetor[] = $item->total_donasi;
            $data[] = $detailSetor;
        }
        Sheets::spreadsheet(config('google.spread_sheet_id'))->sheet('setoran')->append($data);
    }
}
