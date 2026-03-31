@extends('layouts.dashboard.app')
@section('css')
<style>
    .logo-preview-box {
        border: 1px dashed #dee2e6;
        border-radius: 5px;
        padding: 10px;
        background-color: #f8f9fa;
    }
    .preview-container {
        position: relative;
        display: inline-block;
    }
    .custom-file-label::after {
        content: "Browse";
    }
    .new-logo-preview .preview-container {
        border: 2px solid #28a745;
        border-radius: 5px;
        padding: 5px;
    }
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                </div>
                <div class="card-body">
                    @if(Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(Session::has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ Session::get('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('admin.general-settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_name">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ $setting->company_name ?? old('company_name') }}" required>
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ $setting->email ?? old('email') }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $setting->phone ?? old('phone') }}" required>
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Alternative Phone  <span class="text-danger">*</span></label>
                                    <input type="text" name="phone_two" id="phone_two" class="form-control @error('phone_two') is-invalid @enderror" value="{{ $setting->phone_two ?? old('phone_two') }}">
                                    @error('phone_two')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="address">Address <span class="text-danger">*</span></label>
                                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3" required>{{ $setting->address ?? old('address') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="logo">Logo</label>
                                    <div class="logo-upload-container">
                                        <div class="custom-file">
                                            <input type="file" name="logo" id="logo" class="custom-file-input @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg">
                                            <label class="custom-file-label" for="logo">Choose file</label>
                                        </div>
                                        @error('logo')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror

                                        <!-- Logo Preview Box -->
                                        <div class="logo-preview-box mt-3" id="logoPreviewBox">
                                            @if(isset($setting->logo) && $setting->logo)
                                                <div class="current-logo">
                                                    <p class="mb-1"><strong>Current Logo:</strong></p>
                                                    <div class="preview-container">
                                                        <img src="{{ asset($setting->logo) }}" alt="Company Logo" class="img-thumbnail" style="max-height: 150px; max-width: 100%;">
                                                        <p class="text-muted small mt-1">{{ basename($setting->logo) }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="no-logo text-center p-3 border rounded bg-light">
                                                    <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                                    <p class="mb-0">No logo uploaded</p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- New Logo Preview (will show when selecting new file) -->
                                        <div class="new-logo-preview mt-3" id="newLogoPreview" style="display: none;">
                                            <p class="mb-1"><strong>New Logo Preview:</strong></p>
                                            <div class="preview-container position-relative">
                                                <img src="" alt="New Logo Preview" id="newLogoPreviewImg" class="img-thumbnail" style="max-height: 150px; max-width: 100%;">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 right-0" id="removeNewLogo" style="top: 5px; right: 5px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('js')
<script>
    $('#settings-sidebar').addClass('active');
    $('#gsettings-index-sidebar').addClass('active');
    $('#collapseSettings').addClass('show');
</script>
@endsection
