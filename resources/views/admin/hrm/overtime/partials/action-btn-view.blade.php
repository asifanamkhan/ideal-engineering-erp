{{-- resources/views/admin/hrm/overtime/partials/action-btn-view.blade.php --}}

<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle custom-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <button type="button" class="dropdown-item custom-dropdown view-overtime-btn" data-date="{{ $date }}">
                <i class="fas fa-eye me-2 text-info"></i> View Details
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item custom-dropdown delete-date-btn" data-date="{{ $date }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete All
            </button>
        </li>
    </ul>
</div>
