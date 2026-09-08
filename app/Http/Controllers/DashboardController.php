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

    public function index()
    {
        $title = 'Dashboard';
        $role = strtolower(Auth::user()->roles[0]->name);
        $isAdminOrManager = in_array($role, ['admin', 'manager']);

        // Today and current month/year
        $today = date('Y-m-d');
        $currentMonth = date('n');
        $currentYear = date('Y');

        $accessibleIds = $this->getAccessiblePegawaiIds();

        // 1. Report per Penghimpun - Daily
        $dailyPenghimpun = $this->getPenghimpunReport($today, 'daily', $accessibleIds);

        // 2. Report per Penghimpun - Monthly
        $monthlyPenghimpun = $this->getPenghimpunReport($currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT), 'monthly', $accessibleIds);

        // 3. Report per Supervisor - Daily & Monthly (Admin/Manager only)
        $dailySupervisor = [];
        $monthlySupervisor = [];
        if ($isAdminOrManager) {
            $dailySupervisor = $this->getSupervisorReport($today, 'daily');
            $monthlySupervisor = $this->getSupervisorReport($currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT), 'monthly');
        }

        // 4. Yearly Report per Supervisor
        $yearlySupervisor = [];
        if ($isAdminOrManager) {
            $yearlySupervisor = $this->getSupervisorYearlyReport($currentYear);
        }

        return view('dashboard.index', compact(
            'title', 'role', 'isAdminOrManager',
            'dailyPenghimpun', 'monthlyPenghimpun',
            'dailySupervisor', 'monthlySupervisor',
            'yearlySupervisor', 'currentYear'
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
