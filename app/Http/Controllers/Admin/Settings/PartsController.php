<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Parts;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PartsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $parts = DB::table('parts')->select('*')->orderBy('id', 'desc');

            return DataTables::of($parts)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($part) {
                    return $part->id;
                })
                ->addColumn('price', function ($part) {
                    return $part->price ? number_format($part->price, 2) : 'N/A';
                })
                ->addColumn('status_badge', function ($part) {
                    $status = $part->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($part) {
                    return view('admin.settings.parts.partials.action-btn-view', ['id' => $part->id])->render();
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.settings.parts.index');
    }

    public function create()
    {
        return view('admin.settings.parts.partials.create-modal');
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:parts',
            'price' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $insertedId = DB::table('parts')->insertGetId([
                'name' => $request->name,
                'price' => $request->price ?? 0,
                'brand' => $request->brand,
                'model' => $request->model,
                'status' => $request->status ?? 1,
                'description' => $request->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Fetch the created part data
            $part = DB::table('parts')->where('id', $insertedId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Part created successfully!',
                'part' => [
                    'id' => $part->id,
                    'name' => $part->name,
                    'brand' => $part->brand,
                    'model' => $part->model,
                    'price' => $part->price
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create part: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $part = Parts::findOrFail($id);
            // Return JSON data instead of view
            return response()->json([
                'id' => $part->id,
                'name' => $part->name,
                'price' => $part->price,
                'status' => $part->status,
                'description' => $part->description
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Part not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('parts')->ignore($id)
            ],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $part = Parts::findOrFail($id);
            $data = $request->all();
            $data['status'] = $request->status ?? $part->status;

            $part->update($data);

            return response()->json(['success' => true, 'message' => 'Part updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update part'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $part = Parts::findOrFail($id);
            $part->delete();

            return response()->json(['success' => true, 'message' => 'Part deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete part'], 500);
        }
    }
}