<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\File as Files;
use App\Models\Pegawai;
use App\Models\Pekerjaan;
use App\Models\Program;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use App\Services\GoogleSheetService;
use Auth;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class TransaksiController extends Controller
{
    public $title;

    public $redirectUrl;

    public function __construct()
    {
        $this->middleware('permission:transaksi-list|transaksi-create|transaksi-edit|transaksi-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:transaksi-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:transaksi-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:transaksi-delete', ['only' => ['destroy']]);
        $this->title = 'Data Transaksi';
        $this->redirectUrl = route('transaksi.index');
    }

    public function index()
    {
        // $SheetService = new GoogleSheetService;
        // $SheetService->storeSheet();
        $title = $this->title;

        return view('transaksi.index', compact('title'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData()
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $query = Transaksi::leftJoin('transaksi_detail as td', 'transaksi.id', '=', 'td.transaksi_id')
            ->leftJoin('pegawai as p', 'transaksi.pegawai_id', '=', 'p.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->select([
                'transaksi.tanggal as tanggal_donasi',
                'p.nama as nama_relawan',
                'd.nama as nama_donatur',
                DB::raw('sum(td.nominal_donasi) as total_donasi'),
                'transaksi.id',
                DB::raw("case when transaksi.jenis_transaksi = 'transfer' then 'Setoran Transfer' else 'Titip di Penghimpun' end as jenis_transaksi"),
                'transaksi.keterangan',
            ])
            ->groupBy([
                'transaksi.id',
                'transaksi.tanggal',
                'p.nama',
                'd.nama',
                DB::raw("case when transaksi.jenis_transaksi = 'transfer' then 'Setoran Transfer' else 'Titip di Penghimpun' end"),
                'transaksi.keterangan',
            ]);
        if (in_array($role, ['admin', 'manager'])) {
            $data = $query->get();
        } elseif ($role == 'penghimpun') {
            $data = $query->where('transaksi.pegawai_id', Auth::user()->pegawai_id)->get();
        } else {
            $data = $query->leftJoin('korel as k', function ($join) {
                $join->on('transaksi.pegawai_id', '=', 'k.bawahan_id');
                $join->orOn('transaksi.pegawai_id', '=', 'k.kepala_id', 'or');
            })
                ->where('k.kepala_id', Auth::user()->pegawai_id)->get();
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($transaksi) {
                return view('transaksi.action', compact('transaksi'));
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
        $action = route('transaksi.store');
        $redirectUrl = $this->redirectUrl;
        $pegawai_id = Auth::user()->pegawai_id;
        $program = Program::where('status', true)->get();
        $role = strtolower(Auth::user()->roles[0]->name);
        if ($role == 'penghimpun') {
            $donatur = Donatur::where('pegawai_id', $pegawai_id)->get();
            $relawan = Pegawai::where('id', $pegawai_id)->get();
        } elseif (strtolower(Auth::user()->roles[0]->name) == 'supervisor') {
            $donatur = Donatur::join('korel as k', function ($join) {
                $join->on('donatur.pegawai_id', '=', 'k.bawahan_id');
                $join->orOn('donatur.pegawai_id', '=', 'k.kepala_id', 'or');
            })
                ->select([
                    'donatur.*',
                ])
                ->get();

            $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
                ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->join('korel as k', function ($join) {
                    $join->on('p.id', '=', 'k.bawahan_id');
                    $join->orOn('p.id', '=', 'k.kepala_id', 'or');
                })
                ->where('k.kepala_id', $pegawai_id)
                ->select([
                    'p.id',
                    'p.nama',
                    'p.default',
                ])
                ->get();
        } else {
            $donatur = Donatur::get();
            $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
                ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->where('r.name', 'Penghimpun')
                ->select([
                    'p.id',
                    'p.nama',
                    'p.default',
                ])
                ->get();
        }

        $pekerjaan = Pekerjaan::get();

        return view('transaksi.create', compact('title', 'action', 'redirectUrl', 'relawan', 'donatur', 'pekerjaan', 'program'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $checkDonatur = $request->input('status');
        $validation = [];
        $message = [];
        if ($checkDonatur == 'baru') {
            $validation['nama'] = 'required';
            $message['nama.required'] = 'Nama donatur wajib diisi';
        } else {
            $validation['donatur_id'] = 'required';
            $message['donatur_id.required'] = 'Nama donatur wajib dipilih';
        }
        $jenis_transaksi = $request->input('jenis_transaksi');
        if ($jenis_transaksi == 'transfer') {
            $validation['image'] = 'required';
            $message['image.required'] = 'Bukti transfer belum dipilih';
        }

        $googleService = new GoogleSheetService;

        request()->validate($validation, $message);

        DB::transaction(function () use ($request, $checkDonatur, $jenis_transaksi, $googleService) {
            if (strtolower(Auth::user()->roles[0]->name) == 'admin') {
                $pegawai_id = $request->input('pegawai_id');
            } else {
                $pegawai_id = Auth::User()->pegawai_id;
            }
            if ($checkDonatur == 'baru') {
                $dataDonatur['pegawai_id'] = $pegawai_id;
                $dataDonatur['nama'] = $request->input('nama');
                $dataDonatur['no_telepon'] = $request->input('no_telepon');
                $dataDonatur['alamat'] = $request->input('alamat');
                if ($request->input('pekerjaan') == 'lainnya') {
                    $dataDonatur['pekerjaan'] = $request->input('pekerjaan') . '-' . $request->input('lainnya');
                } else {
                    $dataDonatur['pekerjaan'] = $request->input('pekerjaan');
                }

                $donatur = Donatur::create($dataDonatur);

                $donatur_id = $donatur->id;
            } else {
                $donatur_id = $request->input('donatur_id');
            }

            if ($jenis_transaksi == 'transfer') {
                $image = $request->file('image');
                $destinationPath = 'storage/image/transaksi/';
                $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $filename);
                $fileData['jenis'] = 'Transaksi';
                $fileData['path'] = $destinationPath;
                $fileData['nama'] = $filename;
                $file = Files::create($fileData);
                $t['file_id'] = $file->id;
            }

            $t['tanggal'] = date('Y-m-d', strtotime($request->input('tanggal')));
            $t['keterangan'] = $request->input('keterangan');
            $t['donatur_id'] = $donatur_id;
            $t['pegawai_id'] = $pegawai_id;
            $t['jenis_transaksi'] = $jenis_transaksi;

            $transaksi = Transaksi::create($t);

            $nominal = $request->input('nominal_donasi');
            $program_id = $request->input('program_id');
            $n = 0;
            $donasi = [];
            foreach ($nominal as $nml) {
                $td = [];
                $nominal_donasi = $this->convertCurrenctToInt($nml);
                if ($nominal_donasi > 0) {
                    $donasi[$program_id[$n]] = $nominal_donasi;
                    $td['transaksi_id'] = $transaksi->id;
                    $td['program_id'] = $program_id[$n];
                    $td['nominal_donasi'] = $nominal_donasi;
                    TransaksiDetail::create($td);
                }
                $n++;
            }
            $googleService->storeTransaksi($transaksi->id, $donasi);
        });

        return redirect()->route('transaksi.index')
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
        $transaksi = transaksi::leftJoin('file as f', 'transaksi.file_id', '=', 'f.id')
            ->where('transaksi.id', $id)
            ->select([
                'transaksi.*',
                'f.path',
                'f.nama as nama_file',
            ])
            ->first();

        $transaksi_detail = TransaksiDetail::leftJoin('program as p', 'transaksi_detail.program_id', 'p.id')
            ->select([
                'p.nama as nama_program',
                'transaksi_detail.nominal_donasi',
            ])
            ->where('transaksi_id', $id)
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

        return view('transaksi.show', compact('title', 'action', 'redirectUrl', 'transaksi', 'transaksi_detail', 'show', 'relawan', 'donatur'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $transaksi = transaksi::find($id);
        $redirectUrl = $this->redirectUrl;
        $title = 'Edit ' . $this->title;
        $action = route('transaksi.update', $id);

        $program = TransaksiDetail::leftJoin('program as p', 'p.id', '=', 'transaksi_detail.program_id')
            ->select([
                'transaksi_detail.id',
                'p.nama',
                'transaksi_detail.nominal_donasi',
            ])
            ->where('transaksi_id', $id)->get();
        if (strtolower(Auth::user()->roles[0]->name) == 'admin') {
            $donatur = Donatur::get();
        } elseif (strtolower(Auth::user()->roles[0]->name) == 'supervisor') {
            $donatur = Donatur::join('korel as k', function ($join) {
                $join->on('donatur.pegawai_id', '=', 'k.bawahan_id');
                $join->orOn('donatur.pegawai_id', '=', 'k.kepala_id', 'or');
            })
                ->select([
                    'donatur.*',
                ])
                ->get();
        } else {
            $donatur = Donatur::where('pegawai_id', Auth::user()->pegawai_id)->get();
        }

        $pekerjaan = Pekerjaan::get();
        $relawan = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            // ->where('r.name', 'Penghimpun')
            ->select([
                'p.id',
                'p.nama',
            ])
            ->get();

        return view('transaksi.edit', compact('title', 'action', 'redirectUrl', 'transaksi', 'relawan', 'donatur', 'pekerjaan', 'program'));
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
        // $checkDonatur = $request->input('status');
        $validation = [];
        $message = [];
        $validation['donatur_id'] = 'required';
        $message['donatur_id.required'] = 'Nama donatur wajib dipilih';
        $jenis_transaksi = $request->input('jenis_transaksi');
        // if($jenis_transaksi == 'transfer'){
        //     $validation['image'] = 'required';
        //     $message['image.required'] = 'Bukti transfer belum dipilih';
        // }

        request()->validate($validation, $message);

        DB::transaction(function () use ($id, $request, $jenis_transaksi) {
            if (strtolower(Auth::user()->roles[0]->name) == 'admin') {
                $pegawai_id = $request->input('pegawai_id');
            } else {
                $pegawai_id = Auth::User()->pegawai_id;
            }
            $donatur_id = $request->input('donatur_id');

            // if($jenis_transaksi == 'transfer'){
            //     $image = $request->file('image');
            //     $destinationPath = 'storage/image/transaksi/';
            //     $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
            //     $image->move($destinationPath, $filename);
            //     $fileData['jenis'] = 'Transaksi';
            //     $fileData['path'] = $destinationPath;
            //     $fileData['nama'] = $filename;
            //     $file = File::create($fileData);
            //     $t['file_id'] = $file->id;
            // }

            $t['tanggal'] = date('Y-m-d', strtotime($request->input('tanggal')));
            $t['keterangan'] = $request->input('keterangan');
            $t['donatur_id'] = $donatur_id;
            $t['pegawai_id'] = $pegawai_id;
            $t['jenis_transaksi'] = $jenis_transaksi;

            $transaksi = Transaksi::where('id', $id)->update($t);

            $nominal = $request->input('nominal_donasi');
            $program_id = $request->input('program_id');
            $n = 0;
            foreach ($nominal as $nml) {
                $td = [];
                $nominal_donasi = $this->convertCurrenctToInt($nml);
                if ($nominal_donasi > 0) {
                    // $td['transaksi_id'] = $transaksi->id;
                    // $td['program_id'] = $program_id[$n];
                    $td['nominal_donasi'] = $nominal_donasi;
                    TransaksiDetail::where('id', $program_id[$n])->update($td);
                }
                $n++;
            }
        });

        return redirect()->route('transaksi.index')
            ->with('success', ucfirst('Tambah ' . $this->title . ' Berhasil'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);
        if ($transaksi->file_id) {
            $file = Files::find($transaksi->file_id);
            $filepath = $file->path . $file->nama;
            File::delete($filepath);
        }
        $transaksi->delete();
        TransaksiDetail::where('transaksi_id', $id)->delete();

        return redirect()->route('transaksi.index')
            ->with('success', ucfirst('Hapus ' . $this->title . ' berhasil'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function importPdf($id)
    {
        // Ambil data transaksi dari database berdasarkan ID
        // $transaksi = Transaksi::find($id);
        $transaksi_detail = TransaksiDetail::leftJoin('program as p', 'transaksi_detail.program_id', 'p.id')
            ->select([
                'p.nama as nama_program',
                'transaksi_detail.nominal_donasi',
            ])
            ->where('transaksi_id', $id)
            ->get();
        $transaksi = Transaksi::leftJoin('transaksi_detail as td', 'transaksi.id', '=', 'td.transaksi_id')
            ->leftJoin('pegawai as p', 'transaksi.pegawai_id', '=', 'p.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->leftJoin('program', 'td.program_id', '=', 'program.id') // New left join for the program
            ->select([
                'transaksi.tanggal as tanggal_donasi',
                'p.nama as nama_relawan',
                'd.nama as nama_donatur',
                'program.nama as nama_program', // Include program name in the select statement
                DB::raw('sum(td.nominal_donasi) as total_donasi'),
                'transaksi.id',
                DB::raw("case when transaksi.jenis_transaksi = 'transfer' then 'Setoran Transfer' else 'Titip di Penghimpun' end as jenis_transaksi"),
                'transaksi.keterangan',
            ])
            ->where('transaksi.id', $id)
            ->groupBy([
                'transaksi.id',
                'transaksi.tanggal',
                'p.nama',
                'd.nama',
                'program.nama', // Include program name in the group by statement
                DB::raw("case when transaksi.jenis_transaksi = 'transfer' then 'Setoran Transfer' else 'Titip di Penghimpun' end"),
                'transaksi.keterangan',
            ])
            ->first(); // Menggunakan first() karena hanya mengambil satu transaksi
        $redirectUrl = $this->redirectUrl;
        $title = 'Print ' . $this->title;

        if (!$transaksi) {
            return redirect()->route('transaksi.index')->with('error', 'Transaksi tidak ditemukan');
        }

        // Format data transaksi sesuai kebutuhan
        $content = View::make('transaksi.print', compact('title', 'transaksi', 'redirectUrl', 'transaksi_detail'))->render();

        // Tambahkan informasi lain sesuai dengan struktur transaksi Anda


        // Untuk Download :

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML($content);
        // $filename = 'transaksi_' . $transaksi->id . '.pdf';
        // $mpdf->Output($filename, 'D'); 


        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($content);
        $filename = 'transaksi_' . $transaksi->id . '.pdf';
        $mpdf->Output($filename, 'I');
        exit;
    }
}
