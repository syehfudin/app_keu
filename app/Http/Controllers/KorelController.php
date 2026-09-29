<?php

namespace App\Http\Controllers;

use App\Models\Korel;
use App\Models\User;
use DataTables;
use DB;
use Illuminate\Http\Request;

class KorelController extends Controller
{
    use \App\Traits\HasHierarchy;
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

        // ===== STATISTIK untuk kartu info =====
        $totalKoordinator = Korel::distinct('kepala_id')->count('kepala_id');
        $totalBawahan    = Korel::count();

        // Semua pegawai yang ber-peran koordinator-potensial (Supervisor/Manager/GM/Direktur/DirOps)
        // dan penghimpun aktif untuk dasar penghitung "belum terpetakan".
        $roleKoordinator = ['supervisor', 'manager', 'general manager', 'gm', 'direktur', 'dirops',
            'dir. ops', 'direktur ops', 'direktur operasional'];
        $roleBawahan     = ['penghimpun', 'manager'];

        $totalPegawaiKoordinator = User::join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->whereIn(DB::raw('lower(r.name)'), $roleKoordinator)
            ->distinct('users.id')->count('users.id');

        $totalPenghimpun = User::join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->whereIn(DB::raw('lower(r.name)'), $roleBawahan)
            ->distinct('users.id')->count('users.id');

        $penghimpunTanpaKoordinator = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
            ->whereIn(DB::raw('lower(r.name)'), $roleBawahan)
            ->whereNull('k.bawahan_id')
            ->distinct('users.id')->count('users.id');

