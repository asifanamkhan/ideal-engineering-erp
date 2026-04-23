<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary custom-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a href="{{ route('admin.expenses.show', $id) }}" class="custom-dropdown dropdown-item">
                <i class="fas fa-eye me-2 text-info"></i> View
            </a>
        </li>
        <li>
            <a href="{{ route('admin.expenses.edit', $id) }}" class="custom-dropdown dropdown-item">
                <i class="fas fa-edit me-2 text-primary"></i> Edit
            </a>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item print-expense-btn" data-id="{{ $id }}">
                <i class="fas fa-print me-2 text-success"></i> Print
            </button>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item payment-btn" data-id="{{ $id }}">
                <i class="fas fa-money-bill-wave me-2 text-success"></i> Payment
            </button>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    </ul>
</div>
