@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    <button type="button" class="btn btn-close" data-bs-dismiss="alert" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
    <button type="button" class="btn btn-close" data-bs-dismiss="alert" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif
