<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr>
                    <th width="35%">Customer ID</th>
                    <td>{{ $supplier->customer_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $supplier->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $supplier->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $supplier->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>
                        <span class="badge {{ $supplier->type == 'business' ? 'bg-primary' : 'bg-success' }}">
                            {{ ucfirst($supplier->type ?? 'Individual') }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if(($supplier->status ?? 1) == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr>
                    <th width="35%">Address</th>
                    <td>{{ $supplier->address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Reference</th>
                    <td>{{ $supplier->reference ?? 'N/A' }}</td>
                </tr>
                @if($supplier->type == 'business')
                <tr>
                    <th>Business Name</th>
                    <td>{{ $supplier->business_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Business Phone</th>
                    <td>{{ $supplier->business_phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Business Address</th>
                    <td>{{ $supplier->business_address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Tax Number</th>
                    <td>{{ $supplier->tax_no ?? 'N/A' }}</td>
                </tr>
                @endif
                <tr>
                    <th>Opening Balance</th>
                    <td>{{ number_format($supplier->opening_bal ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>