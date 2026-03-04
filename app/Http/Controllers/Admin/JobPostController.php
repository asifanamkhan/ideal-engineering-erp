<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class JobPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('job_posts')
                ->select([
                    'id',
                    'title',
                    'department',
                    'position_type',
                    'experience_level',
                    'vacancies',
                    'status',
                    'application_deadline',
                    'created_at'
                ])
                ->whereNull('deleted_at') // Exclude soft deleted records
                ->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex gap-1 justify-content-center">';
                    $btn .= '<a href="' . route('job-posts.show', $row->id) . '" class="btn btn-info btn-sm px-2" title="View Details">';
                    $btn .= '<i class="fas fa-eye"></i>';
                    $btn .= '</a>';

                    $btn .= '<a href="' . route('job-posts.edit', $row->id) . '" class="btn btn-warning btn-sm px-2" title="Edit">';
                    $btn .= '<i class="fas fa-edit"></i>';
                    $btn .= '</a>';

                    $btn .= '<button type="button" class="btn btn-danger btn-sm px-2 delete-btn" data-id="' . $row->id . '" title="Delete">';
                    $btn .= '<i class="fas fa-trash"></i>';
                    $btn .= '</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('status_badge', function ($row) {
                    $statusClass = [
                        'draft' => 'secondary',
                        'published' => 'success',
                        'closed' => 'danger'
                    ];

                    return '<span style="color: #fff" class="badge bg-' . $statusClass[$row->status] . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('application_deadline_formatted', function ($row) {
                    return $row->application_deadline
                        ? \Carbon\Carbon::parse($row->application_deadline)->format('M d, Y')
                        : '<span class="text-muted">No deadline</span>';
                })
                ->addColumn('created_at_formatted', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('M d, Y');
                })
                ->rawColumns(['action', 'status_badge', 'application_deadline_formatted'])
                ->make(true);
        }

        return view('admin.job-posts.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::get();
        return view('admin.job-posts.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules - updated to match all form fields
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'department' => 'nullable|string|max:100',
            'position_type' => 'required|in:full-time,part-time,contract,remote',
            'experience_level' => 'nullable|in:entry,mid,senior,executive',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'application_deadline' => 'nullable|date|after_or_equal:today',
            'vacancies' => 'required|integer|min:1',
            'is_remote' => 'sometimes|boolean',
            'status' => 'required|in:draft,published,closed',
            'exam_id' => 'nullable|integer',
            'exam_duration' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|numeric|between:0,100'
        ], [
            'title.required' => 'Job title is required',
            'description.required' => 'Job description is required',
            'position_type.required' => 'Position type is required',
            'position_type.in' => 'Invalid position type selected',
            'application_deadline.after_or_equal' => 'Application deadline must be today or a future date',
            'vacancies.required' => 'Number of vacancies is required',
            'vacancies.min' => 'Number of vacancies must be at least 1',
            'status.required' => 'Status is required'
        ]);

        // Custom validation for salary range
        $validator->after(function ($validator) use ($request) {
            if ($request->salary_range_min && $request->salary_range_max) {
                if ($request->salary_range_max < $request->salary_range_min) {
                    $validator->errors()->add(
                        'salary_range_max',
                        'Maximum salary must be greater than or equal to minimum salary'
                    );
                }
            }
        });

        // If validation fails
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Start database transaction
            DB::beginTransaction();

            // Prepare data for insertion
            $jobPostData = [
                'title' => $request->title,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'responsibilities' => $request->responsibilities,
                'department' => $request->department,
                'position_type' => $request->position_type,
                'experience_level' => $request->experience_level,
                'salary_range_min' => $request->salary_range_min,
                'salary_range_max' => $request->salary_range_max,
                'location' => $request->location,
                'application_deadline' => $request->application_deadline,
                'vacancies' => $request->vacancies,
                'is_remote' => $request->has('is_remote') ? 1 : 0, // Fixed boolean conversion
                'status' => $request->status,
                'exam_id' => $request->exam_id,
                'exam_duration' => $request->exam_duration,
                'passing_score' => $request->passing_score,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Set published_at if status is published
            if ($request->status === 'published') {
                $jobPostData['published_at'] = now();
            }

            // Insert into database using Query Builder
            $jobPostId = DB::table('job_posts')->insertGetId($jobPostData);

            // Commit transaction
            DB::commit();

            // Return response based on request type
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Job post created successfully!',
                    'redirect_url' => route('job-posts.index')
                ]);
            }

            // Redirect with success message for non-AJAX requests
            return redirect()->route('job-posts.index')
                ->with('success', 'Job post created successfully!');
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Return error response based on request type
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating job post: ' . $e->getMessage()
                ], 500);
            }

            // Redirect with error message for non-AJAX requests
            return redirect()->back()
                ->with('error', 'Error creating job post: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $jobPost = DB::table('job_posts')
                ->leftJoin('users as creator', 'job_posts.created_by', '=', 'creator.id')
                ->leftJoin('users as updater', 'job_posts.updated_by', '=', 'updater.id')
                ->leftJoin('departments', 'job_posts.department', '=', 'departments.id')
                ->select(
                    'job_posts.*',
                    'departments.name as department_name',
                    'creator.name as created_by_name',
                    'updater.name as updated_by_name'
                )
                ->where('job_posts.id', $id)
                ->first();

            if (!$jobPost) {
                return redirect()->route('job-posts.index')
                    ->with('error', 'Job post not found.');
            }

            return view('admin.job-posts.show', compact('jobPost'));
        } catch (\Exception $e) {
            return redirect()->route('job-posts.index')
                ->with('error', 'Error loading job post: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $jobPost = DB::table('job_posts')->where('id', $id)->first();
            $departments = Department::get();

            if (!$jobPost) {
                return redirect()->route('job-posts.index')
                    ->with('error', 'Job post not found.');
            }

            return view('admin.job-posts.edit', compact('jobPost','departments'));
        } catch (\Exception $e) {
            return redirect()->route('job-posts.index')
                ->with('error', 'Error loading job post: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validation rules (same as store with sometimes for nullable fields)
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'department' => 'nullable|string|max:100',
            'position_type' => 'required|in:full-time,part-time,contract,remote',
            'experience_level' => 'nullable|in:entry,mid,senior,executive',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'application_deadline' => 'nullable|date|after_or_equal:today',
            'vacancies' => 'required|integer|min:1',
            'is_remote' => 'sometimes|boolean',
            'status' => 'required|in:draft,published,closed',
            'exam_id' => 'nullable|integer',
            'exam_duration' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|numeric|between:0,100'
        ], [
            'title.required' => 'Job title is required',
            'description.required' => 'Job description is required',
            'position_type.required' => 'Position type is required',
            'position_type.in' => 'Invalid position type selected',
            'application_deadline.after_or_equal' => 'Application deadline must be today or a future date',
            'vacancies.required' => 'Number of vacancies is required',
            'vacancies.min' => 'Number of vacancies must be at least 1',
            'status.required' => 'Status is required'
        ]);

        // Custom validation for salary range
        $validator->after(function ($validator) use ($request) {
            if ($request->salary_range_min && $request->salary_range_max) {
                if ($request->salary_range_max < $request->salary_range_min) {
                    $validator->errors()->add(
                        'salary_range_max',
                        'Maximum salary must be greater than or equal to minimum salary'
                    );
                }
            }
        });

        // If validation fails
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Start database transaction
            DB::beginTransaction();

            // Check if job post exists
            $existingJobPost = DB::table('job_posts')->where('id', $id)->first();
            if (!$existingJobPost) {
                return redirect()->route('job-posts.index')
                    ->with('error', 'Job post not found.');
            }

            // Prepare data for update
            $jobPostData = [
                'title' => $request->title,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'responsibilities' => $request->responsibilities,
                'department' => $request->department,
                'position_type' => $request->position_type,
                'experience_level' => $request->experience_level,
                'salary_range_min' => $request->salary_range_min,
                'salary_range_max' => $request->salary_range_max,
                'location' => $request->location,
                'application_deadline' => $request->application_deadline,
                'vacancies' => $request->vacancies,
                'is_remote' => $request->has('is_remote') ? 1 : 0,
                'status' => $request->status,
                'exam_id' => $request->exam_id,
                'exam_duration' => $request->exam_duration,
                'passing_score' => $request->passing_score,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            // Set published_at if status changed to published
            if ($request->status === 'published' && $existingJobPost->status !== 'published') {
                $jobPostData['published_at'] = now();
            }

            // Update the record
            DB::table('job_posts')->where('id', $id)->update($jobPostData);

            // Commit transaction
            DB::commit();

            // Return response based on request type
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Job post updated successfully!',
                    'redirect_url' => route('job-posts.index')
                ]);
            }

            // Redirect with success message for non-AJAX requests
            return redirect()->route('job-posts.index')
                ->with('success', 'Job post updated successfully!');
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Return error response based on request type
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating job post: ' . $e->getMessage()
                ], 500);
            }

            // Redirect with error message for non-AJAX requests
            return redirect()->back()
                ->with('error', 'Error updating job post: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::table('job_posts')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job post deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting job post: ' . $e->getMessage()
            ], 500);
        }
    }
}