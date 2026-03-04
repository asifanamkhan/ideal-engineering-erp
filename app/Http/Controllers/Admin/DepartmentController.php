<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('id', 'desc')->get();
        return view('admin.department.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:191','unique:departments,name'],
            'code' => ['nullable','string','max:50','unique:departments,code'],
            'description' => ['nullable','string'],
        ]);

        Department::create($data);

        return response()->json(['success' => true, 'message' => 'Department created successfully!']);
    }

    public function edit(Department $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => ['required','string','max:191', Rule::unique('departments','name')->ignore($department->id)],
            'code' => ['nullable','string','max:50', Rule::unique('departments','code')->ignore($department->id)],
            'description' => ['nullable','string'],
        ]);

        $department->update($data);
        return response()->json(['success' => true, 'message' => 'Department updated successfully!']);
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['success' => true, 'message' => 'Department deleted successfully!']);
    }
}
