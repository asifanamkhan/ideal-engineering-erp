<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle custom-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a href="{{ route('admin.roles.edit', $id) }}" class="dropdown-item custom-dropdown edit-btn" data-id="{{ $id }}">
                <i class="fas fa-edit me-2 text-primary"></i> Edit
            </a>
        </li>
        <li>

            <button type="button"
                class="dropdown-item custom-dropdown delete-role-btn "
                data-id="{{ $id }}"
                data-name="{{ $name ?? '' }}"
                data-url="{{ route('admin.roles.destroy', $id) }}"
                title="Delete">
            <i class="fas fa-trash text-danger me-2"></i> Delete
        </button>
        </li>
    </ul>
</div>
