<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('roles')->select('users.*')->orderBy('users.id', 'desc');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($user) {
                    return $user->id;
                })
                ->addColumn('role_name', function ($user) {
                    $roles = $user->roles->pluck('name')->toArray();
                    if (empty($roles)) {
                        return '<span class="badge bg-secondary">No Role</span>';
                    }
                    $badges = '';
                    foreach ($roles as $role) {
                        $badges .= '<span class="badge bg-primary mr-1">' . $role . '</span>';
                    }
                    return $badges;
                })
                ->addColumn('status_badge', function ($user) {
                    $status = $user->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($user) {
                    return view('admin.user-management.users.partials.action-btn-view', [
                        'id' => $user->id,
                        'name' => $user->name
                    ])->render();
                })
                ->rawColumns(['role_name', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.user-management.users.index');
    }

    public function create()
    {
        $employees = Employee::where('status', 1)->get();
        $roles = Role::where('status', 1)->get();
        return response()->json([
            'employees' => $employees,
            'roles' => $roles
        ]);
    }

    public function getEmployeeDetails($id)
    {
        $employee = Employee::findOrFail($id);
        return response()->json([
            'name' => $employee->name,
            'email' => $employee->email,
            'designation' => $employee->designation,
            'phone' => $employee->phone
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:users,employee_id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:0,1'
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($request->employee_id);
            $user = User::create([
                'employee_id' => $request->employee_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'password' => Hash::make($request->password),
                'status' => $request->status,
            ]);

            // Assign multiple roles
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully with ' . count($roles) . ' role(s) assigned.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            $employees = Employee::where('status', 1)->get();
            $roles = Role::where('status', 1)->get();

            return response()->json([
                'user' => $user,
                'employees' => $employees,
                'roles' => $roles,
                'user_roles' => $user->roles->pluck('id')->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:users,employee_id,' . $id,
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:0,1'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $employee = Employee::findOrFail($request->employee_id);

            $updateData = [
                'employee_id' => $request->employee_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Sync multiple roles
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully with ' . count($roles) . ' role(s).'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $userName = $user->name;
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => "User '{$userName}' deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or could not be deleted.'
            ], 404);
        }
    }
}