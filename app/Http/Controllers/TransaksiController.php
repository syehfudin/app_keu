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
use App\Traits\HasHierarchy;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class TransaksiController extends Controller
{
    use HasHierarchy;
    public $title;

    public $redirectUrl;

    public function __construct()
    {
        $this->middleware('permission:transaksi-list|transaksi-create|transaksi-edit|transaksi-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:transaksi-create', ['only' => ['create', 'store', 'getDonaturByPegawai']]);
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

        // Kirim daftar penghimpun untuk dropdown filter di halaman index.
        // Admin/Manager: semua penghimpun. Supervisor: bawahannya. Penghimpun:
        // hanya dirinya (dropdown berisi 1 item, efektif read-only).
        $role = strtolower(Auth::user()->roles[0]->name);
        $pegawai_id = Auth::user()->pegawai_id;
        $accessibleIds = $this->getAccessiblePegawaiIds();

        if ($role == 'penghimpun') {
            $penghimpunList = Pegawai::where('id', $pegawai_id)->get(['id', 'nama']);
        } elseif ($role == 'supervisor') {
            // Bawahan supervisor + dirinya sendiri
            $ids = $accessibleIds; // sudah termasuk diri sendiri
            $penghimpunList = Pegawai::whereIn('id', $ids)->orderBy('nama', 'asc')->get(['id', 'nama']);
        } else {
            // Admin / Manager: semua user dengan role Penghimpun
            $penghimpunList = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
                ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->where('r.name', 'Penghimpun')
                ->orderBy('p.nama', 'asc')
                ->get(['p.id', 'p.nama']);
        }

        return view('transaksi.index', compact('title', 'penghimpunList'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData(Request $request)
    {
        $query = Transaksi::leftJoin('pegawai as p', 'transaksi.pegawai_id', '=', 'p.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->select([
                'transaksi.id',
                'transaksi.tanggal as tanggal_donasi',
                'p.nama as nama_relawan',
                'd.nama as nama_donatur',
                'transaksi.jenis_transaksi',
                'transaksi.keterangan',
            ]);

        $accessibleIds = $this->getAccessiblePegawaiIds();
        if ($accessibleIds === null) {
            // Admin / Manager: tidak ada batasan pegawai_id
        } else {
            $query->whereIn('transaksi.pegawai_id', $accessibleIds);
        }

        // ===== FILTER: Range Tanggal =====
        // Format input dari datepicker: d-m-Y (dd-mm-yyyy). Konversi ke Y-m-d.
        $tanggal_mulai = $request->input('tanggal_mulai');
        $tanggal_sampai = $request->input('tanggal_sampai');
        if (!empty($tanggal_mulai)) {
            $mulai = date('Y-m-d', strtotime($tanggal_mulai));
            $query->whereDate('transaksi.tanggal', '>=', $mulai);
        }
        if (!empty($tanggal_sampai)) {
            $sampai = date('Y-m-d', strtotime($tanggal_sampai));
            $query->whereDate('transaksi.tanggal', '<=', $sampai);
        }

        // ===== FILTER: Penghimpun =====
        $pegawai_id = $request->input('pegawai_id');
        if (!empty($pegawai_id)) {
            $query->where('transaksi.pegawai_id', $pegawai_id);
        }

        // Urutkan transaksi terbaru lebih dulu (default order juga
        // dikirim dari client: order[0][column]=tanggal desc).
        $data = $query->orderBy('transaksi.tanggal', 'desc')
            ->orderBy('transaksi.id', 'desc')
            ->get();

        // ===== JENIS DONASI (program: nominal) =====
        // Kumpulkan detail program per transaksi untuk kolom "Jenis Donasi".
        $transaksiIds = $data->pluck('id')->all();
        $details = TransaksiDetail::leftJoin('program as pr', 'transaksi_detail.program_id', '=', 'pr.id')
            ->whereIn('transaksi_detail.transaksi_id', $transaksiIds)
            ->where('transaksi_detail.nominal_donasi', '>', 0)
            ->orderBy('pr.id', 'asc')
            ->get(['transaksi_detail.transaksi_id', 'pr.nama', 'transaksi_detail.nominal_donasi']);

        $detailMap = [];
        foreach ($details as $detail) {
            $detailMap[$detail->transaksi_id][] = '<div>' . $detail->nama . ': Rp ' . number_format((float) $detail->nominal_donasi, 0, ',', '.') . '</div>';
        }

        $data->transform(function ($item) use ($detailMap) {
            $item->jenis_donasi = implode('', $detailMap[$item->id] ?? ['-']);
            $item->total_donasi = TransaksiDetail::where('transaksi_id', $item->id)->sum('nominal_donasi');
            $item->jenis_transaksi = $item->jenis_transaksi == 'transfer' ? 'Setoran Transfer' : ($item->jenis_transaksi == 'rek_ulama' ? 'Setoran ke Rek Ulama' : 'Titip di Penghimpun');
            return $item;
        });

        // Global search DataTables DIBATASI hanya ke kolom nama_donatur
        // (Nama Nasabah). whitelist() memastikan di sisi server hanya kolom
        // tersebut yang ikut global search, terlepas dari flag client.
        // (Pertahanan ganda: di JS, kolom lain juga diset searchable:false.)
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($transaksi) {
                return view('transaksi.action', compact('transaksi'));
            })
            ->rawColumns(['action', 'jenis_donasi'])
            ->whitelist(['nama_donatur'])
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
        $accessibleIds = $this->getAccessiblePegawaiIds();
        if ($role == 'penghimpun') {
            // Penghimpun: pre-load donatur miliknya (tidak ada dropdown penghimpun,
            // tidak ada AJAX). Perilaku tidak berubah.
            $donatur = Donatur::where('pegawai_id', $pegawai_id)->orderBy('nama', 'asc')->get();
            $relawan = Pegawai::where('id', $pegawai_id)->get();
        } elseif ($role == 'supervisor') {
            // Supervisor: donatur awal kosong, diisi via AJAX setelah penghimpun
            // (bawahan supervisor) dipilih di dropdown.
            $donatur = collect();
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
            // Admin / Manager: donatur awal kosong, diisi via AJAX setelah
            // penghimpun dipilih di dropdown.
            $donatur = collect();
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
     * AJAX: ambil daftar donatur (nasabah) milik seorang penghimpun.
     * Dipakai oleh form Tambah/Edit Transaksi saat Admin/Supervisor/Manager
     * memilih penghimpun terlebih dahulu, agar dropdown nasabah ter-filter
     * hanya menampilkan nasabah milik penghimpun yang dipilih.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $pegawai_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDonaturByPegawai(Request $request, $pegawai_id)
    {
        $donatur = Donatur::where('pegawai_id', $pegawai_id)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama']);

        return response()->json($donatur);
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
        if (in_array($jenis_transaksi, ['transfer', 'rek_ulama'])) {
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

            if (in_array($jenis_transaksi, ['transfer', 'rek_ulama'])) {
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
        $transaksi = Transaksi::leftJoin('pegawai as p', 'transaksi.pegawai_id', '=', 'p.id')
            ->leftJoin('donatur as d', 'transaksi.donatur_id', '=', 'd.id')
            ->leftJoin('file as f', 'transaksi.file_id', '=', 'f.id')
            ->where('transaksi.id', $id)
            ->select([
                'transaksi.*',
                'p.nama as nama_relawan',
                'd.nama as nama_donatur',
                'f.path',
                'f.nama as nama_file',
            ])
            ->first();

        if (!$transaksi) {
            return redirect()->route('transaksi.index')->with('error', 'Transaksi tidak ditemukan');
        }

        $transaksi_detail = TransaksiDetail::leftJoin('program as p', 'transaksi_detail.program_id', 'p.id')
            ->select([
                'p.nama as nama_program',
                'transaksi_detail.nominal_donasi',
            ])
            ->where('transaksi_id', $id)
            ->where('transaksi_detail.nominal_donasi', '>', 0)
            ->orderBy('p.id', 'asc')
            ->get();

        $total_donasi = $transaksi_detail->sum('nominal_donasi');

        $title = 'Show ' . $this->title;
        $action = '#';
        $show = 'disabled';
        $redirectUrl = $this->redirectUrl;

        return view('transaksi.show', compact('title', 'action', 'redirectUrl', 'transaksi', 'transaksi_detail', 'show', 'total_donasi'));
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
        $transaksi->nama_donatur = $transaksi->donatur_id ? Donatur::where('id', $transaksi->donatur_id)->value('nama') : null;
        // Load bukti transfer (file) agar preview tampil di form edit
        if (! empty($transaksi->file_id)) {
            $file = Files::find($transaksi->file_id);
            if ($file) {
                $transaksi->path = $file->path;
                $transaksi->nama_file = $file->nama;
            }
        }
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
            // Admin: donatur awal hanya milik penghimpun transaksi ini,
            // agar dropdown langsung menampilkan donatur yang relevan.
            // Jika user mengganti penghimpun, AJAX akan me-refresh daftar.
            $donatur = Donatur::where('pegawai_id', $transaksi->pegawai_id)->orderBy('nama', 'asc')->get();
        } elseif (strtolower(Auth::user()->roles[0]->name) == 'supervisor') {
            $donatur = Donatur::where('pegawai_id', $transaksi->pegawai_id)->orderBy('nama', 'asc')->get();
        } else {
            $donatur = Donatur::where('pegawai_id', Auth::user()->pegawai_id)->orderBy('nama', 'asc')->get();
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
        // Format input "Rp 200.000" (titik = pemisah ribuan).
        // Fungsi lama memotong ".000" karena menganggap titik sebagai
        // desimal, sehingga Rp 200.000 menjadi 200 (BUG). Sekarang:
        // buang semua karakter non-digit lalu cast ke int.
        return (int) preg_replace('/[^0-9]/i', '', (string) $currency);
    }

    /**
     * Update the specified resource from storage.
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

            $t['tanggal'] = date('Y-m-d', strtotime($request->input('tanggal')));
            $t['keterangan'] = $request->input('keterangan');
            $t['donatur_id'] = $donatur_id;
            $t['pegawai_id'] = $pegawai_id;
            $t['jenis_transaksi'] = $jenis_transaksi;

            $transaksiRow = Transaksi::find($id);

            // ===== Bukti transfer: upload/replace utk Setoran Transfer & Setoran ke Rek Ulama =====
            if (in_array($jenis_transaksi, ['transfer', 'rek_ulama']) && $request->hasFile('image')) {
                $image = $request->file('image');
                if ($image !== null && $image->isValid()) {
                    // Hapus file lama + record-nya bila ada
                    $oldFile = $transaksiRow->file_id ? Files::find($transaksiRow->file_id) : null;
                    if ($oldFile) {
                        $oldPath = $oldFile->path . $oldFile->nama;
                        if (File::exists($oldPath)) {
                            try {
                                File::delete($oldPath);
                            } catch (\Exception $e) {
                            }
                        }
                        $oldFile->delete();
                    }

                    $destinationPath = 'storage/image/transaksi/';
                    $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
                    $image->move($destinationPath, $filename);
                    $fileData['jenis'] = 'Transaksi';
                    $fileData['path'] = $destinationPath;
                    $fileData['nama'] = $filename;
                    $file = Files::create($fileData);
                    $t['file_id'] = $file->id;
                }
            }

            // Jenis diganti ke cash: bukti lama tidak lagi relevan, reset file_id
            if ($jenis_transaksi == 'cash' && ! empty($transaksiRow->file_id)) {
                $oldFile = Files::find($transaksiRow->file_id);
                if ($oldFile) {
                    $oldPath = $oldFile->path . $oldFile->nama;
                    if (File::exists($oldPath)) {
                        try {
                            File::delete($oldPath);
                        } catch (\Exception $e) {
                        }
                    }
                    $oldFile->delete();
                }
                $t['file_id'] = null;
            }

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

        if (!$transaksi) {
            return redirect()->route('transaksi.index')
                ->with('error', 'Transaksi tidak ditemukan atau sudah dihapus');
        }

        DB::transaction(function () use ($transaksi, $id) {
            // Hapus file bukti transfer jika ada (null-safe).
            if ($transaksi->file_id) {
                $file = Files::find($transaksi->file_id);
                if ($file) {
                    $filepath = public_path($file->path . $file->nama);
                    if (File::exists($filepath)) {
                        File::delete($filepath);
                    }
                }
            }

            // Hapus detail terlebih dahulu, lalu transaksi utama.
            TransaksiDetail::where('transaksi_id', $id)->delete();
            $transaksi->delete();
        });

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