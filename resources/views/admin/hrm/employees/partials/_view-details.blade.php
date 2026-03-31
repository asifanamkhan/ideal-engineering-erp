<div class="container-fluid p-0">
    <!-- Employee Header with Photo and Basic Info - More Compact -->
    <div class="employee-header bg-modal-header  p-3 rounded mb-3">
        <div class="d-flex align-items-center" style="gap: 12px">
            <!-- Photo -->
            <div class="flex-shrink-0 me-3">
                @if($employee->photo && file_exists(base_path($employee->photo)))
                    <img src="{{ asset($employee->photo) }}" alt="{{ $employee->name }}"
                         class="rounded-circle border border-2 border-white" width="70" height="70" style="object-fit: cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white text-primary"
                         style="width: 70px; height: 70px; font-size: 28px; font-weight: bold;">
                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Basic Info -->
            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap mb-1" style="gap: 12px">
                    <h5 class="mb-0  fw-bold">{{ $employee->name }}</h5>
                    <span class="badge bg-primary text-primary rounded-pill px-2">{{ $employee->employee_id ?? 'N/A' }}</span>
                    @if($employee->status == 1)
                        <span class="badge bg-success rounded-pill px-2">Active</span>
                    @else
                        <span class="badge bg-danger rounded-pill px-2">Inactive</span>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-3 small" style="gap: 8px">
                    <span><i class="fas fa-envelope me-1"></i> {{ $employee->email }}</span> |
                    <span><i class="fas fa-phone me-1"></i> {{ $employee->phone ?? 'N/A' }}</span> |
                    <span><i class="fas fa-briefcase me-1"></i> {{ ucfirst($employee->employment_type ?? 'N/A') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards Grid - More Compact and Eye-friendly -->
    <div class="row g-2">
        <!-- Personal Information Card -->
        <div class="col-md-6 mb-4">
            <div class="info-card family">
                <div class="info-card-header">
                    <i class="fas fa-user-circle"></i>
                    <span>Personal Information</span>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value">{{ $employee->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $employee->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value">{{ $employee->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alt Phone</span>
                        <span class="info-value">{{ $employee->phone_two ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date of Birth</span>
                        <span class="info-value">{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Age/Gender</span>
                        <span class="info-value">
                            {{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->age . ' yrs' : '' }}
                            {{ $employee->gender ? '/ ' . ucfirst($employee->gender) : '' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">National ID</span>
                        <span class="info-value">{{ $employee->national_id ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Family Information Card -->
        <div class="col-md-6 mb-4">
            <div class="info-card family">
                <div class="info-card-header">
                    <i class="fas fa-users"></i>
                    <span>Family Information</span>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Father's Name</span>
                        <span class="info-value">{{ $employee->father_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mother's Name</span>
                        <span class="info-value">{{ $employee->mother_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Marital Status</span>
                        <span class="info-value">{{ ucfirst($employee->marital_status ?? 'N/A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Blood Group</span>
                        <span class="info-value">
                            @if($employee->blood_group)
                                <span class="badge bg-danger rounded-pill">{{ $employee->blood_group }}</span>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Religion</span>
                        <span class="info-value">{{ $employee->religion ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information Card -->
        <div class="col-md-6 mb-4">
            <div class="info-card family">
                <div class="info-card-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Address Information</span>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Current Address</span>
                        <span class="info-value">{{ $employee->current_address ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Permanent Address</span>
                        <span class="info-value">{{ $employee->permanent_address ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Information Card -->
        <div class="col-md-6 mb-4">
            <div class="info-card family">
                <div class="info-card-header">
                    <i class="fas fa-briefcase"></i>
                    <span>Professional Information</span>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Employee ID</span>
                        <span class="info-value">{{ $employee->employee_id ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Join Date</span>
                        <span class="info-value">{{ $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('d M, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Employment Type</span>
                        <span class="info-value">
                            @php
                                $types = [
                                    'permanent' => '<span class="badge bg-success rounded-pill">Permanent</span>',
                                    'contract' => '<span class="badge bg-info rounded-pill">Contract</span>',
                                    'probation' => '<span class="badge bg-warning text-dark rounded-pill">Probation</span>',
                                    'intern' => '<span class="badge bg-secondary rounded-pill">Intern</span>',
                                    'part_time' => '<span class="badge bg-primary rounded-pill">Part Time</span>'
                                ];
                            @endphp
                            {!! $types[$employee->employment_type] ?? ucfirst($employee->employment_type ?? 'N/A') !!}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Designation</span>
                        <span class="info-value">
                            <span class="badge bg-info rounded-pill">
                                {{ $employee->designation_name ?? ($employee->designation ?? 'N/A') }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Branch</span>
                        <span class="info-value">
                            <span class="badge bg-primary rounded-pill">
                                {{ $employee->branch_name ?? ($employee->branch ?? 'N/A') }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information Card -->
        <div class="col-md-12 mb-4">
            <div class="info-card family">
                <div class="info-card-header">
                    <i class="fas fa-clock"></i>
                    <span>System Information</span>
                </div>
                <div class="info-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Created At</span>
                                <span class="info-value">{{ $employee->created_at ? \Carbon\Carbon::parse($employee->created_at)->format('d M, Y h:i A') : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Last Updated</span>
                                <span class="info-value">{{ $employee->updated_at ? \Carbon\Carbon::parse($employee->updated_at)->format('d M, Y h:i A') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-modal-header {
        background: #F7F8FB;
    }

    .info-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 10px;
        border: 1px solid #e9ecef;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .info-card-header {
        padding: 10px 15px;
        font-weight: 600;
        font-size: 0.95rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-card-header i {
        font-size: 1rem;
    }

    .info-card-body {
        padding: 12px 15px;
    }

    .info-row {
        display: flex;
        margin-bottom: 8px;
        font-size: 0.9rem;
        border-bottom: 1px dashed #f0f0f0;
        padding-bottom: 6px;
    }

    .info-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-label {
        width: 120px;
        color: #6c757d;
        font-weight: 500;
    }

    .info-value {
        flex: 1;
        color: #2c3e50;
        font-weight: 500;
    }


    .info-card.family .info-card-header {
        color: black;
        border-left: 3px solid #1cc88a;
    }

    /* Badge styles */
    .badge {
        font-weight: 500;
        padding: 4px 8px;
        font-size: 0.8rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .info-row {
            flex-direction: column;
        }
        .info-label {
            width: 100%;
            margin-bottom: 2px;
        }
    }
</style>
