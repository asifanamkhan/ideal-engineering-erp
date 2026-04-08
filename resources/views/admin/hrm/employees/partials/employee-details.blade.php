<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white py-2">
        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i> Employee Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center">
                @if($employee->photo && file_exists(base_path($employee->photo)))
                    <img src="{{ asset($employee->photo) }}" class="employee-avatar" alt="{{ $employee->name }}">
                @else
                    <div class="employee-avatar bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="font-size: 40px;">
                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                    </div>
                @endif
                <div class="mt-3">
                    <span class="badge-employee">
                        <i class="fas fa-id-card"></i> {{ $employee->employee_id }}
                    </span>
                </div>
            </div>
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">{{ $employee->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="info-label">Designation</div>
                            <div class="info-value">{{ $employee->designation_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="info-label">Email Address</div>
                            <div class="info-value">{{ $employee->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">{{ $employee->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="info-label">Join Date</div>
                            <div class="info-value">{{ $employee->join_date ? date('d M, Y', strtotime($employee->join_date)) : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="info-label">Branch</div>
                            <div class="info-value">{{ $employee->branch_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="info-label">Type</div>
                            <div class="info-value">{{ $employee->employment_type ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="info-card">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $employee->address ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>