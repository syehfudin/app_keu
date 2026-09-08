<?php

namespace App\Http\Controllers;

use App\Models\Korel;
use App\Models\User;
use DataTables;
use DB;
use Illuminate\Http\Request;

class KorelController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:korel-list|korel-create|korel-edit|korel-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:korel-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:korel-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:korel-delete', ['only' => ['destroy']]);
        $this->title = 'Data Koordinator Penghimpun';
        $this->redirectUrl = route('korel.index');
    }

    public function index()
    {
        $title = $this->title;

        return view('korel.index', compact('title'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData()
    {
        $data = Korel::leftJoin('pegawai as pa', 'korel.kepala_id', '=', 'pa.id')
            ->leftJoin('pegawai as pb', 'korel.bawahan_id', '=', 'pb.id')
            ->select([
                'korel.kepala_id as id',
                'pa.nama as nama_atasan',
                DB::raw("array_to_string(ARRAY_AGG(pb.nama), ',', '') as list_bawahan"),
            ])
            ->groupBy([
                'korel.kepala_id',
                'pa.nama',
            ])
            ->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($korel) {
                return view('korel.action', compact('korel'));
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
        $action = route('korel.store');
        $redirectUrl = $this->redirectUrl;
        $kepala = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where(DB::raw('lower(r.name)'), 'supervisor')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        $bawahan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
            ->where(DB::raw('lower(r.name)'), 'penghimpun')
            ->whereNull('k.bawahan_id')
            ->select([
                'p.id',
                'p.nama',
                DB::raw("'' as cek"),
            ])
            ->get();

        return view('korel.create', compact('title', 'action', 'redirectUrl', 'kepala', 'bawahan'));
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
            'kepala' => 'required',
            'bawahan' => 'required',
        ], [
            'kepala.required' => 'Atasan wajib diisi',
            'bawahan.required' => 'list bawahan wajib dipilih',
        ]
        );

        DB::transaction(function () use ($request) {
            $kepala = $request->input('kepala');
            $bawahan = $request->input('bawahan');
            foreach ($bawahan as $bawahan_id) {
                $data = [];
                $data['kepala_id'] = $kepala;
                $data['bawahan_id'] = $bawahan_id;
                Korel::create($data);
            }
        });

        return redirect()->route('korel.index')
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
        $korel = korel::find($id);
        $title = 'Show '.$this->title;
        $action = '#';
        $show = 'disabled';
        $redirectUrl = $this->redirectUrl;

        $kepala = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where(DB::raw('lower(r.name)'), 'supervisor')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        $first = Korel::leftJoin('pegawai as p', 'korel.bawahan_id', '=', 'p.id')
                    ->where('korel.kepala_id', $id)
                    ->select([
                        'p.id',
                        'p.nama',
                        DB::raw("'selected' as cek"),
                    ]);

        $bawahan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
            ->where(DB::raw('lower(r.name)'), 'penghimpun')
            ->whereNull('k.bawahan_id')
            ->union($first)
            ->select([
                'p.id',
                'p.nama',
                DB::raw("'' as cek"),
            ])
            ->get();

        return view('korel.create', compact('title', 'action', 'redirectUrl', 'kepala', 'id', 'bawahan', 'show'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $redirectUrl = $this->redirectUrl;
        $title = 'Ubah '.$this->title;
        $action = route('korel.update', $id);

        $kepala = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where(DB::raw('lower(r.name)'), 'supervisor')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        $first = Korel::leftJoin('pegawai as p', 'korel.bawahan_id', '=', 'p.id')
                    ->where('korel.kepala_id', $id)
                    ->select([
                        'p.id',
                        'p.nama',
                        DB::raw("'selected' as cek"),
                    ]);

        $bawahan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
            ->where(DB::raw('lower(r.name)'), 'penghimpun')
            ->whereNull('k.bawahan_id')
            ->union($first)
            ->select([
                'p.id',
                'p.nama',
                DB::raw("'' as cek"),
            ])
            ->get();

        return view('korel.create', compact('title', 'action', 'redirectUrl', 'id', 'kepala', 'bawahan'));
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
            // 'kepala'   => 'required',
            'bawahan' => 'required',
        ], [
            // 'kepala.required'   => 'Atasan wajib diisi',
            'bawahan.required' => 'list bawahan wajib dipilih',
        ]
        );

        DB::transaction(function () use ($request, $id) {
            $data = Korel::where('kepala_id', $id)->get();
            $data_lama = [];
            foreach ($data as $item) {
                $data_lama[] = $item->bawahan_id;
            }

            $list_bawahan = $request->input('bawahan');
            $delete = array_diff($data_lama, $list_bawahan);
            $update = array_diff($list_bawahan, $data_lama);
            foreach ($delete as $bawahan_id) {
                Korel::where('kepala_id', $id)
                    ->where('bawahan_id', $bawahan_id)
                    ->delete();
            }
            foreach ($update as $bawahan_id) {
                $bawahan = [];
                $bawahan['kepala_id'] = $id;
                $bawahan['bawahan_id'] = $bawahan_id;
                Korel::create($bawahan);
            }
        });

        return redirect()->route('korel.index')
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
        Korel::where('kepala_id', $id)->delete();

        return redirect()->route('korel.index')
                        ->with('success', ucfirst('Hapus '.$this->title.' berhasil'));
    }
}
