<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SendPasswordEmail;


class CandidateRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        // $jobs = DB::table('')
        return view('auth.candidate-register'); // Make sure this matches your blade file name
    }

    public function register(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string','email', 'max:255', 'unique:'.User::class],
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

        return redirect()->route('candidate-registration-complete')
                ->with('success', 'Successfully registered! Please wait for admin approval.');
    }

    public function candidate_registration_complete(){
        return view ('auth.complete-register');
    }
}
