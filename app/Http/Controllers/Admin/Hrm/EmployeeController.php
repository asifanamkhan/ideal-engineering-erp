<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employees = DB::table('employees')
                ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                ->leftJoin('branches', 'employees.branch_id', '=', 'branches.id')
                ->select(['employees.*', 'designations.name as designation', 'branches.name as branch'])
                ->orderBy('employees.id', 'desc');

            // Apply search filter if search term exists
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];

                $employees->where(function ($query) use ($searchTerm) {
                    $query->where('employees.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('employees.email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('employees.phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('employees.employee_id', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('employees.current_address', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('designations.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('branches.name', 'LIKE', "%{$searchTerm}%");
                });
            }

            return DataTables::of($employees)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($employee) {
                    return $employee->id;
                })
                ->addColumn('image', function ($employee) {
                    if ($employee->photo && file_exists(base_path($employee->photo))) {
                        $imageUrl = asset($employee->photo);
                        return '<a href="' . $imageUrl . '" target="_blank"><img src="' . $imageUrl . '" class="rounded-circle" width="40" height="40" style="object-fit: cover;"></a>';
                    } else {
                        // Default avatar based on gender or name
                        $name = $employee->name;
                        $initial = strtoupper(substr($name, 0, 1));

                        return '<div class="avatar rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #4e73df; color: white; font-weight: bold;">' . $initial . '</div>';
                    }
                })
                ->addColumn('employee_id', function ($employee) {
                    return '<span class="badge bg-info">' . ($employee->employee_id ?? 'N/A') . '</span>';
                })
                ->addColumn('name', function ($employee) {
                    return '<strong>' . $employee->name . '</strong>';
                })
                ->addColumn('address', function ($employee) {
                    return $employee->current_address ?? 'N/A';
                })
                ->addColumn('phone', function ($employee) {
                    return $employee->phone ?? 'N/A';
                })
                ->addColumn('join_date_formatted', function ($employee) {
                    return $employee->join_date ? Carbon::parse($employee->join_date)->format('M d, Y') : 'N/A';
                })
                ->addColumn('branch', function ($employee) {
                    return $employee->branch ?? 'N/A';
                })
                ->addColumn('designation', function ($employee) {
                    return $employee->designation ?? 'N/A';
                })
                ->addColumn('employment_type_formatted', function ($employee) {
                    $types = [
                        'permanent' => '<span class="badge bg-success">Permanent</span>',
                        'contract' => '<span class="badge bg-info">Contract</span>',
                        'probation' => '<span class="badge bg-warning text-dark">Probation</span>',
                        'intern' => '<span class="badge bg-secondary">Intern</span>',
                        'part_time' => '<span class="badge bg-primary">Part Time</span>',
                    ];

                    return $types[$employee->employment_type] ?? '<span class="badge bg-secondary">' . ucfirst($employee->employment_type ?? 'N/A') . '</span>';
                })
                ->addColumn('status_badge', function ($employee) {
                    $status = $employee->status ?? 1;

                    if ($status == 1 || $status == 'active' || $status == 'Active') {
                        return '<span class="badge bg-success">Active</span>';
                    } elseif ($status == 0 || $status == 'inactive' || $status == 'Inactive') {
                        return '<span class="badge bg-danger">Inactive</span>';
                    } else {
                        return '<span class="badge bg-info">' . ucfirst($status) . '</span>';
                    }
                })
                ->addColumn('action', function ($employee) {
                    return view('admin.hrm.employees.partials.action-btn-view', ['id' => $employee->id])->render();
                })
                ->filter(function ($query) use ($request) {

                    if ($request->has('name') && !empty($request->name)) {
                        $query->where('employees.name', 'LIKE', "%{$request->name}%");
                    }
                    if ($request->has('email') && !empty($request->email)) {
                        $query->where('employees.email', 'LIKE', "%{$request->email}%");
                    }
                    if ($request->has('phone') && !empty($request->phone)) {
                        $query->where('employees.phone', 'LIKE', "%{$request->phone}%");
                    }
                    if ($request->has('employee_id') && !empty($request->employee_id)) {
                        $query->where('employees.employee_id', 'LIKE', "%{$request->employee_id}%");
                    }
                    if ($request->has('branch_id') && !empty($request->branch_id)) {
                        $query->where('employees.branch_id', $request->branch_id);
                    }
                    if ($request->has('designation_id') && !empty($request->designation_id)) {
                        $query->where('employees.designation_id', $request->designation_id);
                    }
                    if ($request->has('status') && $request->status !== '') {
                        $query->where('employees.status', $request->status);
                    }
                    if ($request->has('employment_type') && !empty($request->employment_type)) {
                        $query->where('employees.employment_type', $request->employment_type);
                    }
                })
                ->rawColumns(['image', 'employee_id', 'name', 'employment_type_formatted', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.hrm.employees.index');
    }

    public function create()
    {
        $branches = DB::table('branches')->where('status', 1)->get();
        $designations = DB::table('designations')->where('status', 1)->get();
        return view('admin.hrm.employees.create', compact('branches', 'designations'));
    }

    /**
     * Generate employee ID in format IEW00001
     */
    private function generateEmployeeId()
    {
        // Get the last employee ID
        $lastEmployee = DB::table('employees')
            ->where('employee_id', 'LIKE', 'IEW%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEmployee) {
            // Extract the number part (after IEW)
            $lastNumber = intval(substr($lastEmployee->employee_id, 3));
            $newNumber = $lastNumber + 1;
        } else {
            // Start from 1 if no employees exist
            $newNumber = 1;
        }

        // Format with leading zeros (5 digits)
        $formattedNumber = str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        return 'IEW' . $formattedNumber;
    }

    public function store(Request $request)
    {
        // Validation rules with all new fields
        $validator = Validator::make($request->all(), [
            // Personal Information
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            try {
                $photoFile = $request->file('photo');

                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();

                // Store in public/uploads/employees folder
                $photoPath = 'public/uploads/employees/' . $filename;
                $photoFile->move(public_path('uploads/employees'), $filename);
            } catch (\Exception $e) {
                $photoPath = null;
            }
        }

        // Generate Employee ID
        $employeeId = $this->generateEmployeeId();

        // Create employee with all fields
        try {
            DB::table('employees')->insert([
                // Employee ID
                'employee_id' => $employeeId,

                // Personal Information
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'phone_two' => $request->phone_two,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'national_id' => $request->national_id,

                // Family Information
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'marital_status' => $request->marital_status,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,

                // Address Information
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,

                // Professional Information
                'join_date' => $request->join_date,
                'employment_type' => $request->employment_type,
                'branch_id' => $request->branch_id,
                'designation_id' => $request->designation_id,

                // Photo
                'photo' => $photoPath,

                // Status (default to 'applied' if not provided)
                'status' => $request->status ?? 'applied',

                // Timestamps
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Employee created with ID: ' . $employeeId);

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee created successfully! Employee ID: ' . $employeeId);
        } catch (\Exception $e) {
            Log::error('Employee creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create employee. Please try again.')
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $employee = DB::table('employees')
                ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                ->leftJoin('branches', 'employees.branch_id', '=', 'branches.id')
                ->select([
                    'employees.*',
                    'designations.name as designation_name',
                    'branches.name as branch_name'
                ])
                ->where('employees.id', $id)
                ->first();

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            // Return the view partial as HTML
            $html = view('admin.hrm.employees.partials._view-details', compact('employee'))->render();

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            Log::error('Employee show failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load employee details'], 500);
        }
    }

    public function edit($id)
    {
        try {
            $employee = DB::table('employees')->where('id', $id)->first();

            if (!$employee) {
                return redirect()->route('admin.employees.index')
                    ->with('error', 'Employee not found');
            }

            $branches = DB::table('branches')->where('status', 1)->get();
            $designations = DB::table('designations')->where('status', 1)->get();

            return view('admin.hrm.employees.edit', compact('employee', 'branches', 'designations'));
        } catch (\Exception $e) {
            Log::error('Employee edit failed: ' . $e->getMessage());
            return redirect()->route('admin.employees.index')
                ->with('error', 'Failed to load employee data');
        }
    }

    public function update(Request $request, $id)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            // Personal Information
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email,' . $id,
            'phone' => 'required|string|max:20',

            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Status
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get existing employee
            $employee = DB::table('employees')->where('id', $id)->first();

            if (!$employee) {
                return redirect()->route('admin.employees.index')
                    ->with('error', 'Employee not found');
            }

            // Handle photo upload
            $photoPath = $employee->photo; // Keep existing photo by default

            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                try {
                    // Delete old photo if exists
                    if ($employee->photo && file_exists(public_path($employee->photo))) {
                        unlink(public_path($employee->photo));
                    }

                    $photoFile = $request->file('photo');
                    $filename = time() . '_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();
                    $photoPath = 'public/uploads/employees/' . $filename;
                    $photoFile->move(public_path('uploads/employees'), $filename);
                } catch (\Exception $e) {
                    Log::error('Photo upload failed during update: ' . $e->getMessage());
                    $photoPath = $employee->photo; // Keep old photo on error
                }
            }

            // Update employee
            DB::table('employees')->where('id', $id)->update([
                // Personal Information
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'phone_two' => $request->phone_two,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'national_id' => $request->national_id,

                // Family Information
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'marital_status' => $request->marital_status,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,

                // Address Information
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,

                // Professional Information
                'join_date' => $request->join_date,
                'employment_type' => $request->employment_type,
                'branch_id' => $request->branch_id,
                'designation_id' => $request->designation_id,

                // Photo
                'photo' => $photoPath,

                // Status
                'status' => $request->status ?? $employee->status,

                // Timestamps
                'updated_at' => now(),
            ]);

            Log::info('Employee updated with ID: ' . $id);

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee updated successfully!');
        } catch (\Exception $e) {
            Log::error('Employee update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update employee. Please try again.')
                ->withInput();
        }
    }
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);

            // Delete photo if exists
            if ($employee->photo && file_exists(public_path($employee->photo))) {
                unlink(public_path($employee->photo));
            }

            $employee->delete();

            return response()->json(['success' => true, 'message' => 'Employee deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete employee'], 500);
        }
    }
    // In your EmployeeController
    public function search(Request $request)
    {
        $search = $request->get('search');
        $employees = Employee::where('employees.status', 1) // Specify table name for status
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where(function ($query) use ($search) {
                $query->where('employees.name', 'like', "%{$search}%")
                    ->orWhere('employees.email', 'like', "%{$search}%")
                    ->orWhere('employees.employee_id', 'like', "%{$search}%");
            })
            ->limit(20)
            ->select(['employees.*', 'designations.name as designation_name'])
            ->get();

        $results = [];
        foreach ($employees as $employee) {
            $results[] = [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'designation' => $employee->designation_name,
                'text' => $employee->name . ' (' . $employee->email . ')'
            ];
        }

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false]
        ]);
    }

    public function salary_info()
    {
        $employees = DB::table('employees')
            ->where('employees.status', 1)
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->get(['employees.*', 'designations.name as designation_name']);
        return view('admin.hrm.employees.salary-info', compact('employees'));
    }
    public function getDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid employee selected'], 422);
        }

        try {
            // Get employee details with all salary fields
            $employee = DB::table('employees')
                ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                ->leftJoin('branches', 'employees.branch_id', '=', 'branches.id')
                ->select([
                    'employees.*',
                    'designations.name as designation_name',
                    'branches.name as branch_name'
                ])
                ->where('employees.id', $request->employee_id)
                ->first();

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            // Make sure default_overtime_hour is included
            if (!isset($employee->default_overtime_hour)) {
                $employee->default_overtime_hour = 0;
            }

            // Render partial views
            $detailsHtml = view('admin.hrm.employees.partials.employee-details', compact('employee'))->render();
            $formHtml = view('admin.hrm.employees.partials.salary-form', compact('employee'))->render();

            return response()->json([
                'success' => true,
                'details_html' => $detailsHtml,
                'form_html' => $formHtml
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load employee data: ' . $e->getMessage()], 500);
        }
    }

    public function saveSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'basic_salary' => 'required|numeric|min:0',
            'total_allowance' => 'nullable|numeric|min:0',
            'total_deduction' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'default_overtime_hour' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $basicSalary = $request->basic_salary;
            $totalAllowance = $request->total_allowance ?? 0;
            $totalDeduction = $request->total_deduction ?? 0;
            $overtimeRate = $request->overtime_rate ?? 0;
            $defaultOvertimeHour = $request->default_overtime_hour ?? 0;

            // Calculate gross salary
            $grossSalary = $basicSalary + $totalAllowance - $totalDeduction;

            // Update employee record with salary information
            $updateData = [
                'basic_salary' => $basicSalary,
                'total_allowance' => $totalAllowance,
                'total_deduction' => $totalDeduction,
                'overtime_rate' => $overtimeRate,
                'default_overtime_hour' => $defaultOvertimeHour,
                'gross_salary' => $grossSalary,
                // 'salary_effective_date' => $request->effective_date ?? date('Y-m-d'),
                'updated_at' => now()
            ];

            DB::table('employees')
                ->where('id', $request->employee_id)
                ->update($updateData);

            // Get the updated employee data
            $updatedEmployee = DB::table('employees')->where('id', $request->employee_id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Salary information saved successfully!',
                'gross_salary' => $grossSalary,
                'employee' => $updatedEmployee
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save salary information: ' . $e->getMessage()], 500);
        }
    }
}
