{{-- resources/views/admin/hrm/salary/partials/action-btn-view.blade.php --}}

<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle custom-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <button type="button" class="dropdown-item custom-dropdown view-btn" data-id="{{ $id }}">
                <i class="fas fa-eye me-2 text-info"></i> View Details
            </button>
        </li>
        {{-- @if($status == 'draft' || $status == 'generated')
        <li>
            <button type="button" class="dropdown-item custom-dropdown edit-btn" data-id="{{ $id }}">
                <i class="fas fa-edit me-2 text-primary"></i> Edit
            </button>
        </li>
        @endif --}}
        @if($status != 'paid')
        <li>
            <button type="button" class="dropdown-item custom-dropdown mark-paid-btn" data-id="{{ $id }}">
                <i class="fas fa-check-circle me-2 text-success"></i> Mark as Paid
            </button>
        </li>
        @endif
        <li>
            <button type="button" class="dropdown-item custom-dropdown delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    </ul>
</div>
