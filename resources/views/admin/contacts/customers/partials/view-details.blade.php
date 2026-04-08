<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr>
                    <th width="35%">Customer ID</th>
                    <td>{{ $customer->customer_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $customer->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $customer->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $customer->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>
                        <span class="badge {{ $customer->type == 'business' ? 'bg-primary' : 'bg-success' }}">
                            {{ ucfirst($customer->type ?? 'Individual') }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if(($customer->status ?? 1) == 1)
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
                    <td>{{ $customer->address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Reference</th>
                    <td>{{ $customer->reference ?? 'N/A' }}</td>
                </tr>
                @if($customer->type == 'business')
                <tr>
                    <th>Business Name</th>
                    <td>{{ $customer->business_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Business Phone</th>
                    <td>{{ $customer->business_phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Business Address</th>
                    <td>{{ $customer->business_address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Tax Number</th>
                    <td>{{ $customer->tax_no ?? 'N/A' }}</td>
                </tr>
                @endif
                <tr>
                    <th>Opening Balance</th>
                    <td>{{ number_format($customer->opening_bal ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>