        return view('korel.index', compact('title',
            'totalKoordinator', 'totalBawahan',
            'totalPegawaiKoordinator', 'totalPenghimpun',
            'penghimpunTanpaKoordinator'));
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
            // role koordinator: pegawai pa -> users(pegawai_id) -> model_has_roles -> roles
            ->leftJoin('users as uk', 'uk.pegawai_id', '=', 'pa.id')
            ->leftJoin('model_has_roles as mk', function ($j) {
                $j->on('mk.model_id', '=', 'uk.id')
                  ->where('mk.model_type', '=', 'App\Models\User');
            })
            ->leftJoin('roles as rk', 'rk.id', '=', 'mk.role_id')
            ->select([
                'korel.kepala_id as id',
                'pa.nama as nama_atasan',
                DB::raw('min(rk.name) as role_atasan'),
                // urutan role: Direktur=1, DirOps=2, General Manager=3, Manager=4, Supervisor=5
                DB::raw("CASE lower(min(rk.name))
                    WHEN 'direktur' THEN 1
                    WHEN 'dirops' THEN 2
                    WHEN 'general manager' THEN 3
                    WHEN 'gm' THEN 3
                    WHEN 'manager' THEN 4
                    WHEN 'supervisor' THEN 5
                    ELSE 99 END as role_order"),
                DB::raw('count(korel.bawahan_id) as jml_bawahan'),
                DB::raw("array_to_string(ARRAY_AGG(pb.nama ORDER BY pb.nama), ',', '') as list_bawahan"),
            ])
            ->groupBy([
                'korel.kepala_id',
                'pa.nama',
            ])
            ->orderBy(DB::raw('role_order'))
            ->orderBy('pa.nama')
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
     * Ambil daftar bawahan berdasarkan role kepala yang dipilih.
     * Menghasilkan kandidat "belum punya atasan" sesuai jenjang:
     *   Direktur      -> DirOps
     *   DirOps        -> General Manager
     *   General Mgr   -> Manager
     *   Manager       -> Supervisor
     *   Supervisor    -> Penghimpun
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBawahan(Request $request)
    {
        $kepalaId = (int) $request->input('kepala');

        // 1) cari role kepala terpilih
        $roleKepala = '';
        if ($kepalaId) {
            $roleKepala = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
                ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->where('p.id', $kepalaId)
                ->value('r.name');
        }
        $roleKepala = strtolower(trim((string) $roleKepala));

        // 2) mapping role bawahan per jenjang
        $map = [
            'direktur'             => ['dirops'],
            'dirops'               => ['general manager', 'gm'],
            'general manager'      => ['manager'],
            'gm'                   => ['manager'],
            'manager'              => ['supervisor'],
            'supervisor'           => ['penghimpun'],
        ];

        $rolesBawahan = $map[$roleKepala] ?? [];

        // 3) daftar bawahan (belum punya atasan) untuk role tsb
        $bawahan = collect();
        if ($rolesBawahan) {
            $bawahan = User::with('pegawai')
                ->join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
                ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
                ->whereIn(DB::raw('lower(r.name)'), $rolesBawahan)
                ->whereNull('k.bawahan_id')
                ->select([
                    'p.id',
                    'p.nama',
                    'r.name as role',
                ])
                ->orderBy('p.nama')
                ->distinct()
                ->get()
                ->keyBy('id');
        }

        // 4) untuk mode edit/show: selalu sertakan bawahan yang sudah terpilih
        //    (meski sudah punya atasan = kepala saat ini), tandai cek=selected.
        $selectedIds = $request->input('selected', '');
        $selectedIds = $selectedIds === '' ? [] : array_map('intval', explode(',', $selectedIds));
        foreach ($selectedIds as $sid) {
            if (!$bawahan->has($sid)) {
                $p = \App\Models\Pegawai::find($sid);
                if ($p) {
                    $bawahan->put($sid, (object) [
                        'id' => $p->id,
                        'nama' => $p->nama,
                        'role' => '',
                    ]);
                }
            }
        }

        $result = $bawahan->values()->map(function ($item) use ($selectedIds) {
            return [
                'id'   => $item->id,
                'nama' => $item->nama,
                'role' => isset($item->role) ? $item->role : '',
                'cek'  => in_array((int) $item->id, $selectedIds, true) ? 'selected' : '',
            ];
        });

        return response()->json([
            'kepala_role' => $roleKepala,
            'bawahan'     => $result,
        ]);
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
        $kepala = User::with('pegawai')
            ->join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->whereIn(DB::raw('lower(r.name)'), ['supervisor', 'manager', 'general manager', 'gm',
                'direktur', 'dirops', 'dir. ops', 'direktur ops', 'direktur operasional'])
            ->select([
                'p.id',
                'p.nama',
                'r.name as role',
            ])
            ->orderBy('p.nama')
            ->distinct()
            ->get();

        $bawahan = User::with('pegawai')
            ->join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
            ->whereIn(DB::raw('lower(r.name)'), ['penghimpun', 'manager', 'supervisor'])
            ->whereNull('k.bawahan_id')
            ->select([
                'p.id',
                'p.nama',
                'r.name as role',
                DB::raw("'' as cek"),
            ])
            ->orderBy('p.nama')
            ->distinct()
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

            // ===== CYCLE GUARD =====
            // Tolak (skip) bawahan yang: self-loop, relasi sudah ada,
            // atau membuat siklus (kepala ada di subtree bawahan).
            $skipped = 0;
            foreach ($bawahan as $bawahan_id) {
                if ($this->wouldCreateCycle($kepala, $bawahan_id)) {
                    $skipped++;
                    continue;
                }
                if (Korel::where('kepala_id', $kepala)->where('bawahan_id', $bawahan_id)->exists()) {
                    $skipped++;
                    continue;
                }
                Korel::create([
                    'kepala_id' => $kepala,
                    'bawahan_id' => $bawahan_id,
                ]);
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

        $kepala = User::with('pegawai')
            ->join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->whereIn(DB::raw('lower(r.name)'), ['supervisor', 'manager', 'general manager', 'gm',
                'direktur', 'dirops', 'dir. ops', 'direktur ops', 'direktur operasional'])
            ->select([
                'p.id',
                'p.nama',
                'r.name as role',
            ])
            ->orderBy('p.nama')
            ->distinct()
            ->get();

        $first = Korel::leftJoin('pegawai as p', 'korel.bawahan_id', '=', 'p.id')
                    ->where('korel.kepala_id', $id)
                    ->select([
                        'p.id',
                        'p.nama',
                        DB::raw("'' as role"),
                        DB::raw("'selected' as cek"),
                    ]);

        $bawahan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
            ->whereIn(DB::raw('lower(r.name)'), ['penghimpun', 'manager', 'supervisor'])
            ->whereNull('k.bawahan_id')
            ->select([
                'p.id',
                'p.nama',
                'r.name as role',
                DB::raw("'' as cek"),
            ])
            ->union($first)
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

        $kepala = User::with('pegawai')
            ->join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->whereIn(DB::raw('lower(r.name)'), ['supervisor', 'manager', 'general manager', 'gm',
                'direktur', 'dirops', 'dir. ops', 'direktur ops', 'direktur operasional'])
            ->select([
                'p.id',
                'p.nama',
                'r.name as role',
            ])
            ->orderBy('p.nama')
            ->distinct()
            ->get();

        $first = Korel::leftJoin('pegawai as p', 'korel.bawahan_id', '=', 'p.id')
                    ->where('korel.kepala_id', $id)
                    ->select([
                        'p.id',
                        'p.nama',
                        DB::raw("'' as role"),
                        DB::raw("'selected' as cek"),
                    ]);

        $bawahan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->leftJoin('korel as k', 'k.bawahan_id', 'p.id')
            ->whereIn(DB::raw('lower(r.name)'), ['penghimpun', 'manager', 'supervisor'])
            ->whereNull('k.bawahan_id')
            ->select([
                'p.id',
                'p.nama',
                'r.name as role',
                DB::raw("'' as cek"),
            ])
            ->union($first)
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
