<?php

namespace App\Http\Controllers;

use App\Models\Program;
use DataTables;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:program-list|program-create|program-edit|program-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:program-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:program-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:program-delete', ['only' => ['destroy']]);
        $this->title = 'Data Program';
        $this->redirectUrl = route('program.index');
    }

    public function index()
    {
        $title = $this->title;

        return view('program.index', compact('title'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData()
    {
        $data = Program::get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($program) {
                return view('program.action', compact('program'));
            })
            ->editColumn('status', function ($program) {
                if ($program->status == true) {
                    return 'Aktif';
                } else {
                    return 'Tidak Aktif';
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
        $action = route('program.store');
        $redirectUrl = $this->redirectUrl;

        return view('program.create', compact('title', 'action', 'redirectUrl'));
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
            'status' => 'required',
        ], [
            'nama.required' => 'Nama Program wajib diisi',
            'status.required' => 'status wajib dipilih',
        ]
        );

        Program::create($request->all());

        return redirect()->route('program.index')
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
        $program = Program::find($id);
        $title = 'Show '.$this->title;
        $action = '#';
        $show = 'disabled';
        $redirectUrl = $this->redirectUrl;

        return view('program.create', compact('title', 'action', 'redirectUrl', 'program', 'show'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $program = Program::find($id);
        $redirectUrl = $this->redirectUrl;
        $title = 'Ubah '.$this->title;
        $action = route('program.update', $id);

        return view('program.create', compact('title', 'action', 'redirectUrl', 'program'));
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
            'status' => 'required',
        ], [
            'nama.required' => 'Nama Program wajib diisi',
            'status.required' => 'Status wajib dipilih',
        ]
        );

        Program::find($id)
            ->update($request->all());

        return redirect()->route('program.index')
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
        Program::find($id)->delete();

        return redirect()->route('program.index')
                        ->with('success', ucfirst('Hapus '.$this->title.' berhasil'));
    }
}
