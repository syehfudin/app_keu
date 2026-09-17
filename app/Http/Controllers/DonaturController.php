<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\Pekerjaan;
use App\Models\User;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use App\Traits\HasHierarchy;

class DonaturController extends Controller
{
    use HasHierarchy;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:donatur-list|donatur-create|donatur-edit|donatur-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:donatur-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:donatur-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:donatur-delete', ['only' => ['destroy']]);
        $this->title = 'Data Nasabah';
        $this->redirectUrl = route('donatur.index');
    }

    public function index()
    {
        $title = $this->title;

        // ===== Dropdown filter hierarki =====
        // Manager & Supervisor: users dengan role tsb. Penghimpun: users role Penghimpun.
        $managers = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Manager')
            ->orderBy('p.nama')->get(['p.id', 'p.nama']);

        $supervisors = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Supervisor')
            ->orderBy('p.nama')->get(['p.id', 'p.nama']);

        $penghimpuns = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Penghimpun')
            ->orderBy('p.nama')->get(['p.id', 'p.nama']);

        return view('donatur.index', compact(
            'title', 'managers', 'supervisors', 'penghimpuns'
        ));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData()
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;

        // ===== Kolom hierarki: Penghimpun (pemegang nasabah),
        // Supervisor & Manager = atasan di korel =====
        $query = Donatur::leftJoin('pegawai as p', 'donatur.pegawai_id', '=', 'p.id')
            ->leftJoin('korel as k1', 'k1.bawahan_id', '=', 'p.id') // p adalah bawahan dari Supervisor
            ->leftJoin('pegawai as sp', 'sp.id', '=', 'k1.kepala_id')
            ->leftJoin('korel as k2', 'k2.bawahan_id', '=', 'sp.id') // Supervisor bawahan dari Manager
            ->leftJoin('pegawai as mg', 'mg.id', '=', 'k2.kepala_id')
            ->select([
                'donatur.id',
                'donatur.nama as nama_donatur',
                'donatur.no_telepon',
                'donatur.pekerjaan',
                'p.id as penghimpun_id',
                'p.nama as nama_relawan',
                'sp.nama as nama_supervisor',
                'mg.nama as nama_manager',
                'mg.id as manager_id',
                'sp.id as supervisor_id',
            ]);

        // ===== Filter Manager / Supervisor / Penghimpun =====
        // (berlaku untuk semua role yang dapat melihat menu ini; scope data tetap)
        $filterManager = request()->input('filter_manager');
        $filterSupervisor = request()->input('filter_supervisor');
        $filterPenghimpun = request()->input('filter_penghimpun');

        $accessibleIds = $this->getAccessiblePegawaiIds();
        if ($accessibleIds !== null) {
            $query->whereIn('donatur.pegawai_id', $accessibleIds);
        }

        if ($filterManager) {
            // Nasabah milik seluruh subtree Manager: ambil bawahan Manager
            // (Supervisor + Penghimpun level-2 di bawahnya)
            $subtree = $this->getSubtreeIds((int) $filterManager);
            $query->whereIn('donatur.pegawai_id', $subtree);
        } elseif ($filterSupervisor) {
            $subtree = $this->getSubtreeIds((int) $filterSupervisor);
            $query->whereIn('donatur.pegawai_id', $subtree);
        }
        if ($filterPenghimpun) {
            $query->where('donatur.pegawai_id', (int) $filterPenghimpun);
        }

        // ===== Client-side mode: return plain JSON =====
        // 471 rows ringan di browser; search/filter instant client-side.
        // Yajra collection engine terbukti lambat (~1s) karena memproses
        // seluruh collection per request; DB sendiri sub-milidetik (12 buffers).
        $data = $query->get();

        $rows = [];
        foreach ($data as $donatur) {
            $rows[] = [
                'nama_manager' => $donatur->nama_manager,
                'nama_supervisor' => $donatur->nama_supervisor,
                'nama_relawan' => $donatur->nama_relawan,
                'nama_donatur' => $donatur->nama_donatur,
                'action' => view('donatur.action', ['donatur' => $donatur])->render(),
            ];
        }

        return response()->json(['data' => $rows, 'recordsTotal' => count($rows)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Tambah '.$this->title;
        $action = route('donatur.store');
        $redirectUrl = $this->redirectUrl;

        $pekerjaan = Pekerjaan::get();

        $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Penghimpun')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        return view('donatur.create', compact('title', 'action', 'redirectUrl', 'relawan', 'pekerjaan'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'nama' => 'required',
            'pegawai_id' => 'required',
        ], [
            'nama.required' => 'Nama donatur wajib diisi',
            'pegawai_id.required' => 'Nama Penghimpun wajib dipilih',
        ]
        );

        $input = $request->all();
        if ($input['pekerjaan'] == 'lainnya') {
            $input['pekerjaan'] = $input['pekerjaan'].'-'.$input['lainnya'];
        }

        Donatur::create($input);

        return redirect()->route('donatur.index')
                        ->with('success', ucfirst('Tambah '.$this->title.' Berhasil'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $donatur = donatur::find($id);
        $title = 'Show '.$this->title;
        $action = '#';
        $show = 'disabled';
        $redirectUrl = $this->redirectUrl;

        $pekerjaan = Pekerjaan::get();
        $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Penghimpun')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        return view('donatur.create', compact('title', 'action', 'redirectUrl', 'donatur', 'show', 'relawan', 'pekerjaan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $donatur = donatur::find($id);
        $redirectUrl = $this->redirectUrl;
        $title = 'Ubah '.$this->title;
        $action = route('donatur.update', $id);

        $pekerjaan = Pekerjaan::get();
        $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Penghimpun')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        return view('donatur.create', compact('title', 'action', 'redirectUrl', 'donatur', 'relawan', 'pekerjaan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'nama' => 'required',
            'pegawai_id' => 'required',
        ], [
            'nama.required' => 'Nama donatur wajib diisi',
            'pegawai_id.required' => 'Nama Penghimpun wajib dipilih',
        ]
        );

        $input = $request->all();
        if ($input['pekerjaan'] == 'lainnya') {
            $input['pekerjaan'] = $input['pekerjaan'].'-'.$input['lainnya'];
        }

        donatur::find($id)
            ->update($input);

        return redirect()->route('donatur.index')
                        ->with('success', ucfirst('Ubah '.$this->title.' Berhasil'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        donatur::find($id)->delete();

        return redirect()->route('donatur.index')
                        ->with('success', ucfirst('Hapus '.$this->title.' berhasil'));
    }
}
