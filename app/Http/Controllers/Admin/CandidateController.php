<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Mail\SendPasswordEmail;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $candidates = Candidate::select('*')->orderBy('id', 'desc');

            return DataTables::of($candidates)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($candidate) {
                    return $candidate->id;
                })
                ->addColumn('status_badge', function ($candidate) {
                    $status = $candidate->status ?? 'active';
                    $badgeClass = $status === 'active' ? 'badge badge-success' : 'badge badge-secondary';
                    return '<span class="' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('created_at_formatted', function ($candidate) {
                    return \Carbon\Carbon::parse($candidate->created_at)->format('M d, Y');
                })
                ->addColumn('action', function ($candidate) {
                    return view('admin.candidates.partials.action-btn-view', ['id' => $candidate->id])->render();
                    // return $btn;
                })
                ->rawColumns(['status_badge', 'created_at_formatted', 'action'])
                ->make(true);
        }

        return view('admin.candidates.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jobPost = DB::table('job_posts')->get();
        return view('admin.candidates.create', compact('jobPost'));
    }

    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'job_id' => 'string|max:50',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle resume file upload - Store in public/resume folder
        $resumePath = null;
        if ($request->hasFile('resume') && $request->file('resume')->isValid()) {
            try {
                $resumeFile = $request->file('resume');

                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $resumeFile->getClientOriginalExtension();

                // Store in public/resume folder
                $resumePath = 'resume/' . $filename;
                $resumeFile->move(public_path('resume'), $filename);
            } catch (\Exception $e) {
                $resumePath = null;
                \Log::error('Resume upload failed: ' . $e->getMessage());
            }
        }

        // Create candidate
        $candidateId = DB::table('candidates')->insertGetId([
            'name' => $request->name,
            'status' => 'Applied',
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'current_company' => $request->current_company,
            'current_position' => $request->current_position,
            'total_experience' => $request->total_experience,
            'current_salary' => $request->current_salary,
            'expected_salary' => $request->expected_salary,
            'linkedin_profile' => $request->linkedin_profile,
            'github_profile' => $request->github_profile,
            'portfolio_website' => $request->portfolio_website,
            'job_id' => $request->job_id,
            'resume_path' => $resumePath,
        ]);

        // $password = Str::random(12);
        // $user = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => Hash::make($password),
        //     'candidate_id' => $candidateId
        // ]);

        // Mail::to($request->email)->send(new SendPasswordEmail(
        //     $request->name,
        //     $request->email,
        //     $password
        // ));

        return redirect()->route('candidates.index')
            ->with('success', 'Successfully registered.');
    }

    public function show($id)
    {
        $candidate = DB::table('candidates')
            ->leftJoin('job_posts', 'job_posts.id', '=', 'candidates.job_id')
            ->where('candidates.id', $id)
            ->select(
                'candidates.*',
                'job_posts.title as job_title'
            )
            ->first();

        return view('admin.candidates.show', compact('candidate'));
    }

    public function edit($id)
    {
        $jobPost = DB::table('job_posts')->get();
        $candidate = Candidate::findOrFail($id);

        return view('admin.candidates.edit', compact('candidate', 'jobPost'));
    }

    public function update(Request $request, $id)
    {
        // Find the candidate
        $candidate = DB::table('candidates')->where('id', $id)->first();

        if (!$candidate) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found.'
                ], 404);
            }
            return redirect()->route('candidates.index')
                ->with('error', 'Candidate not found.');
        }

        // Validation rules - email unique except for current candidate
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('candidates')->ignore($id)
            ],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'job_id' => 'nullable|string|max:50',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
        ]);

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

        // Handle resume file upload - Store in public/resume folder
        $resumePath = $candidate->resume_path; // Keep existing resume by default

        if ($request->hasFile('resume') && $request->file('resume')->isValid()) {
            try {
                $resumeFile = $request->file('resume');

                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $resumeFile->getClientOriginalExtension();

                // Store in public/resume folder
                $resumePath = 'resume/' . $filename;
                $resumeFile->move(public_path('resume'), $filename);

                // Delete old resume file if exists
                if ($candidate->resume_path && file_exists(public_path($candidate->resume_path))) {
                    @unlink(public_path($candidate->resume_path));
                }
            } catch (\Exception $e) {
                \Log::error('Resume upload failed: ' . $e->getMessage());
                // Keep the old resume path if upload fails
            }
        }

        // Update candidate
        DB::table('candidates')->where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'current_company' => $request->current_company,
            'current_position' => $request->current_position,
            'total_experience' => $request->total_experience,
            'current_salary' => $request->current_salary,
            'expected_salary' => $request->expected_salary,
            'linkedin_profile' => $request->linkedin_profile,
            'github_profile' => $request->github_profile,
            'portfolio_website' => $request->portfolio_website,
            'job_id' => $request->job_id,
            'resume_path' => $resumePath,
            'updated_at' => now(), // Add this if you have timestamps
        ]);

        // Handle AJAX response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Candidate updated successfully!',
                'redirect_url' => route('candidates.index')
            ]);
        }

        return redirect()->route('candidates.index')
            ->with('success', 'Candidate updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $candidate = Candidate::findOrFail($id);
            $candidate->delete();

            return response()->json(['success' => true, 'message' => 'Candidate deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting candidate.'], 500);
        }
    }

    public function approve($id)
    {
        try{
            $password = Str::random(12);
            $candidate = Candidate::where('id', $id)->first();
            $user = User::create([
                'name' => $candidate->name,
                'email' => $candidate->email,
                'password' => Hash::make($password),
                'candidate_id' => $id
            ]);

            Mail::to($candidate->email)->send(new SendPasswordEmail(
                $candidate->name,
                $candidate->email,
                $password
            ));

            return response()->json(['success' => true, 'message' => 'Candidate approved successfully.']);

        }catch (\Exception $e) {
                return response()->json(['erroe' => false, 'message' => 'Error deleting candidate.'], 500);
            }


    }
}
