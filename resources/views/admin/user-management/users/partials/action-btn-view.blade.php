<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle custom-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <button type="button" class="dropdown-item custom-dropdown edit-btn" data-id="{{ $id }}">
                <i class="fas fa-edit me-2 text-primary"></i> Edit
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item custom-dropdown delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    </ul>
</div>