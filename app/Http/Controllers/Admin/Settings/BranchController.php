<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = DB::table('branches')->orderBy('id', 'desc')->get();
        return view('admin.settings.branch.index', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        DB::table('branches')->insert([
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Branch created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        DB::table('branches')->where('id', $id)->update([
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::table('branches')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Branch deleted successfully.');
    }
}
