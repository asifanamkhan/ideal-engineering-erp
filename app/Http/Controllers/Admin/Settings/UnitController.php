<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $units = DB::table('units')->select('*')
                    ->orderBy('is_default', 'DESC')
                    ->orderBy('id', 'desc');

            return DataTables::of($units)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($unit) {
                    return $unit->id;
                })
                ->addColumn('status_badge', function ($unit) {
                    $status = $unit->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('is_default', function ($unit) {
                    $status = $unit->is_default == 1 ? 'Yes' : '';
                    $badgeClass = $status === 'Yes' ? 'badge bg-primary' : ' ';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($unit) {
                    return view('admin.settings.units.partials.action-btn-view', ['id' => $unit->id])->render();
                })
                ->rawColumns(['status_badge', 'action','is_default'])
                ->make(true);
        }

        return view('admin.settings.units.index');
    }

    public function create()
    {
        return view('admin.settings.units.partials.create-modal');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191', 'unique:units'],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? 1;

        Unit::create($data);

        if($data['is_default'] == 1){
            DB::table('units')
                ->where('id', '!=', $data['id'])
                ->update(['is_default' => 0]);
        }

        return response()->json(['success' => true, 'message' => 'Unit created successfully!']);
    }

    public function edit($id)
    {
        try {
            $unit = Unit::findOrFail($id);
            // Return JSON data instead of view
            return response()->json([
                'id' => $unit->id,
                'name' => $unit->name,
                'price' => $unit->price,
                'status' => $unit->status,
                'description' => $unit->description,
                'is_default' => $unit->is_default
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unit not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('units')->ignore($id)
            ],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'is_default' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $unit = Unit::findOrFail($id);
            $data = $request->all();
            $data['status'] = $request->status ?? $unit->status;

            $unit->update($data);

            if($data['is_default'] == 1){
                DB::table('units')
                    ->where('id', '!=', $id)
                    ->update(['is_default' => 0]);
            }

            return response()->json(['success' => true, 'message' => 'Unit updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update unit'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $unit = Unit::findOrFail($id);
            $unit->delete();

            return response()->json(['success' => true, 'message' => 'Unit deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete unit'], 500);
        }
    }
}
