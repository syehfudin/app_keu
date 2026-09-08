<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use Auth;
use DataTables;
use DB;
use Hash;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:pegawai-list|pegawai-create|pegawai-edit|pegawai-delete', ['only' => ['index', 'show', 'indexData']]);
        $this->middleware('permission:pegawai-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:pegawai-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:pegawai-delete', ['only' => ['destroy']]);
        $this->title = 'Data Relawan';
        $this->redirectUrl = route('pegawai.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = $this->title;

        return view('pegawai.index', compact('title'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData(Request $request)
    {
        $role = strtolower(Auth::user()->roles[0]->name);
        $query = User::join('pegawai as p', 'users.pegawai_id', '=', 'p.id')
            ->join('model_has_roles as mhr', 'users.id', '=', 'mhr.model_id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->select([
                'users.id',
                'p.nip',
                'p.nama',
                'users.username',
                'r.name as role',
            ]);

        if (in_array($role, ['admin', 'manager'])) {
            $data = $query->get();
        } else {
            $data = $query->leftJoin('korel as k', function ($join) {
                $join->on('p.id', '=', 'k.bawahan_id');
                $join->on('p.id', '=', 'k.kepala_id', 'or');
            })
                ->where('k.kepala_id', Auth::user()->pegawai_id)->get();
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($user) {
                return view('pegawai.action', compact('user'));
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
        $redirectUrl = $this->redirectUrl;
        $title = 'Tambah '.$this->title;
        $action = route('pegawai.store');
        $roles = Role::pluck('name', 'name')->all();

        return view('pegawai.create', compact('title', 'redirectUrl', 'roles', 'action'));
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
            'nip' => 'required',
            'nama' => 'required',
            'username' => 'required|unique:users,username',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
        ], [
            'nip.required' => 'ID Pegawai wajib diisi',
            'nama.required' => 'Nama Pegawai wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah terpakai',
            'password.required' => 'Password wajib diisi',
            'password.same' => 'Password dan Konfirmasi Password harus sama',
        ]
        );
        $default = $request->input('default') ? true : false;
        if ($default) {
            $updatePegawai = ['default' => false];
            Pegawai::where('default', true)->update($updatePegawai);
        }

        $data_pegawai['nama'] = $request->input('nama');
        $data_pegawai['nip'] = $request->input('nip');
        $data_pegawai['alamat'] = $request->input('alamat');
        $data_pegawai['dafault'] = $default;

        $pegawai = Pegawai::create($data_pegawai);

        $data_user['pegawai_id'] = $pegawai->id;
        $data_user['username'] = $request->input('username');
        $data_user['password'] = Hash::make($request->input('password'));

        $user = User::create($data_user);
        $user->assignRole($request->input('roles'));

        return redirect()->route('pegawai.index')
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
        $user = User::find($id);
        $redirectUrl = $this->redirectUrl;
        $show = 'disabled';
        $title = 'Show '.$this->title;
        $action = '#';
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('pegawai.create', compact('title', 'redirectUrl', 'action', 'user', 'roles', 'userRole', 'show'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $redirectUrl = $this->redirectUrl;
        $title = 'Ubah '.$this->title;
        $action = route('pegawai.update', $id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('pegawai.create', compact('title', 'redirectUrl', 'action', 'user', 'roles', 'userRole'));
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
            'nip' => 'required',
            'nama' => 'required',
            'username' => 'required',
            'password' => 'same:confirm-password',
            'roles' => 'required',
        ], [
            'nip.required' => 'ID Pegawai wajib diisi',
            'nama.required' => 'Nama Pegawai wajib diisi',
            'username.required' => 'Username wajib diisi',
            'password.same' => 'Password dan Konfirmasi Password harus sama',
        ]
        );

        $data_user['username'] = $request->input('username');
        if (! empty($request->input('password'))) {
            $data_user['password'] = Hash::make($request->input('password'));
        }
        $default = $request->input('default') ? true : false;

        if ($default) {
            $updatePegawai = ['default' => false];
            Pegawai::where('default', true)->update($updatePegawai);
        }

        $user = User::find($id);
        $user->update($data_user);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));

        $data_pegawai['default'] = $default;
        $data_pegawai['nama'] = $request->input('nama');
        $data_pegawai['nip'] = $request->input('nip');
        $data_pegawai['alamat'] = $request->input('alamat');

        Pegawai::find($user->pegawai_id)
            ->update($data_pegawai);

        return redirect()->route('pegawai.index')
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
        $user = User::find($id);
        Pegawai::find($user->pegawai_id)->delete();
        $user->delete();

        return redirect()->route('pegawai.index')
                        ->with('success', ucfirst('Hapus '.$this->title.' berhasil'));
    }
}
