<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Action
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <button onclick="approveCandidate({{ $id }})" class="dropdown-item text-success">
                <i class="fas fa-check me-2"></i> Approve
            </button>
        </li>
        <li>
            <a href="{{ route('candidates.show', $id) }}" class="dropdown-item text-info">
                <i class="fas fa-eye me-2"></i> View
            </a>
        </li>
        <li>
            <a href="{{ route('candidates.edit', $id) }}" class="dropdown-item text-warning">
                <i class="fas fa-edit me-2"></i> Edit
            </a>
        </li>
        <li>
            <button type="button" class="dropdown-item text-danger delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2"></i> Delete
            </button>
        </li>
    </ul>
</div>
