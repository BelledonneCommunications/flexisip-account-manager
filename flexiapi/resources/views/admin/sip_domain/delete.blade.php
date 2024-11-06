@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.sip_domains.index') }}">SIP Domains</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">trash</i> Delete a SIP Domain</h1>
        <a href="{{ route('admin.sip_domains.edit', $sip_domain->id) }}" class="btn btn-secondary oppose">Cancel</a>
        <input form="delete" class="btn" type="submit" value="Delete">
    </header>
    <form id="delete" method="POST" action="{{ route('admin.sip_domains.destroy', $sip_domain->id) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>You are going to permanently delete the following domain please confirm your action.</p>
            <h3>{{ $sip_domain->domain }}</h3>
            <p>This will also destroy <b>{{ $sip_domain->accounts()->count() }} related accounts</b></p>

            <input name="sip_domain" type="hidden" value="{{ $sip_domain->id }}">
        </div>

        <div>
            <input placeholder="domain.tld" required="required" name="domain" type="text">
            <label for="username">Please retype the domain here to confirm</label>
            @include('parts.errors', ['name' => 'domain'])
        </div>
    </form>
@endsection
