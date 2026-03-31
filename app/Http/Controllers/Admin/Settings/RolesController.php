<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = DB::table('roles')->select('*')->orderBy('id', 'desc');

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($role) {
                    return $role->id;
                })

                ->addColumn('status_badge', function ($role) {
                    $status = $role->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($role) {
                    return view('admin.settings.roles.partials.action-btn-view', [
                        'id' => $role->id,
                        'name' => $role->name
                    ])->render();
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.settings.roles.index');
    }

    public function create()
    {
        $modules = Module::with('permissions')->get();
        return view('admin.settings.roles.create', compact('modules'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create(['name' => $request->name,'status' => 1, 'guard_name' => 'web']);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully with permissions.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $modules = Module::with('permissions')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.settings.roles.edit', compact('role', 'modules', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return redirect()->back()
            ->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
{
    try {
        $role = Role::findOrFail($id);
        $roleName = $role->name;
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => "Role '{$roleName}' deleted successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Role not found or could not be deleted.'
        ], 404);
    }
}
}