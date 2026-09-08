<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Transaksi;
use Auth;
use DataTables;
use DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $title = 'Dashboard';

        return view('dashboard.index', compact('title'));
    }

    public function indexData(Request $request)
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $type = $request['type'];
        $query = Program::leftJoin('transaksi_detail as td', 'td.program_id', '=', 'program.id')
            ->leftJoin('transaksi as t', 'td.transaksi_id', '=', 't.id')
            ->select([
                'program.nama as nama_program',
                DB::raw('count(t.id) as count_nominal'),
                DB::raw('sum(coalesce(td.nominal_donasi, 0)) as sum_nonimal'),
                DB::raw('sum(case when extract(month from t.tanggal) = 1 then td.nominal_donasi else 0 end) as jan'),
                DB::raw('sum(case when extract(month from t.tanggal) = 2 then td.nominal_donasi else 0 end) as feb'),
                DB::raw('sum(case when extract(month from t.tanggal) = 3 then td.nominal_donasi else 0 end) as mar'),
                DB::raw('sum(case when extract(month from t.tanggal) = 4 then td.nominal_donasi else 0 end) as apr'),
                DB::raw('sum(case when extract(month from t.tanggal) = 5 then td.nominal_donasi else 0 end) as mei'),
                DB::raw('sum(case when extract(month from t.tanggal) = 6 then td.nominal_donasi else 0 end) as jun'),
                DB::raw('sum(case when extract(month from t.tanggal) = 7 then td.nominal_donasi else 0 end) as jul'),
                DB::raw('sum(case when extract(month from t.tanggal) = 8 then td.nominal_donasi else 0 end) as agu'),
                DB::raw('sum(case when extract(month from t.tanggal) = 9 then td.nominal_donasi else 0 end) as sep'),
                DB::raw('sum(case when extract(month from t.tanggal) = 10 then td.nominal_donasi else 0 end) as okt'),
                DB::raw('sum(case when extract(month from t.tanggal) = 11 then td.nominal_donasi else 0 end) as nov'),
                DB::raw('sum(case when extract(month from t.tanggal) = 12 then td.nominal_donasi else 0 end) as des'),
                DB::raw('count(case when extract(month from t.tanggal) = 1 then td.nominal_donasi end) as count_jan'),
                DB::raw('count(case when extract(month from t.tanggal) = 2 then td.nominal_donasi end) as count_feb'),
                DB::raw('count(case when extract(month from t.tanggal) = 3 then td.nominal_donasi end) as count_mar'),
                DB::raw('count(case when extract(month from t.tanggal) = 4 then td.nominal_donasi end) as count_apr'),
                DB::raw('count(case when extract(month from t.tanggal) = 5 then td.nominal_donasi end) as count_mei'),
                DB::raw('count(case when extract(month from t.tanggal) = 6 then td.nominal_donasi end) as count_jun'),
                DB::raw('count(case when extract(month from t.tanggal) = 7 then td.nominal_donasi end) as count_jul'),
                DB::raw('count(case when extract(month from t.tanggal) = 8 then td.nominal_donasi end) as count_agu'),
                DB::raw('count(case when extract(month from t.tanggal) = 9 then td.nominal_donasi end) as count_sep'),
                DB::raw('count(case when extract(month from t.tanggal) = 10 then td.nominal_donasi end) as count_okt'),
                DB::raw('count(case when extract(month from t.tanggal) = 11 then td.nominal_donasi end) as count_nov'),
                DB::raw('count(case when extract(month from t.tanggal) = 12 then td.nominal_donasi end) as count_des'),
            ])
            ->groupBy([
                'program.nama',
            ]);

        if (in_array($role, ['admin', 'manager'])) {
            // $data = $query->get();
        } elseif ($role == 'relawan') {
            $query = $query->where('t.pegawai_id', Auth::user()->pegawai_id);
        } else {
            $query = $query->leftJoin('korel as k', function ($join) {
                $join->on('t.pegawai_id', '=', 'k.bawahan_id');
                $join->orOn('t.pegawai_id', '=', 'k.kepala_id', 'or');
            })
                ->where('k.kepala_id', Auth::user()->pegawai_id);
        }

        if ($type == 'daily') {
            $data = $query->where('t.tanggal', date('Y-m-d', strtotime($request['tanggal'])))
                ->get();
        } elseif ($type == 'monthly') {
            $data = $query->where(DB::raw("to_char(t.tanggal, 'mm')"), date('m', strtotime($request['tanggal'])))
                ->where(DB::raw("to_char(t.tanggal, 'yyyy')"), date('Y', strtotime($request['tanggal'])))
                ->get();
        } else {
            $data = $query->where(DB::raw("to_char(t.tanggal, 'yyyy')"), date('Y', strtotime($request['tanggal'])))
                ->get();
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function indexDataChart(Request $request)
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;
        $tanggal = date('Y', strtotime($request['tanggal']));

        $query = Transaksi::leftJoin('transaksi_detail as td', 'transaksi.id', '=', 'td.transaksi_id')
                ->select([
                    DB::raw("to_char(transaksi.tanggal, 'mm') as mon"),
                    DB::raw('sum(td.nominal_donasi) as total_donasi'),
                ])
                ->where(DB::raw("to_char(transaksi.tanggal, 'yyyy')"), $tanggal)
                ->groupBy([
                    DB::raw("to_char(transaksi.tanggal, 'mm')"),
                ]);

        if (in_array($role, ['admin', 'manager'])) {
            $data = $query->get();
        } elseif ($role == 'relawan') {
            $data = $query->where('transaksi.pegawai_id', $pegawai_id)->get();
        } else {
            $data = $query->leftJoin('korel as k', function ($join) {
                $join->on('transaksi.pegawai_id', '=', 'k.bawahan_id');
                $join->orOn('transaksi.pegawai_id', '=', 'k.kepala_id', 'or');
            })
                ->where('k.kepala_id', $pegawai_id)
                ->get();
        }

        return json_encode($data);
    }
}
