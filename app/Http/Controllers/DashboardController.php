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

        $tanggal = date('Y-m-d', strtotime($tanggalInput));
        $tanggalDisplay = date('d-m-Y', strtotime($tanggal));

        // Bulan & tahun diambil dari tanggal yang dipilih
        $selectedYear = date('Y', strtotime($tanggal));
        $selectedMonth = date('n', strtotime($tanggal));
        $bulanDisplay = date('F Y', strtotime($tanggal));

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

        $yearlyPenghimpun = $this->getPenghimpunYearlyReport($selectedYear, $accessibleIds);

        return view('dashboard.index', compact(
            'title', 'role', 'isAdminOrManager',
            'dailyPenghimpun', 'monthlyPenghimpun',
            'dailySupervisor', 'monthlySupervisor',
            'yearlySupervisor', 'yearlyPenghimpun', 'selectedYear',
            'tanggalInput', 'tanggalDisplay', 'bulanDisplay'
        ));
    }

    private function getPenghimpunReport($date, $type, $accessibleIds)
    {
        if ($type == 'daily') {
            // Harian: hanya penghimpun yang punya transaksi di tanggal tsb
            $query = Pegawai::select([
                    'pegawai.id',
                    'pegawai.nama as nama_penghimpun',
                    DB::raw('COUNT(DISTINCT t.id) as jumlah_transaksi'),
                    DB::raw('COUNT(DISTINCT d.id) as jumlah_nasabah'),
                    DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as total_nominal'),
                ])
                ->join('transaksi as t', function ($join) use ($date) {
                    $join->on('t.pegawai_id', '=', 'pegawai.id')
                        ->where('t.tanggal', $date);
                })
                ->leftJoin('transaksi_detail as td', 'td.transaksi_id', '=', 't.id')
                ->leftJoin('donatur as d', function ($join) use ($date) {
                    $join->on('d.pegawai_id', '=', 'pegawai.id');
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

            if ($accessibleIds !== null) {
                $query->whereIn('pegawai.id', $accessibleIds);
            }
            return $query->orderBy('pegawai.nama')->get();
        }

        // Bulanan: tampilkan SEMUA penghimpun (yang punya transaksi + yang tidak)
        $allPenghimpun = Pegawai::select([
                'pegawai.id',
                'pegawai.nama as nama_penghimpun',
            ])
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users as u')
                    ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                    ->whereColumn('u.pegawai_id', 'pegawai.id')
                    ->whereRaw("lower(r.name) = 'penghimpun'");
            })
            ->groupBy('pegawai.id', 'pegawai.nama');

        if ($accessibleIds !== null) {
            $allPenghimpun->whereIn('pegawai.id', $accessibleIds);
        }
        $allPenghimpun = $allPenghimpun->orderBy('pegawai.nama')->get();

        // Get transaksi data for the month
        $transaksiData = Transaksi::select([
                'transaksi.pegawai_id',
                DB::raw('COUNT(DISTINCT transaksi.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as total_nominal'),
            ])
            ->leftJoin('transaksi_detail as td', 'td.transaksi_id', '=', 'transaksi.id')
            ->whereMonth('transaksi.tanggal', date('n', strtotime($date . '-01')))
            ->whereYear('transaksi.tanggal', date('Y', strtotime($date . '-01')))
            ->groupBy('transaksi.pegawai_id')
            ->get()
            ->keyBy('pegawai_id');

        // Get nasabah count per penghimpun
        $nasabahData = Donatur::select('pegawai_id', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('pegawai_id')
            ->get()
            ->keyBy('pegawai_id');

        foreach ($allPenghimpun as $ph) {
            $t = $transaksiData->get($ph->id);
            $ph->jumlah_transaksi = $t->jumlah_transaksi ?? 0;
            $ph->total_nominal = $t->total_nominal ?? 0;
            $ph->jumlah_nasabah = $nasabahData->get($ph->id)->jumlah ?? 0;
        }

        return $allPenghimpun;
    }

    private function getSupervisorReport($date, $type)
    {
        // Get ALL supervisors (regardless of transaksi)
        $allSupervisors = Pegawai::select([
                'pegawai.id',
                'pegawai.nama as nama_supervisor',
            ])
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

        // Get bawahan IDs for each supervisor (including self)
        $supBawahanMap = [];
        foreach ($allSupervisors as $sup) {
            $bawahanIds = Korel::where('kepala_id', $sup->id)->pluck('bawahan_id')->toArray();
            $bawahanIds[] = $sup->id;
            $supBawahanMap[$sup->id] = $bawahanIds;
        }

        // Get transaksi data
        $transaksiQuery = Transaksi::select([
                'transaksi.pegawai_id',
                DB::raw('COUNT(DISTINCT transaksi.id) as jumlah_transaksi'),
                DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as total_nominal'),
            ])
            ->leftJoin('transaksi_detail as td', 'td.transaksi_id', '=', 'transaksi.id');

        if ($type == 'daily') {
            $transaksiQuery->where('transaksi.tanggal', $date);
        } elseif ($type == 'monthly') {
            $transaksiQuery->whereMonth('transaksi.tanggal', date('n', strtotime($date . '-01')))
                           ->whereYear('transaksi.tanggal', date('Y', strtotime($date . '-01')));
        }

        $transaksiData = $transaksiQuery->groupBy('transaksi.pegawai_id')->get()->keyBy('pegawai_id');

        // Get nasabah count per pegawai
        $nasabahData = Donatur::select('pegawai_id', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('pegawai_id')
            ->get()
            ->keyBy('pegawai_id');

        // Aggregate per supervisor
        foreach ($allSupervisors as $sup) {
            $bawahanIds = $supBawahanMap[$sup->id];
            $sup->jumlah_transaksi = 0;
            $sup->total_nominal = 0;
            $sup->jumlah_nasabah = 0;

            foreach ($bawahanIds as $bid) {
                $t = $transaksiData->get($bid);
                if ($t) {
                    $sup->jumlah_transaksi += $t->jumlah_transaksi;
                    $sup->total_nominal += $t->total_nominal;
                }
                $n = $nasabahData->get($bid);
                if ($n) {
                    $sup->jumlah_nasabah += $n->jumlah;
                }
            }
        }

        return $allSupervisors;
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

    private function getPenghimpunYearlyReport($year, $accessibleIds)
    {
        $penghimpun = Pegawai::select([
                'pegawai.id',
                'pegawai.nama as nama_penghimpun',
                DB::raw('COUNT(DISTINCT d.id) as jumlah_nasabah_terdaftar'),
            ])
            ->leftJoin('donatur as d', 'd.pegawai_id', '=', 'pegawai.id')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users as u')
                    ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                    ->whereColumn('u.pegawai_id', 'pegawai.id')
                    ->whereRaw("lower(r.name) = 'penghimpun'");
            })
            ->groupBy('pegawai.id', 'pegawai.nama')
            ->orderBy('pegawai.nama');

        if ($accessibleIds !== null) {
            $penghimpun->whereIn('pegawai.id', $accessibleIds);
        }

        $penghimpun = $penghimpun->get();

        foreach ($penghimpun as $ph) {
            for ($m = 1; $m <= 12; $m++) {
                $data = Donatur::leftJoin('transaksi as t', function ($join) use ($m, $year) {
                        $join->on('donatur.id', '=', 't.donatur_id')
                            ->whereMonth('t.tanggal', $m)
                            ->whereYear('t.tanggal', $year)
                            ->whereIn('t.jenis_transaksi', ['cash', 'transfer']);
                    })
                    ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
                    ->where('donatur.pegawai_id', $ph->id)
                    ->select([
                        DB::raw('COUNT(DISTINCT donatur.id) as jumlah_nasabah'),
                        DB::raw('COALESCE(SUM(td.nominal_donasi), 0) as nominal'),
                    ])
                    ->first();

                $ph->{'m' . $m . '_nasabah'} = $data->jumlah_nasabah ?? 0;
                $ph->{'m' . $m . '_nominal'} = $data->nominal ?? 0;
            }
        }

        return $penghimpun;
    }
}