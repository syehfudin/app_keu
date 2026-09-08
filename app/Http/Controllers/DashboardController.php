<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Pegawai;
use App\Models\Korel;
use Auth;
use DB;
use Illuminate\Http\Request;
use App\Traits\HasHierarchy;

class DashboardController extends Controller
{
    use HasHierarchy;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $title = 'Dashboard';
        $role = strtolower(Auth::user()->roles[0]->name);
        $isAdminOrManager = in_array($role, ['admin', 'manager']);

        $tanggalInput = $request->input('tanggal', date('d-m-Y'));
        $bulanInput = $request->input('bulan', date('Y-m'));

        $tanggal = date('Y-m-d', strtotime($tanggalInput));
        $tanggalDisplay = date('d-m-Y', strtotime($tanggal));

        $bulanParts = explode('-', $bulanInput);
        $selectedYear = $bulanParts[0] ?? date('Y');
        $selectedMonth = $bulanParts[1] ?? date('m');
        $bulanDisplay = date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));

        $accessibleIds = $this->getAccessiblePegawaiIds();

        $dailyPenghimpun = $this->getPenghimpunReport($tanggal, 'daily', $accessibleIds);
        $monthlyPenghimpun = $this->getPenghimpunReport($selectedYear . '-' . $selectedMonth, 'monthly', $accessibleIds);

        $dailySupervisor = [];
        $monthlySupervisor = [];
        if ($isAdminOrManager) {
            $dailySupervisor = $this->getSupervisorReport($tanggal, 'daily');
            $monthlySupervisor = $this->getSupervisorReport($selectedYear . '-' . $selectedMonth, 'monthly');
        }

        $yearlySupervisor = [];
        if ($isAdminOrManager) {
            $yearlySupervisor = $this->getSupervisorYearlyReport($selectedYear);
        }

        return view('dashboard.index', compact(
            'title', 'role', 'isAdminOrManager',
            'dailyPenghimpun', 'monthlyPenghimpun',
            'dailySupervisor', 'monthlySupervisor',
            'yearlySupervisor', 'selectedYear',
            'tanggalInput', 'bulanInput', 'tanggalDisplay', 'bulanDisplay'
        ));
    }

    private function getPenghimpunReport($date, $type, $accessibleIds)
    {
        $query = Pegawai::select([
                'pegawai.id',
                'pegawai.nama as nama_penghimpun',
                DB::raw('COUNT(DISTINCT t.id) as jumlah_transaksi'),
                DB::raw('COUNT(DISTINCT d.id) as jumlah_nasabah'),
                DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as total_nominal'),
            ])
            ->leftJoin('transaksi as t', 't.pegawai_id', '=', 'pegawai.id')
            ->leftJoin('transaksi_detail as td', 'td.transaksi_id', '=', 't.id')
            ->leftJoin('donatur as d', function ($join) {
                $join->on('d.pegawai_id', '=', 'pegawai.id')
                    ->where('d.id', '!=', null);
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users as u')
                    ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                    ->whereColumn('u.pegawai_id', 'pegawai.id')
                    ->whereRaw("lower(r.name) = 'penghimpun'");
            })
            ->groupBy('pegawai.id', 'pegawai.nama');

        if ($type == 'daily') {
            $query->where('t.tanggal', $date);
        } elseif ($type == 'monthly') {
            $query->whereMonth('t.tanggal', date('n', strtotime($date . '-01')))
                  ->whereYear('t.tanggal', date('Y', strtotime($date . '-01')));
        }

        if ($accessibleIds !== null) {
            $query->whereIn('pegawai.id', $accessibleIds);
        }

        return $query->orderBy('pegawai.nama')->get();
    }

    private function getSupervisorReport($date, $type)
    {
        $supervisors = Pegawai::select([
                'pegawai.id',
                'pegawai.nama as nama_supervisor',
                DB::raw('COUNT(DISTINCT t.id) as jumlah_transaksi'),
                DB::raw('COUNT(DISTINCT d.id) as jumlah_nasabah'),
                DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as total_nominal'),
            ])
            ->leftJoin('korel as k', 'k.kepala_id', '=', 'pegawai.id')
            ->leftJoin('pegawai as bawahan', function ($join) {
                $join->on('bawahan.id', '=', 'k.bawahan_id')
                    ->orOn('bawahan.id', '=', 'pegawai.id');
            })
            ->leftJoin('transaksi as t', 't.pegawai_id', '=', 'bawahan.id')
            ->leftJoin('transaksi_detail as td', 'td.transaksi_id', '=', 't.id')
            ->leftJoin('donatur as d', function ($join) {
                $join->on('d.pegawai_id', '=', 'bawahan.id');
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users as u')
                    ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                    ->whereColumn('u.pegawai_id', 'pegawai.id')
                    ->whereIn(DB::raw('lower(r.name)'), ['supervisor', 'manager']);
            })
            ->groupBy('pegawai.id', 'pegawai.nama');

        if ($type == 'daily') {
            $supervisors->where('t.tanggal', $date);
        } elseif ($type == 'monthly') {
            $supervisors->whereMonth('t.tanggal', date('n', strtotime($date . '-01')))
                        ->whereYear('t.tanggal', date('Y', strtotime($date . '-01')));
        }

        return $supervisors->orderBy('pegawai.nama')->get();
    }

    private function getSupervisorYearlyReport($year)
    {
        $supervisors = Pegawai::select([
                'pegawai.id',
                'pegawai.nama as nama_supervisor',
                DB::raw('COUNT(DISTINCT d.id) as jumlah_nasabah_terdaftar'),
            ])
            ->leftJoin('korel as k', 'k.kepala_id', '=', 'pegawai.id')
            ->leftJoin('pegawai as bawahan', function ($join) {
                $join->on('bawahan.id', '=', 'k.bawahan_id')
                    ->orOn('bawahan.id', '=', 'pegawai.id');
            })
            ->leftJoin('donatur as d', 'd.pegawai_id', '=', 'bawahan.id')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users as u')
                    ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                    ->whereColumn('u.pegawai_id', 'pegawai.id')
                    ->whereIn(DB::raw('lower(r.name)'), ['supervisor', 'manager']);
            })
            ->groupBy('pegawai.id', 'pegawai.nama')
            ->orderBy('pegawai.nama')
            ->get();

        // For each supervisor, get monthly nasabah tunai count and nominal
        foreach ($supervisors as $sup) {
            $bawahanIds = Korel::where('kepala_id', $sup->id)->pluck('bawahan_id')->toArray();
            $bawahanIds[] = $sup->id;

            for ($m = 1; $m <= 12; $m++) {
                $data = Donatur::leftJoin('transaksi as t', function ($join) use ($m, $year) {
                        $join->on('donatur.id', '=', 't.donatur_id')
                            ->whereMonth('t.tanggal', $m)
                            ->whereYear('t.tanggal', $year)
                            ->whereIn('t.jenis_transaksi', ['cash', 'transfer']);
                    })
                    ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
                    ->whereIn('donatur.pegawai_id', $bawahanIds)
                    ->select([
                        DB::raw('COUNT(DISTINCT donatur.id) as jumlah_nasabah'),
                        DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as nominal'),
                    ])
                    ->first();

                $sup->{'m' . $m . '_nasabah'} = $data->jumlah_nasabah ?? 0;
                $sup->{'m' . $m . '_nominal'} = $data->nominal ?? 0;
            }
        }

        return $supervisors;
    }
}
