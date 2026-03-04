@extends('layouts.dashboard.app')
@section('css')
<style>
    .card {
        border: 1px solid #ccc;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .card-header {
        background: linear-gradient(135deg, #28ACE2 0%, #1E88E5 100%);
        color: #fff;
        padding: 10px;
        border-radius: 8px 8px 0 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Candidate</h1>
        <div>
            <a target="_blank" href="{{ route('candidate.register') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Public Link
            </a>
            <a href="{{ route('candidates.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-list fa-sm text-white-50"></i> Candidate List
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Display Validation Errors -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Display Success Message -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Display Error Message -->
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('candidates.store') }}" method="POST" enctype="multipart/form-data" id="candidateForm">
                @csrf

                <!-- Personal Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" required value="{{ old('name') }}"
                                       placeholder="Enter full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" required value="{{ old('email') }}"
                                       placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-control @error('gender') is-invalid @enderror"
                                        id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="national_id" class="form-label">NID / Birth Certificate / Passport</label>
                                <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                       id="national_id" name="national_id" value="{{ old('national_id') }}"
                                       placeholder="Enter ID number">
                                @error('national_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Address Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       id="address" name="address" value="{{ old('address') }}"
                                       placeholder="Enter address">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city" value="{{ old('city') }}"
                                       placeholder="Enter city">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror"
                                       id="state" name="state" value="{{ old('state') }}"
                                       placeholder="Enter state">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror"
                                       id="country" name="country" value="{{ old('country') }}"
                                       placeholder="Enter country">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                       id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                                       placeholder="Enter postal code">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Professional Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="current_company" class="form-label">Current Company</label>
                                <input type="text" class="form-control @error('current_company') is-invalid @enderror"
                                       id="current_company" name="current_company" value="{{ old('current_company') }}"
                                       placeholder="Enter current company">
                                @error('current_company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="current_position" class="form-label">Current Position</label>
                                <input type="text" class="form-control @error('current_position') is-invalid @enderror"
                                       id="current_position" name="current_position" value="{{ old('current_position') }}"
                                       placeholder="Enter current position">
                                @error('current_position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="total_experience" class="form-label">Total Experience (Years)</label>
                                <input type="number" step="0.1" class="form-control @error('total_experience') is-invalid @enderror"
                                       id="total_experience" name="total_experience" value="{{ old('total_experience') }}"
                                       placeholder="e.g., 3.5" min="0">
                                @error('total_experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="current_salary" class="form-label">Current Salary</label>
                                <input type="text" class="form-control @error('current_salary') is-invalid @enderror"
                                       id="current_salary" name="current_salary" value="{{ old('current_salary') }}"
                                       placeholder="Enter current salary">
                                @error('current_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expected_salary" class="form-label">Expected Salary</label>
                                <input type="text" class="form-control @error('expected_salary') is-invalid @enderror"
                                       id="expected_salary" name="expected_salary" value="{{ old('expected_salary') }}"
                                       placeholder="Enter expected salary">
                                @error('expected_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Online Profiles Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Online Profiles</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="linkedin_profile" class="form-label">LinkedIn Profile</label>
                                <input type="url" class="form-control @error('linkedin_profile') is-invalid @enderror"
                                       id="linkedin_profile" name="linkedin_profile" value="{{ old('linkedin_profile') }}"
                                       placeholder="https://linkedin.com/in/username">
                                @error('linkedin_profile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="github_profile" class="form-label">GitHub Profile</label>
                                <input type="url" class="form-control @error('github_profile') is-invalid @enderror"
                                       id="github_profile" name="github_profile" value="{{ old('github_profile') }}"
                                       placeholder="https://github.com/username">
                                @error('github_profile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="portfolio_website" class="form-label">Portfolio Website</label>
                                <input type="url" class="form-control @error('portfolio_website') is-invalid @enderror"
                                       id="portfolio_website" name="portfolio_website" value="{{ old('portfolio_website') }}"
                                       placeholder="https://yourportfolio.com">
                                @error('portfolio_website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Details Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Application Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="job_id" class="form-label">Job Position Applied For</label>
                                <select name="job_id" id="" class="form-control">
                                    <option value="">Select Job Position</option>
                                    @foreach ($jobPost as $job)
                                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                                    @endforeach
                                </select>
                                @error('job_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="resume" class="form-label">Upload Resume (PDF/DOC)</label>
                                <input type="file" class="form-control @error('resume') is-invalid @enderror"
                                       id="resume" name="resume" accept=".pdf,.doc,.docx">
                                @error('resume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Max file size: 5MB</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-center" style="gap: 10px">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>Register Candidate
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i>Reset Form
                            </button>
                            <a href="{{ route('candidates.index') }}" class="btn btn-outline-danger">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#candidate-sidebar').addClass('active');
    $('#candidate-create-sidebar').addClass('active');
    $('#collapseCandidate').addClass('show');

    $(document).ready(function() {
        // Form submission with SweetAlert confirmation
        $('#candidateForm').submit(function(e) {
            e.preventDefault();

            // Basic validation
            const name = $('#name').val().trim();
            const email = $('#email').val().trim();

            if (!name || !email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields (Name and Email)',
                    confirmButtonColor: '#28ACE2'
                });
                return;
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'Register Candidate?',
                text: 'Are you sure you want to register this candidate?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28ACE2',
                cancelButtonColor: '#e53e3e',
                confirmButtonText: 'Yes, Register!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const submitBtn = $('button[type="submit"]');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Registering...');
                    submitBtn.prop('disabled', true);

                    // Submit the form
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
