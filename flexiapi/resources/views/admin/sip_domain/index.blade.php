@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">
        SIP Domains
    </li>
@endsection

@section('content')

<header>
    <h1><i class="material-symbols-outlined">dns</i> SIP Domains</h1>
    <a class="btn oppose" href="{{ route('admin.sip_domains.create') }}">
        <i class="material-symbols-outlined">add_circle</i>
        New SIP Domain
    </a>
</header>

<table>
    <thead>
        <tr>
            <th>SIP Domain</th>
            <th>Accounts</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sip_domains as $sip_domain)
            <tr>
                <td>
                    <a href="{{ route('admin.sip_domains.edit', $sip_domain->id) }}">
                        {{ $sip_domain->domain }}
                        @if ($sip_domain->super) <span class="badge badge-error" title="Super domain">Super</span> @endif
                    </a>
                </td>
                <td>
                    {{ $sip_domain->accounts_count }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection