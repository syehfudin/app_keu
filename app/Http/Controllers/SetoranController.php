<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\File;
use App\Models\Pekerjaan;
use App\Models\Setoran;
use App\Models\SetoranDetail;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\GoogleSheetService;
use Auth;
use DataTables;
use DB;
use Illuminate\Http\Request;
use App\Traits\HasHierarchy;

class SetoranController extends Controller
{
    use HasHierarchy;
    public function __construct()
    {
        $this->middleware('permission:setoran-list|setoran-create|setoran-edit|setoran-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:setoran-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:setoran-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:setoran-delete', ['only' => ['destroy']]);
        $this->title = 'Data Setoran';
        $this->redirectUrl = route('setoran.index');
    }

    public function index()
    {
        // $googleService = new GoogleSheetService;
        // $googleService->firstStore();
        $title = $this->title;

        return view('setoran.index', compact('title'));
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
        $query = setoran::leftJoin('setoran_detail as sd', 'setoran.id', '=', 'sd.setoran_id')
            ->leftJoin('transaksi as t', 'sd.transaksi_id', '=', 't.id')
            ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
            ->leftJoin('pegawai as p', 'setoran.pegawai_id', '=', 'p.id')
            ->leftJoin('file as f', 'setoran.file_id', 'f.id')
            ->select([
                'p.nama as nama_pegawai',
                'setoran.id',
                DB::raw('cast(setoran.created_at as date) as tanggal_setoran'),
                'f.path',
                'f.nama as nama_file',
                DB::raw('concat(f.path, f.nama) as image_url'),
                DB::raw('sum(td.nominal_donasi) as total_donasi'),
            ])
            ->groupBy([
                'p.nama',
                'setoran.id',
                DB::raw('concat(f.path, f.nama)'),
                DB::raw('cast(setoran.created_at as date)'),
                'f.path',
                'f.nama',
            ])
            ->orderBy('setoran.created_at', 'desc');

        $accessibleIds = $this->getAccessiblePegawaiIds();
        if ($accessibleIds === null) {
            $data = $query->get();
        } else {
            $data = $query->whereIn('setoran.pegawai_id', $accessibleIds)->get();
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($setoran) {
                return view('setoran.action', compact('setoran'));
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
        $title = 'Tambah ' . $this->title;
        $action = route('setoran.store');
        $redirectUrl = $this->redirectUrl;
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;

        $queryTransaksi = Transaksi::leftJoin('transaksi_detail as td', 'transaksi.id', '=', 'td.transaksi_id')
            ->leftJoin('setoran_detail as sd', 'sd.transaksi_id', '=', 'transaksi.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->whereNull('sd.id')
            ->where('transaksi.jenis_transaksi', 'cash')
            ->select([
                'transaksi.id',
                DB::raw("TO_CHAR(cast(transaksi.tanggal as date), 'dd-mm-yyyy') as tanggal"),
                'd.nama as nama_donatur',
                DB::raw('sum(td.nominal_donasi) as total_donasi'),
            ])
            ->groupBy([
                'transaksi.id',
                'd.nama',
            ]);
        if ($role == 'admin') {
            $transaksi = $queryTransaksi->get();
        } elseif ($role == 'supervisor') {
            $transaksi = $queryTransaksi->leftJoin('korel as k', function ($join) {
                $join->on('d.pegawai_id', '=', 'k.bawahan_id');
                $join->orOn('d.pegawai_id', '=', 'k.kepala_id', 'or');
            })
                ->where('k.kepala_id', $pegawai_id)->get();
        } else {
            $transaksi = $queryTransaksi->where('d.pegawai_id', $pegawai_id)->get();
        }

        $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Penghimpun')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        return view('setoran.create', compact('title', 'action', 'redirectUrl', 'relawan', 'transaksi'));
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
            'total_setor' => 'required',
            'file' => 'required',
        ], [
            'total_setor.required' => 'Transaksi untuk setor tidak boleh kosong',
            'file.required' => 'Bukti transfer belum dipilih',
        ]);

        DB::transaction(function () use ($request) {
            $googleService = new GoogleSheetService;
            if (strtolower(Auth::user()->roles[0]->name) == 'admin') {
                $pegawai_id = $request->input('pegawai_id');
            } else {
                $pegawai_id = Auth::User()->pegawai_id;
            }

            $image = $request->file('file');
            $destinationPath = 'storage/image/setoran/';
            $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $filename);
            $fileData['jenis'] = 'Setoran';
            $fileData['path'] = $destinationPath;
            $fileData['nama'] = $filename;
            $file = File::create($fileData);

            $setoranData['pegawai_id'] = $pegawai_id;
            $setoranData['file_id'] = $file->id;
            $setoranData['total_setoran'] = $request->input('total_setor');

            $setoran = setoran::create($setoranData);

            $transaksi = $request->input('transaksi_id');
            foreach ($transaksi as $id) {
                $sd = [];
                $sd['setoran_id'] = $setoran->id;
                $sd['transaksi_id'] = $id;
                SetoranDetail::create($sd);
            }
            $googleService->storeSetoran($setoran->id);
        });

        return redirect()->route('setoran.index')
            ->with('success', ucfirst('Tambah ' . $this->title . ' Berhasil'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $setoran = setoran::leftJoin('file as f', 'setoran.file_id', '=', 'f.id')
            ->leftJoin('pegawai as p', 'setoran.pegawai_id', '=', 'p.id')
            ->where('setoran.id', $id)
            ->select([
                'setoran.*',
                'p.nama as nama_penyetor',
                'f.path',
                'f.nama as nama_file',
            ])
            ->first();

        $setoran_detail = setoranDetail::leftJoin('transaksi as t', 'setoran_detail.transaksi_id', 't.id')
            ->leftJoin('transaksi_detail as td', 't.id', '=', 'td.transaksi_id')
            ->leftJoin('donatur as d', 't.donatur_id', '=', 'd.id')
            ->select([
                't.tanggal',
                DB::raw("to_char(t.tanggal, 'dd-mm-yyyy') as date"),
                'd.nama as nama_donatur',
                DB::raw('sum(td.nominal_donasi) as total_donasi'),
            ])
            ->groupBy([
                't.tanggal',
                DB::raw("to_char(t.tanggal, 'dd-mm-yyyy')"),
                'd.nama',
            ])
            ->where('setoran_detail.setoran_id', $id)
            ->orderBy('t.tanggal', 'asc')
            ->get();
        $title = 'Show ' . $this->title;
        $action = '#';
        $show = 'disabled';
        $redirectUrl = $this->redirectUrl;

        $donatur = Donatur::get();
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

        return view('setoran.show', compact('title', 'action', 'redirectUrl', 'setoran', 'setoran_detail', 'show', 'relawan', 'donatur'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $setoran = setoran::find($id);
        $redirectUrl = $this->redirectUrl;
        $title = 'Tambah ' . $this->title;
        $action = route('setoran.update', $id);

        $queryTransaksi = Transaksi::leftJoin('transaksi_detail as td', 'transaksi.id', '=', 'td.transaksi_id')
            ->leftJoin('setoran_detail as sd', 'sd.transaksi_id', '=', 'transaksi.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->whereNull('sd.id')
            ->where('transaksi.jenis_transaksi', 'cash')
            ->select([
                'transaksi.id',
                DB::raw("TO_CHAR(cast(transaksi.tanggal as date), 'dd-mm-yyyy') as tanggal"),
                'd.nama as nama_donatur',
                DB::raw('sum(td.nominal_donasi) as total_donasi'),
            ])
            ->groupBy([
                'transaksi.id',
                'd.nama',
            ])
            ->get();
        if (strtolower(Auth::user()->roles[0]->name) == 'admin') {
            $transaksi = $queryTransaksi;
        } else {
            $transaksi = $queryTransaksi->where('d.pegawai_id', Auth::user()->pegawai_id);
        }

        $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('r.name', 'Penghimpun')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        return view('setoran.create', compact('title', 'action', 'redirectUrl', 'setoran', 'transaksi', 'relawan'));
    }

    private function convertCurrenctToInt($currency)
    {
        return (int) preg_replace("/\..+$/i", '', preg_replace("/[^0-9\.]/i", '', $currency));
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
        request()->validate(
            [
                'nama' => 'required',
                'pegawai_id' => 'required',
            ],
            [
                'nama.required' => 'Nama setoran wajib diisi',
                'pegawai_id.required' => 'Nama Penghimpun wajib dipilih',
            ]
        );

        $input = $request->all();
        if ($input['pekerjaan'] == 'lainnya') {
            $input['pekerjaan'] = $input['pekerjaan'] . '-' . $input['lainnya'];
        }

        setoran::find($id)
            ->update($input);

        return redirect()->route('setoran.index')
            ->with('success', ucfirst('Ubah ' . $this->title . ' Berhasil'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        setoran::find($id)->delete();

        return redirect()->route('setoran.index')
            ->with('success', ucfirst('Hapus ' . $this->title . ' berhasil'));
    }
}
