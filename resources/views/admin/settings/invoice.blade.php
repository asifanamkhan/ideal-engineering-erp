@extends('layouts.dashboard.app')

@section('title', 'Invoice Settings')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .card-header {
        background: forestgreen;
        color: white;
        padding: 12px 20px;
    }
    .card-header h6 {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 0;
    }
    .current-logo {
        max-width: 100px;
        max-height: 80px;
        object-fit: contain;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 5px;
    }
    .signature-preview {
        max-width: 150px;
        max-height: 60px;
        object-fit: contain;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Invoice Settings</h4>
                <span class="text-muted small">Configure invoice header, logo, signature and footer</span>
            </div>
            <span class="breadcrumb-item">Dashboard / Settings / Invoice</span>
        </div>
    </div>

    <form action="{{ route('admin.invoice-settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-heading me-2"></i> Header Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Header Text (HTML)</label>
                                    <textarea name="header_text" id="header_text" class="form-control summernote" rows="8">{{ $setting->header_text ?? '' }}</textarea>
                                    <small class="text-muted">This will appear at the top of all invoices. You can use HTML tags.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Company Logo</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <small class="text-muted">Recommended size: 120px width, 80px height</small>
                                    @if(isset($setting->logo) && $setting->logo)
                                    <div class="mt-2">
                                        <p class="mb-1">Current Logo:</p>
                                        <img src="{{ asset($setting->logo) }}" alt="Logo" class="current-logo">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Authorized Signature (Image)</label>
                                    <input type="file" name="author_signature" class="form-control" accept="image/*">
                                    <small class="text-muted">Upload signature image for authorized person</small>
                                    @if(isset($setting->author_signature) && $setting->author_signature)
                                    <div class="mt-2">
                                        <p class="mb-1">Current Signature:</p>
                                        <img src="{{ asset($setting->author_signature) }}" alt="Signature" class="signature-preview">
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card shadow mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i> Footer Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Footer Text</label>
                            <textarea name="footer_text" id="footer_text" class="form-control" rows="3" placeholder="Enter footer text...">{{ $setting->footer_text ?? '' }}</textarea>
                            <small class="text-muted">This will appear at the bottom of all invoices.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 mb-4 text-center">
            <button type="submit" class="btn btn-success px-5 py-2">
                <i class="fas fa-save me-2"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $('#settings-sidebar').addClass('active');
    $('#invoice-settings-index-sidebar').addClass('active');
    $('#collapseSettings').addClass('show');
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endsection
