@php
    $company = \App\Support\CurrentCompany::get();
@endphp

@if ($company)
    <a
        href="{{ url('/app/company-switcher') }}"
        class="fi-btn fi-btn-size-sm fi-color-gray"
        style="margin-right: 12px;"
    >
        Empresa: {{ $company->name }}
    </a>
@endif