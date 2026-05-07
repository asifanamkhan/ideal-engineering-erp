

@if($invoice_settings && $invoice_settings->header_text)
    <div class="bill-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            @if($invoice_settings && $invoice_settings->logo)
                <div style="width: 120px;">
                    <img src="{{ asset($invoice_settings->logo) }}" alt="Logo" style="max-width: 110px; max-height: 80px; object-fit: contain;">
                </div>
            @endif
            <div style="flex: 1; text-align: center;">
                {!! $invoice_settings->header_text !!}
            </div>
            @if($invoice_settings && $invoice_settings->logo)
                <div style="width: 120px;"></div>
            @endif
        </div>
    </div>
@else
    <div class="bill-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            @if($invoice_settings && $invoice_settings->logo)
                <div style="width: 120px;">
                    <img src="{{ asset($invoice_settings->logo) }}" alt="Logo" style="max-width: 110px; max-height: 80px; object-fit: contain;">
                </div>
            @endif
            <div style="flex: 1; text-align: center;">
                <div class="company-name">{{ $invoice_settings->company_name ?? 'Ideal Engineering Works' }}</div>
                <div class="company-sub">{{ $invoice_settings->company_description ?? '...' }}</div>
                <div class="address-line">{{ $invoice_settings->address ?? 'Milgate (Sonali Road), Tongi, Gazipur.' }}</div>
                <div class="contact-line">Phone: {{ $invoice_settings->phone ?? '02-9816620' }}, Cell: {{ $invoice_settings->mobile ?? '01725-126517' }}</div>
                <div class="contact-line">email: {{ $invoice_settings->email ?? 'idealengineering.manager@gmail.com' }}</div>
            </div>
            @if($invoice_settings && $invoice_settings->logo)
                <div style="width: 120px;"></div>
            @endif
        </div>
    </div>
@endif


