<?php

namespace App\Http\Controllers;

use App\Models\Pekerjaan;
use DataTables;
use Illuminate\Http\Request;

class PekerjaanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:pekerjaan-list|pekerjaan-create|pekerjaan-edit|pekerjaan-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:pekerjaan-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:pekerjaan-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:pekerjaan-delete', ['only' => ['destroy']]);
        $this->title = 'Data Pekerjaan';
        $this->redirectUrl = route('pekerjaan.index');
    }

    public function index()
    {
        $title = $this->title;

        return view('pekerjaan.index', compact('title'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData()
    {
        $data = Pekerjaan::get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($pekerjaan) {
                return view('pekerjaan.action', compact('pekerjaan'));
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
        $action = route('pekerjaan.store');
        $redirectUrl = $this->redirectUrl;

        return view('pekerjaan.create', compact('title', 'action', 'redirectUrl'));
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
        ], [
            'nama.required' => 'Nama Perkerjaan wajib diisi',
        ]
        );

        Pekerjaan::create($request->all());

        return redirect()->route('pekerjaan.index')
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
        $pekerjaan = Pekerjaan::find($id);
        $title = 'Show '.$this->title;
        $action = '#';
        $show = 'disabled';
        $redirectUrl = $this->redirectUrl;

        return view('pekerjaan.create', compact('title', 'action', 'redirectUrl', 'pekerjaan', 'show'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pekerjaan = Pekerjaan::find($id);
        $redirectUrl = $this->redirectUrl;
        $title = 'Ubah '.$this->title;
        $action = route('pekerjaan.update', $id);

        return view('pekerjaan.create', compact('title', 'action', 'redirectUrl', 'pekerjaan'));
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
        ], [
            'nama,required' => 'Nama Perkerjaan wajib diisi',
        ]
        );

        Pekerjaan::find($id)
            ->update($request->all());

        return redirect()->route('pekerjaan.index')
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
        Pekerjaan::find($id)->delete();

        return redirect()->route('pekerjaan.index')
                        ->with('success', ucfirst('Hapus '.$this->title.' berhasil'));
    }
}
