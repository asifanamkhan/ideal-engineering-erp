<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary custom-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a href="{{ route('admin.job-books.show', $id) }}" class="custom-dropdown dropdown-item">
                <i class="fas fa-eye me-2 text-info"></i> View
            </a>
        </li>
        <li>
            <a href="{{ route('admin.job-books.edit', $id) }}" class="custom-dropdown dropdown-item">
                <i class="fas fa-edit me-2 text-primary"></i> Edit
            </a>
        </li>
        <li>
            <a href="{{ route('admin.job-parts.create') }}?job_id={{ $id }}" class="custom-dropdown dropdown-item">
                <i class="fas fa-microchip me-2 text-info"></i> Purse List
            </a>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item print-btn" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#printOptionsModal">
                <i class="fas fa-print me-2 text-success"></i> Print
            </button>
        </li>
        @if($job_status == 'delivered')
        <li>
            <button type="button" class="custom-dropdown dropdown-item challan-print-btn" data-id="{{ $id }}">
                <i class="fas fa-truck me-2 text-success"></i> Challan Print
            </button>
        </li>
        @endif

        @if(isset($show_convert_btn) && $show_convert_btn)
        <li>
            <button type="button" class="custom-dropdown dropdown-item convert-to-invoice-btn" data-id="{{ $id }}">
                <i class="fas fa-exchange-alt me-2 text-warning"></i> Convert to Invoice
            </button>
        </li>
        @endif
        <li>
            <button type="button" class="custom-dropdown dropdown-item payment-btn"
                data-id="{{ $id }}"
                data-customer-id="{{ $customer_id }}">
                <i class="fas fa-money-bill-wave me-2 text-success"></i> Payment
            </button>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item change-status-btn"
                data-id="{{ $id }}"
                data-status="{{ $job_status }}"
                data-delivery-date="{{ $delivery_date ?? '' }}">
                <i class="fas fa-exchange-alt me-2 text-primary"></i> Change Status
            </button>
        </li>
        <li>
            <button type="button" class="custom-dropdown dropdown-item delete-btn" data-id="{{ $id }}">
                <i class="fas fa-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    </ul>
</div>
