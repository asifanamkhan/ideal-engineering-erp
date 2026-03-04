@extends('layouts.dashboard.app')

@section('css')
@include('admin.job-posts.partials.css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="job-profile">
                <!-- Header Section -->
                <div class="profile-header">
                    <div class="header-left">
                        <div class="job-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>

                    <div class="header-center">
                        <h1 class="job-title">{{ $jobPost->title }}</h1>
                        <div class="job-meta">
                            <span class="me-3"><i class="fas fa-building me-1"></i> {{ $jobPost->department_name ?? 'Not specified' }}</span>
                            <span class="me-3"><i class="fas fa-map-marker-alt me-1"></i> {{ $jobPost->is_remote ? 'Remote' : ($jobPost->location ?? 'Location not specified') }}</span>
                            <span><i class="fas fa-clock me-1"></i> {{ ucfirst(str_replace('-', ' ', $jobPost->position_type)) }}</span>
                        </div>

                        <div class="badge-container">
                            <span class="badge
                                @if($jobPost->status == 'published') badge-success
                                @elseif($jobPost->status == 'closed') badge-danger
                                @else badge-warning @endif">
                                {{ ucfirst($jobPost->status) }}
                            </span>
                            <span class="badge vacancies-badge">{{ $jobPost->vacancies }} Open Positions</span>
                            @if($jobPost->experience_level)
                            <span class="badge badge-primary">{{ ucfirst($jobPost->experience_level) }} Level</span>
                            @endif
                        </div>
                    </div>

                    <div class="header-right">
                        <div class="status-badge">
                            <i class="fas fa-circle" style="font-size: 10px;"></i>
                            {{ ucfirst($jobPost->status) }}
                        </div>
                    </div>
                </div>

                <!-- Body Section -->
                <div class="profile-body">
                    <div class="row">
                        <!-- Left Column - Job Details -->
                        <div class="col-md-4">
                            @if($jobPost->application_deadline)
                            <div class="deadline-card">
                                @php
                                    $deadline = \Carbon\Carbon::parse($jobPost->application_deadline);
                                    $now = now();

                                    if ($now->gt($deadline)) {
                                        $timeLeft = 'Expired';
                                    } else {
                                        $diff = $now->diff($deadline);
                                        $daysLeft = $diff->d;
                                        $hoursLeft = $diff->h;
                                        $minutesLeft = $diff->i;

                                        if ($daysLeft > 0) {
                                            $timeLeft = "{$daysLeft}d {$hoursLeft}h {$minutesLeft}m";
                                        } else {
                                            $timeLeft = "{$hoursLeft}h {$minutesLeft}m";
                                        }
                                    }
                                @endphp

                                @if($timeLeft === 'Expired')
                                    <div class="deadline-time">Application Closed</div>
                                @else
                                    <div class="deadline-time">{{ $timeLeft }}</div>
                                    <div class="deadline-label">Time Left to Apply</div>
                                @endif
                                <small>Deadline: {{ $deadline->format('M d, Y') }}</small>
                            </div>
                            @endif

                            <h3 class="section-title">Job Details</h3>

                            <div class="info-item">
                                <div class="info-label">Position Type</div>
                                <div class="info-value">{{ ucfirst(str_replace('-', ' ', $jobPost->position_type)) }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Experience Level</div>
                                <div class="info-value">
                                    @if($jobPost->experience_level)
                                        {{ ucfirst($jobPost->experience_level) }} Level
                                    @else
                                        Not specified
                                    @endif
                                </div>
                            </div>

                            @if($jobPost->salary_range_min || $jobPost->salary_range_max)
                            <div class="info-item">
                                <div class="info-label">Salary Range</div>
                                <div class="info-value">
                                    @if($jobPost->salary_range_min && $jobPost->salary_range_max)
                                        ${{ number_format($jobPost->salary_range_min) }} - ${{ number_format($jobPost->salary_range_max) }}
                                    @elseif($jobPost->salary_range_min)
                                        From ${{ number_format($jobPost->salary_range_min) }}
                                    @elseif($jobPost->salary_range_max)
                                        Up to ${{ number_format($jobPost->salary_range_max) }}
                                    @else
                                        Negotiable
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div class="info-item">
                                <div class="info-label">Location</div>
                                <div class="info-value">
                                    @if($jobPost->is_remote)
                                        <span class="badge badge-success">Remote Position</span>
                                    @else
                                        {{ $jobPost->location ?? 'Not specified' }}
                                    @endif
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Department</div>
                                <div class="info-value">{{ $jobPost->department_name ?? 'Not specified' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Vacancies</div>
                                <div class="info-value">{{ $jobPost->vacancies }} Position(s)</div>
                            </div>

                            <h3 class="section-title mt-4">Post Information</h3>

                            <div class="info-item">
                                <div class="info-label">Created</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($jobPost->created_at)->format('M d, Y') }}<br>
                                    <small class="text-muted">by {{ $jobPost->created_by_name ?? 'System' }}</small>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Last Updated</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($jobPost->updated_at)->format('M d, Y') }}<br>
                                    <small class="text-muted">by {{ $jobPost->updated_by_name ?? 'System' }}</small>
                                </div>
                            </div>

                            @if($jobPost->published_at)
                            <div class="info-item">
                                <div class="info-label">Published</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($jobPost->published_at)->format('M d, Y') }}
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Right Column - Description & Details -->
                        <div class="col-md-8">
                            <h3 class="section-title">Job Description</h3>
                            <div class="description-section">
                                <div class="description-content">
                                    {!! nl2br(e($jobPost->description)) !!}
                                </div>
                            </div>

                            @if($jobPost->requirements)
                            <h3 class="section-title">Requirements</h3>
                            <div class="description-section">
                                <div class="description-content">
                                    {!! nl2br(e($jobPost->requirements)) !!}
                                </div>
                            </div>
                            @endif

                            @if($jobPost->responsibilities)
                            <h3 class="section-title">Responsibilities</h3>
                            <div class="description-section">
                                <div class="description-content">
                                    {!! nl2br(e($jobPost->responsibilities)) !!}
                                </div>
                            </div>
                            @endif

                            @if($jobPost->exam_id || $jobPost->exam_duration || $jobPost->passing_score)
                            <h3 class="section-title">Exam Configuration</h3>
                            <div class="exam-badges">
                                @if($jobPost->exam_id)
                                <span class="exam-badge">
                                    <i class="fas fa-id-card"></i> Exam ID: #{{ $jobPost->exam_id }}
                                </span>
                                @endif
                                @if($jobPost->exam_duration)
                                <span class="exam-badge">
                                    <i class="fas fa-clock"></i> Duration: {{ $jobPost->exam_duration }}min
                                </span>
                                @endif
                                @if($jobPost->passing_score)
                                <span class="exam-badge">
                                    <i class="fas fa-percentage"></i> Passing: {{ $jobPost->passing_score }}%
                                </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $('#job-sidebar').addClass('active');
    $('#job-index-sidebar').addClass('active');
    $('#collapseJob').addClass('show');
    // Add hover effects to info items
    document.addEventListener('DOMContentLoaded', function() {
        const infoItems = document.querySelectorAll('.info-item');
        infoItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    });
</script>
@endsection
