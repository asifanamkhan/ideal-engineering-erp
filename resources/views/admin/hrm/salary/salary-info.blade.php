@extends('layouts.dashboard.app')

@section('css')

@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1">
                <i class="fas fa-users me-2"></i>
                Employee list
                </h4>
            </div>
            <span class="breadcrumb-item">Dashboard / employees</span>
            <div>
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-2"></i> Add new employee
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">

            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<!-- DataTables -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Activate sidebar
    $('#employee-sidebar, #employee-index-sidebar').addClass('active');
    $('#collapseHRM').addClass('show');

</script>
@endsection
