@php
    $general_settings = DB::table('generel_settings')->first();
@endphp

<div class="bill-header">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        @if(isset($general_settings->logo) && $general_settings->logo)
            <div style="width: 120px;">
                <img src="{{ asset($general_settings->logo) }}" alt="Logo" style="max-width: 110px; max-height: 80px; object-fit: contain;">
            </div>
        @endif
        <div style="flex: 1; text-align: center;">
            <div class="company-name">{{ $general_settings->company_name ?? 'Ideal Engineering Works' }}</div>
            <div class="company-sub">
                {{ $general_settings->company_description ?? 'All kinds Of Marine, Petrol, Diesel, Gas Generator, MWM, Caterpillar, Waukesha, Jenbacher, Wartsila, Perkins, Cummins, Mitsubishi, Hino, Ashok Leyland, TATA, Excavator Engine & Mill Factory\'s Any Spare Parts Repairing By Expert Hands' }}
            </div>
            <div class="address-line">{{ $general_settings->address ?? 'Milgate (Sonali Road), Tongi, Gazipur.' }}</div>
            <div class="contact-line">Phone: {{ $general_settings->phone ?? '02-9816620' }}, Cell: {{ $general_settings->mobile ?? '01725-126517, 01855956767, 01716657171, 01981022952' }}</div>
            <div class="contact-line">email: {{ $general_settings->email ?? 'azizur.rahman6767@gmail.com, idealengineering.manager@gmail.com' }}</div>
        </div>
        @if(isset($general_settings->logo) && $general_settings->logo)
            <div style="width: 120px;"></div>
        @endif
    </div>
</div>
