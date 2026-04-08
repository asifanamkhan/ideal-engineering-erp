<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sizes = DB::table('sizes')->select('*')->orderBy('id', 'desc');

            return DataTables::of($sizes)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($size) {
                    return $size->id;
                })
                ->addColumn('status_badge', function ($size) {
                    $status = $size->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($size) {
                    return view('admin.settings.sizes.partials.action-btn-view', ['id' => $size->id])->render();
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.settings.sizes.index');
    }

    public function create()
    {
        return view('admin.settings.sizes.partials.create-modal');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191', 'unique:sizes'],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? 1;

        Size::create($data);

        return response()->json(['success' => true, 'message' => 'Size created successfully!']);
    }

    public function edit($id)
    {
        try {
            $size = Size::findOrFail($id);
            // Return JSON data instead of view
            return response()->json([
                'id' => $size->id,
                'name' => $size->name,
                'price' => $size->price,
                'status' => $size->status,
                'description' => $size->description
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Size not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('sizes')->ignore($id)
            ],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $size = Size::findOrFail($id);
            $data = $request->all();
            $data['status'] = $request->status ?? $size->status;

            $size->update($data);

            return response()->json(['success' => true, 'message' => 'Size updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update size'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $size = Size::findOrFail($id);
            $size->delete();

            return response()->json(['success' => true, 'message' => 'Size deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete size'], 500);
        }
    }
}