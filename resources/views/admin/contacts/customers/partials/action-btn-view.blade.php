<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary custom-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <button type="button" class="custom-dropdown dropdown-item view-btn" data-id="{{ $id }}">
                <i class="fas fa-eye me-2 text-info"></i> View
            </button>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item edit-btn" data-id="{{ $id }}">
                <i class="fas fa-edit me-2 text-primary"></i> Edit
            </button>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    </ul>
</div>