@extends('layouts.dashboard.app')

@section('css')
<style>

</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><i class="fas fa-user-plus me-2"></i>Add New Employee</h4>
            </div>
            <div>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Display Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data" id="employeeForm">
        @csrf

        <!-- Main Card -->
        <div class="card shadow">
            <div class="card-body">

                <!-- Personal Information -->
                <h5 class="section-title">
                    <i class="fas fa-user-circle"></i>Personal Information
                </h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i>Full Name <span class="required-star">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" placeholder="Enter full name">
                        @error('name')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-envelope"></i>Email <span class="required-star">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" placeholder="Enter email address">
                        @error('email')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-phone"></i>Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               name="phone" value="{{ old('phone') }}" placeholder="Enter primary phone">
                        @error('phone')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-phone-alt"></i>Phone (Secondary)</label>
                        <input type="text" class="form-control @error('phone_two') is-invalid @enderror"
                               name="phone_two" value="{{ old('phone_two') }}" placeholder="Enter secondary phone">
                        @error('phone_two')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-calendar"></i>Date of Birth</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                               name="date_of_birth" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-venus-mars"></i>Gender</label>
                        <select class="form-control @error('gender') is-invalid @enderror" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-id-card"></i>National ID</label>
                        <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                               name="national_id" value="{{ old('national_id') }}" placeholder="NID / Passport">
                        @error('national_id')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Family Information -->
                <h5 class="section-title">
                    <i class="fas fa-users"></i>Family Information
                </h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-user-tie"></i>Father's Name</label>
                        <input type="text" class="form-control @error('father_name') is-invalid @enderror"
                               name="father_name" value="{{ old('father_name') }}" placeholder="Enter father's name">
                        @error('father_name')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-user-nurse"></i>Mother's Name</label>
                        <input type="text" class="form-control @error('mother_name') is-invalid @enderror"
                               name="mother_name" value="{{ old('mother_name') }}" placeholder="Enter mother's name">
                        @error('mother_name')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-heart"></i>Marital Status</label>
                        <select class="form-control @error('marital_status') is-invalid @enderror" name="marital_status">
                            <option value="">Select Status</option>
                            <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                        @error('marital_status')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-tint"></i>Blood Group</label>
                        <select class="form-control @error('blood_group') is-invalid @enderror" name="blood_group">
                            <option value="">Select Blood Group</option>
                            <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                            <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                        </select>
                        @error('blood_group')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-pray"></i>Religion</label>
                        <input type="text" class="form-control @error('religion') is-invalid @enderror"
                               name="religion" value="{{ old('religion') }}" placeholder="Enter religion">
                        @error('religion')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Address Information -->
                <h5 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>Address Information
                </h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-home"></i>Current Address</label>
                        <textarea class="form-control @error('current_address') is-invalid @enderror"
                                  name="current_address" rows="2" placeholder="Enter current address">{{ old('current_address') }}</textarea>
                        @error('current_address')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-home"></i>Permanent Address</label>
                        <textarea class="form-control @error('permanent_address') is-invalid @enderror"
                                  name="permanent_address" rows="2" placeholder="Enter permanent address">{{ old('permanent_address') }}</textarea>
                        @error('permanent_address')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Professional Information -->
                <h5 class="section-title">
                    <i class="fas fa-briefcase"></i>Professional Information
                </h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-calendar-check"></i>Join Date</label>
                        <input type="date" class="form-control @error('join_date') is-invalid @enderror"
                               name="join_date" value="{{ old('join_date') }}">
                        @error('join_date')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-user-tag"></i>Employment Type</label>
                        <select class="form-control @error('employment_type') is-invalid @enderror" name="employment_type">
                            <option value="">Select Type</option>
                            <option value="permanent" {{ old('employment_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                            <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="probation" {{ old('employment_type') == 'probation' ? 'selected' : '' }}>Probation</option>
                            <option value="intern" {{ old('employment_type') == 'intern' ? 'selected' : '' }}>Intern</option>
                            <option value="part_time" {{ old('employment_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                        </select>
                        @error('employment_type')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-building"></i>Branch</label>
                        <select class="form-control @error('branch_id') is-invalid @enderror" name="branch_id">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-building"></i>Designation</label>
                        <select class="form-control @error('designation_id') is-invalid @enderror" name="designation_id">
                            <option value="">Select Branch</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ old('designation_id') == $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                            @endforeach
                        </select>
                        @error('designation_id')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Photo Upload -->
                <h5 class="section-title">
                    <i class="fas fa-camera"></i>Photo
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="photo-upload-area" id="photoUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p class="mb-2">Click or drag photo to upload</p>
                            <img id="photoPreview" class="photo-preview d-none" src="#" alt="Preview">
                            <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">
                        </div>
                        @error('photo')<div class="invalid-feedback d-block small">{{ $message }}</div>@enderror
                        <div class="info-text">Supported: JPG, PNG, GIF. Max: 2MB</div>
                    </div>
                </div>

                <!-- Status -->
                <h5 class="section-title">
                    <i class="fas fa-toggle-on"></i>Status
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control @error('status') is-invalid @enderror" name="status">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <hr>
                <div class="d-flex justify-content-center gap-2 mt-4" style="gap: 10px">
                    <button type="submit" class="btn btn-success btn-submit" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Save Employee
                    </button>
                    <button type="reset" class="btn btn-primary btn-reset">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-danger btn-cancel">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Activate sidebar
    $('#candidate-sidebar, #candidate-create-sidebar').addClass('active');
    $('#collapseCandidate').addClass('show');

    $(document).ready(function() {
        // PHOTO UPLOAD - Ultra simple approach
        var photoUploadArea = document.getElementById('photoUploadArea');
        var photoInput = document.getElementById('photoInput');
        var photoPreview = document.getElementById('photoPreview');

        if (photoUploadArea && photoInput) {
            photoUploadArea.onclick = function(e) {
                // Prevent re-triggering if the click came FROM the input itself
                if (e.target === photoInput) return;
                e.preventDefault();
                e.stopPropagation();
                photoInput.click();
            };

            photoInput.onchange = function(e) {
                console.log(858);
                var file = this.files[0];
                if (file) {
                    // Check if image
                    console.log(85);
                    // Show preview
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        photoPreview.src = e.target.result;
                        photoPreview.classList.remove('d-none');

                        // Hide icon and text
                        var icon = photoUploadArea.querySelector('i');
                        var text = photoUploadArea.querySelector('p');
                        if (icon) icon.style.display = 'none';
                        if (text) text.style.display = 'none';
                    }
                    reader.readAsDataURL(file);
                }
            };

            // Drag and drop with native JS
            photoUploadArea.ondragover = function(e) {
                e.preventDefault();
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#f1f3f5';
            };

            photoUploadArea.ondragleave = function(e) {
                e.preventDefault();
                this.style.borderColor = '#dee2e6';
                this.style.backgroundColor = '#f8f9fa';
            };

            photoUploadArea.ondrop = function(e) {
                e.preventDefault();

                this.style.borderColor = '#dee2e6';
                this.style.backgroundColor = '#f8f9fa';

                var files = e.dataTransfer.files;
                if (files.length) {
                    photoInput.files = files;
                    // Trigger change event manually
                    var event = new Event('change', { bubbles: true });
                    photoInput.dispatchEvent(event);
                }
            };
        }

        // Reset button with jQuery (this is safe)
        $('button[type="reset"]').on('click', function(e) {
            e.preventDefault();

            // Reset form
            $('#employeeForm')[0].reset();

            // Reset photo preview
            if (photoPreview) {
                photoPreview.src = '#';
                photoPreview.classList.add('d-none');
            }

            // Show icon and text again
            if (photoUploadArea) {
                var icon = photoUploadArea.querySelector('i');
                var text = photoUploadArea.querySelector('p');
                if (icon) icon.style.display = 'block';
                if (text) text.style.display = 'block';
            }

            if (photoInput) photoInput.value = '';
        });

        // Form submission
        $('#employeeForm').on('submit', function(e) {
            e.preventDefault();

            var name = $('input[name="name"]').val().trim();
            var email = $('input[name="email"]').val().trim();

            if (!name || !email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Name and Email are required'
                });
                return;
            }

            Swal.fire({
                title: 'Save Employee?',
                text: 'Are you sure?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Save!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    // Use native submit
                    document.getElementById('employeeForm').submit();
                }
            });
        });
    });
</script>
@endsection
