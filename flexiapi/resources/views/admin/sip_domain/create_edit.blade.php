@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.sip_domains.index') }}">SIP Domains</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <header>
        @if ($sip_domain->id)
            <h1><i class="material-symbols-outlined">dns</i> {{ $sip_domain->domain }}</h1>
            <a href="{{ route('admin.sip_domains.index') }}" class="btn btn-secondary oppose">Cancel</a>
            <a class="btn btn-secondary" href="{{ route('admin.sip_domains.delete', $sip_domain->id) }}">
                <i class="material-symbols-outlined">delete</i>
                Delete
            </a>
            <input form="create_edit_sip_domains" class="btn" type="submit" value="Update">
        @else
            <h1><i class="material-symbols-outlined">account_box</i> Create a SIP Domain</h1>
            <a href="{{ route('admin.sip_domains.index') }}" class="btn btn-secondary oppose">Cancel</a>
            <input form="create_edit_sip_domains" class="btn" type="submit" value="Create">
        @endif
    </header>

    <form method="POST" id="create_edit_sip_domains"
        action="{{ $sip_domain->id ? route('admin.sip_domains.update', $sip_domain->id) : route('admin.sip_domains.store') }}"
        accept-charset="UTF-8">
        @csrf
        @method($sip_domain->id ? 'put' : 'post')
        @if (!$sip_domain->id)
            <div>
                <input placeholder="Name" required="required" name="domain" type="text"
                    value="{{ $sip_domain->domain ?? old('domain') }}">
                <label for="username">Domain</label>
                @include('parts.errors', ['name' => 'domain'])
            </div>
        @endif

        <div>
            <input name="super" value="true" type="radio" @if ($sip_domain->super) checked @endif>
            <p>Enabled</p>
            <input name="super" value="false" type="radio" @if (!$sip_domain->super) checked @endif>
            <p>Disabled</p>
            <label>Super domain</label>
        </div>

    </form>
@endsection
