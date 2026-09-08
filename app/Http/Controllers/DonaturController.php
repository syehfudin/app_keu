<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\Pekerjaan;
use App\Models\User;
use Auth;
use DataTables;
use Illuminate\Http\Request;

class DonaturController extends Controller
{
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
        $this->title = 'Data Donatur';
        $this->redirectUrl = route('donatur.index');
    }

    public function index()
    {
        $title = $this->title;
        
        return view('donatur.index', compact('title'));
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
        $query = Donatur::leftJoin('pegawai as p', 'donatur.pegawai_id', '=', 'p.id')
            ->select([
                'donatur.id',
                'donatur.nama as nama_donatur',
                'donatur.no_telepon',
                'donatur.pekerjaan',
                'p.nama as nama_relawan',
            ]);

        if (in_array($role, ['admin', 'manager'])) {
            $data = $query->get();
        } elseif ($role == 'relawan') {
            $data = $query->where('donatur.pegawai_id', $pegawai_id)->get();
        } else {
            $data = $query->leftJoin('korel as k', function ($join) {
                $join->on('donatur.pegawai_id', '=', 'k.bawahan_id');
                $join->orOn('donatur.pegawai_id', '=', 'k.kepala_id', 'or');
            })
                ->where('k.kepala_id', $pegawai_id)->get();
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($donatur) {
                return view('donatur.action', compact('donatur'));
            })
            ->editColumn('pekerjaan', function ($donatur) {
                if (explode('-', $donatur->pekerjaan)[0] == 'lainnya') {
                    return explode('-', $donatur->pekerjaan)[1];
                } else {
                    return $donatur->pekerjaan;
                }
            })
            ->rawColumns(['action'])
            ->make(true);
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
            ->where('r.name', 'Relawan')
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
            'pegawai_id.required' => 'Nama Relawan wajib dipilih',
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
            ->where('r.name', 'Relawan')
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
            ->where('r.name', 'Relawan')
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
            'pegawai_id.required' => 'Nama Relawan wajib dipilih',
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
