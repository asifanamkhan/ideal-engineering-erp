{{-- resources/views/admin/hrm/leave_applications/partials/action-btn-view.blade.php --}}

<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle custom-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @if($status == 'pending')
        <li>
            <button type="button" class="dropdown-item custom-dropdown edit-btn" data-id="{{ $id }}">
                <i class="fas fa-edit me-2 text-primary"></i> Edit
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item custom-dropdown approve-btn" data-id="{{ $id }}">
                <i class="fas fa-check-circle me-2 text-success"></i> Approve
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item custom-dropdown reject-btn" data-id="{{ $id }}">
                <i class="fas fa-times-circle me-2 text-danger"></i> Reject
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item custom-dropdown delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
        @elseif($status == 'approved')
        <li>
            <button type="button" class="dropdown-item custom-dropdown unapprove-btn" data-id="{{ $id }}">
                <i class="fas fa-undo-alt me-2 text-warning"></i> Unapprove
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item custom-dropdown delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
        @elseif($status == 'rejected')
        <li>
            <button type="button" class="dropdown-item custom-dropdown delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
        @else
        <li>
            <button type="button" class="dropdown-item custom-dropdown" disabled>
                <i class="fas fa-lock me-2 text-muted"></i> No Actions
            </button>
        </li>
        @endif
    </ul>
</div>
