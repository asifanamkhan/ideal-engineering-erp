@extends('layouts.dashboard.app')

@section('css')
@include('admin.candidates.partials.css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="candidate-profile">
                <!-- Header Section -->
                <div class="profile-header">
                    <div class="header-left">
                        <div class="profile-avatar">
                            {{ substr($candidate->name, 0, 1) }}
                        </div>
                    </div>

                    <div class="header-center">
                        <h1 class="profile-name">{{ $candidate->name }}</h1>
                        <div class="profile-position">{{ $candidate->job_title ?? 'Position Not Specified' }}</div>

                        <div class="badge-container">
                            <span class="badge badge-success">{{ $candidate->status }}</span>
                            @if($candidate->total_experience)
                            <span class="badge experience-badge">{{ $candidate->total_experience }} Exp</span>
                            @endif
                            @if($candidate->current_company)
                            <span class="badge badge-warning">{{ $candidate->current_company }}</span>
                            @endif
                            @if($candidate->expected_salary)
                            <span class="badge salary-badge">Expected: {{ $candidate->expected_salary }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="header-right">
                        @if($candidate->resume_path)
                        <a target="_blank" href="{{ asset('public/' . $candidate->resume_path) }}" class="btn-view-resume" >
                            <i class="fas fa-file-pdf"></i> View Resume
                        </a>
                        @else
                        <button class="btn-view-resume" disabled>
                            <i class="fas fa-file-pdf"></i> No Resume
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Body Section -->
                <div class="profile-body">
                    <div class="row">
                        <!-- Left Column - Personal Info -->
                        <div class="col-md-6">
                            <h3 class="section-title">Personal Information</h3>

                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $candidate->email }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value">{{ $candidate->phone ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Gender</div>
                                <div class="info-value">{{ $candidate->gender ?? 'Not specified' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Date of Birth</div>
                                <div class="info-value">
                                    {{ $candidate->date_of_birth ? \Carbon\Carbon::parse($candidate->date_of_birth)->format('M d, Y') : 'Not provided' }}
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">National ID</div>
                                <div class="info-value">{{ $candidate->national_id ?? 'Not provided' }}</div>
                            </div>

                            <h3 class="section-title mt-4">Location</h3>

                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value">{{ $candidate->address ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">City</div>
                                <div class="info-value">{{ $candidate->city ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">State</div>
                                <div class="info-value">{{ $candidate->state ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Country</div>
                                <div class="info-value">{{ $candidate->country ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Postal Code</div>
                                <div class="info-value">{{ $candidate->postal_code ?? 'Not provided' }}</div>
                            </div>
                        </div>

                        <!-- Right Column - Professional Info -->
                        <div class="col-md-6">
                            <h3 class="section-title">Professional Information</h3>

                            <div class="info-item">
                                <div class="info-label">Current Company</div>
                                <div class="info-value">{{ $candidate->current_company ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Current Position</div>
                                <div class="info-value">{{ $candidate->current_position ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Total Experience</div>
                                <div class="info-value">{{ $candidate->total_experience ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Current Salary</div>
                                <div class="info-value">{{ $candidate->current_salary ?? 'Not provided' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Expected Salary</div>
                                <div class="info-value">{{ $candidate->expected_salary ?? 'Not provided' }}</div>
                            </div>

                            <h3 class="section-title mt-4">Online Profiles</h3>

                            <div class="social-links">
                                @if($candidate->linkedin_profile)
                                <a href="{{ $candidate->linkedin_profile }}" target="_blank" class="social-link">
                                    <i class="fab fa-linkedin"></i> LinkedIn
                                </a>
                                @endif

                                @if($candidate->github_profile)
                                <a href="{{ $candidate->github_profile }}" target="_blank" class="social-link">
                                    <i class="fab fa-github"></i> GitHub
                                </a>
                                @endif

                                @if($candidate->portfolio_website)
                                <a href="{{ $candidate->portfolio_website }}" target="_blank" class="social-link">
                                    <i class="fas fa-globe"></i> Portfolio
                                </a>
                                @endif

                                @if(!$candidate->linkedin_profile && !$candidate->github_profile && !$candidate->portfolio_website)
                                <span>No online profiles provided</span>
                                @endif
                            </div>

                            <h3 class="section-title mt-4">Application Details</h3>

                            <div class="info-item">
                                <div class="info-label">Job ID</div>
                                <div class="info-value">{{ $candidate->job_id ?? 'Not specified' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Application Date</div>
                                <div class="info-value">
                                    {{ $candidate->created_at ? \Carbon\Carbon::parse($candidate->created_at)->format('M d, Y') : 'Not available' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')


<!-- Font Awesome for icons -->
<script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
<script>
     $('#candidate-sidebar').addClass('active');
     $('#candidate-index-sidebar').addClass('active');
     $('#collapseCandidate').addClass('show');
</script>
@endsection
