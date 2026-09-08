<?php

namespace App\Http\Controllers;

use DataTables;
use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'indexData', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
        $this->title = 'Pengaturan Role';
        $this->redirectUrl = route('roles.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('id', 'DESC')->paginate(5);
        $title = $this->title;

        return view('roles.index', compact('title', 'roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function indexData()
    {
        $data = Role::select('id', 'name')->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($role) {
                return view('role.action', compact('role'));
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
        $redirectUrl = $this->redirectUrl;
        $permission = Permission::select([
            DB::raw("split_part(name, '-', 1) as name"),
            DB::raw("array_to_string(ARRAY_AGG(id), ', ', '*') as list_id"),
            DB::raw("array_to_string(ARRAY_AGG(split_part(name, '-', 2)), ', ', '*') as list_role"),
        ])
            ->groupBy([DB::raw("split_part(name, '-', 1)")])
            ->get();

        return view('roles.create', compact('title', 'redirectUrl', 'permission'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        return redirect()->route('roles.index')
                        ->with('success', 'Role created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $title = 'Show '.$this->title;
        $redirectUrl = $this->redirectUrl;
        $role = Role::find($id);
        $rolePermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $id)
            ->get();

        return view('roles.show', compact('title', 'redirectUrl', 'role', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $title = 'Ubah '.$this->title;
        $redirectUrl = $this->redirectUrl;
        $role = Role::find($id);
        // $permission = Permission::get();
        $permission = Permission::select([
            DB::raw("split_part(name, '-', 1) as name"),
            DB::raw("array_to_string(ARRAY_AGG(id), ', ', '*') as list_id"),
            DB::raw("array_to_string(ARRAY_AGG(split_part(name, '-', 2)), ', ', '*') as list_role"),
        ])
            ->groupBy([DB::raw("split_part(name, '-', 1)")])
            ->get();
        $rolePermissions = DB::table('role_has_permissions')->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        return view('roles.edit', compact('title', 'redirectUrl', 'role', 'permission', 'rolePermissions'));
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
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();

        $role->syncPermissions($request->input('permission'));

        return redirect()->route('roles.index')
                        ->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('roles')->where('id', $id)->delete();

        return redirect()->route('roles.index')
                        ->with('success', 'Role deleted successfully');
    }
}
