<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $services = DB::table('services')->select('*')->orderBy('id', 'desc');

            return DataTables::of($services)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($service) {
                    return $service->id;
                })
                ->addColumn('price', function ($service) {
                    return $service->price ? '৳' . number_format($service->price, 2) : 'N/A';
                })
                ->addColumn('status_badge', function ($service) {
                    $status = $service->status == 1 ? 'active' : 'inactive';
                    $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('action', function ($service) {
                    return view('admin.settings.services.partials.action-btn-view', ['id' => $service->id])->render();
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.settings.services.index');
    }

    public function create()
    {
        return view('admin.settings.services.partials.create-modal');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191', 'unique:services'],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? 1;

        $insertedId = DB::table('services')->insertGetId([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'status' => $request->status ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = DB::table('services')->where('id', $insertedId)->first();

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully!',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'description' => $service->description
            ]
        ]);
    }

    public function edit($id)
    {
        try {
            $service = Services::findOrFail($id);
            // Return JSON data instead of view
            return response()->json([
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'status' => $service->status,
                'description' => $service->description
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Service not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('services')->ignore($id)
            ],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:0,1'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $service = Services::findOrFail($id);
            $data = $request->all();
            $data['status'] = $request->status ?? $service->status;

            $service->update($data);

            return response()->json(['success' => true, 'message' => 'Service updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update service'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $service = Services::findOrFail($id);
            $service->delete();

            return response()->json(['success' => true, 'message' => 'Service deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete service'], 500);
        }
    }
}