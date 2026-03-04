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
        <h1 class="h3 mb-0 text-gray-800">Add new Job Post</h1>
        <a href="{{ route('job-posts.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list fa-sm text-white-50"></i> Job List
        </a>
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

            <form action="{{ route('job-posts.store') }}" method="POST" id="jobPostForm">
                @csrf

                <!-- Basic Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" required value="{{ old('title') }}"
                                       placeholder="e.g., Senior Laravel Developer">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enter a clear and descriptive job title</div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="5" required
                                          placeholder="Describe the job role, expectations, and company culture...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="requirements" class="form-label">Requirements</label>
                                <textarea class="form-control @error('requirements') is-invalid @enderror"
                                          id="requirements" name="requirements" rows="4"
                                          placeholder="List the required skills, qualifications, and experience...">{{ old('requirements') }}</textarea>
                                @error('requirements')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="responsibilities" class="form-label">Responsibilities</label>
                                <textarea class="form-control @error('responsibilities') is-invalid @enderror"
                                          id="responsibilities" name="responsibilities" rows="4"
                                          placeholder="Describe the key responsibilities and duties...">{{ old('responsibilities') }}</textarea>
                                @error('responsibilities')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Job Details Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Job Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select required name="department" id="" class="form-control">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="position_type" class="form-label">Position Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('position_type') is-invalid @enderror"
                                        id="position_type" name="position_type">
                                    <option value="full-time" {{ old('position_type') == 'full-time' ? 'selected' : '' }}>Full Time</option>
                                    <option value="part-time" {{ old('position_type') == 'part-time' ? 'selected' : '' }}>Part Time</option>
                                    <option value="contract" {{ old('position_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="remote" {{ old('position_type') == 'remote' ? 'selected' : '' }}>Remote</option>
                                </select>
                                @error('position_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="experience_level" class="form-label">Experience Level</label>
                                <select class="form-control @error('experience_level') is-invalid @enderror"
                                        id="experience_level" name="experience_level">
                                    <option value="">Select Level</option>
                                    <option value="entry" {{ old('experience_level') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                                    <option value="mid" {{ old('experience_level') == 'mid' ? 'selected' : '' }}>Mid Level</option>
                                    <option value="senior" {{ old('experience_level') == 'senior' ? 'selected' : '' }}>Senior Level</option>
                                    <option value="executive" {{ old('experience_level') == 'executive' ? 'selected' : '' }}>Executive</option>
                                </select>
                                @error('experience_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="salary_range_min" class="form-label">Salary Range (Min)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('salary_range_min') is-invalid @enderror"
                                           id="salary_range_min" name="salary_range_min" value="{{ old('salary_range_min') }}"
                                           placeholder="Minimum salary" step="0.01" min="0">
                                </div>
                                @error('salary_range_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="salary_range_max" class="form-label">Salary Range (Max)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('salary_range_max') is-invalid @enderror"
                                           id="salary_range_max" name="salary_range_max" value="{{ old('salary_range_max') }}"
                                           placeholder="Maximum salary" step="0.01" min="0">
                                </div>
                                @error('salary_range_max')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location" value="{{ old('location') }}"
                                       placeholder="e.g., New York, NY or Remote">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">Remote Work</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote" value="1"
                                           {{ old('is_remote') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_remote">
                                        This is a remote position
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Process Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Application Process</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="application_deadline" class="form-label">Application Deadline</label>
                                <input type="date" class="form-control @error('application_deadline') is-invalid @enderror"
                                       id="application_deadline" name="application_deadline" value="{{ old('application_deadline') }}">
                                @error('application_deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty for no deadline</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vacancies" class="form-label">Number of Vacancies <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('vacancies') is-invalid @enderror"
                                       id="vacancies" name="vacancies" value="{{ old('vacancies', 1) }}" min="1">
                                @error('vacancies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Configuration Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Exam Configuration</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="exam_id" class="form-label">Exam ID</label>
                                <input type="number" class="form-control @error('exam_id') is-invalid @enderror"
                                       id="exam_id" name="exam_id" value="{{ old('exam_id') }}"
                                       placeholder="Enter exam ID">
                                @error('exam_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="exam_duration" class="form-label">Exam Duration (minutes)</label>
                                <input type="number" class="form-control @error('exam_duration') is-invalid @enderror"
                                       id="exam_duration" name="exam_duration" value="{{ old('exam_duration') }}"
                                       placeholder="Duration in minutes">
                                @error('exam_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="passing_score" class="form-label">Passing Score (%)</label>
                                <input type="number" class="form-control @error('passing_score') is-invalid @enderror"
                                       id="passing_score" name="passing_score" value="{{ old('passing_score') }}"
                                       placeholder="Percentage" step="0.01" min="0" max="100">
                                @error('passing_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Start with "Draft" to save without publishing</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-center" style="gap: 10px">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Job Post
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i>Reset Form
                            </button>
                            <a href="{{ route('job-posts.index') }}" class="btn btn-outline-danger">
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

    $('#job-sidebar').addClass('active');
    $('#job-create-sidebar').addClass('active');
    $('#collapseJob').addClass('show');

    $(document).ready(function() {
        // Remote work checkbox logic
        $('#is_remote').change(function() {
            if ($(this).is(':checked')) {
                $('#location').val('Remote').prop('readonly', true);
            } else {
                $('#location').val('').prop('readonly', false);
            }
        });

        // Initialize remote checkbox state on page load
        if ($('#is_remote').is(':checked')) {
            $('#location').val('Remote').prop('readonly', true);
        }

        // Form submission with SweetAlert confirmation
        $('#jobPostForm').submit(function(e) {
            e.preventDefault();

            // Basic validation
            const title = $('#title').val().trim();
            const description = $('#description').val().trim();

            if (!title || !description) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields (Job Title and Description)',
                    confirmButtonColor: '#28ACE2'
                });
                return;
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'Create Job Post?',
                text: 'Are you sure you want to create this job post?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28ACE2',
                cancelButtonColor: '#e53e3e',
                confirmButtonText: 'Yes, Create It!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const submitBtn = $('button[type="submit"]');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Creating...');
                    submitBtn.prop('disabled', true);

                    // Submit the form via AJAX
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#28ACE2',
                                    timer: 2000,
                                    timerProgressBar: true
                                }).then(() => {
                                    // Redirect to index page
                                    window.location.href = response.redirect_url;
                                });
                            }
                        },
                        error: function(xhr) {
                            // Reset button state
                            submitBtn.html(originalText);
                            submitBtn.prop('disabled', false);

                            if (xhr.status === 422) {
                                // Validation errors
                                const errors = xhr.responseJSON.errors;
                                let errorMessage = 'Please fix the following errors:\n';

                                for (const field in errors) {
                                    errorMessage += `• ${errors[field][0]}\n`;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    text: errorMessage,
                                    confirmButtonColor: '#28ACE2'
                                });
                            } else {
                                // Other errors
                                let errorMessage = 'An error occurred while creating the job post. Please try again.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage,
                                    confirmButtonColor: '#28ACE2'
                                });
                            }
                        }
                    });
                }
            });
        });

        // Form reset handler
        $('button[type="reset"]').click(function() {
            // Reset remote checkbox effect
            if ($('#is_remote').is(':checked')) {
                $('#location').val('Remote').prop('readonly', true);
            } else {
                $('#location').prop('readonly', false);
            }
        });
    });
</script>
@endsection
