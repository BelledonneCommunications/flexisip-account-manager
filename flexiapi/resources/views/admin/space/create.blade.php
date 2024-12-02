@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.index') }}">Spaces</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">user-rectangle</i> Create a Space</h1>
        <a href="{{ route('admin.spaces.index') }}" class="btn btn-secondary oppose">Cancel</a>
    </header>

    <form method="POST"
        action="{{ route('admin.spaces.store') }}"
        accept-charset="UTF-8">
        @csrf
        @method('post')

        <div class="large">
            <input placeholder="subdomain" required="required" name="host" type="text" pattern="{{ $space::HOST_REGEX}}" style="width: 60%"
                value="{{ $space->host ?? old('host') }}" onchange="copyValueTo(this, this.form.querySelector('input[name=domain]'), '.{{ config('app.root_domain') }}')">
            <input placeholder=".{{ config('app.root_domain') }}" style="position: absolute; width: calc(40% - 1rem); margin-left: 1rem;" disabled>
            <label for="username">Subdomain</label>
            @include('parts.errors', ['name' => 'host'])
            @include('parts.errors', ['name' => 'full_host'])
            <span class="supporting">Cannot be changed once created</span>
        </div>

        <div class="large">
            <input placeholder="domain.sip" required="required" name="domain" type="text" pattern="{{ $space::DOMAIN_REGEX}}" value="{{ $space->domain ?? old('domain') }}">
            <label for="username">SIP Domain</label>
            @include('parts.errors', ['name' => 'domain'])
            <span class="supporting">Cannot be changed once created</span>
        </div>

        @include('parts.form.toggle', ['object' => $space, 'key' => 'super', 'label' => 'Super space', 'supporting' => 'All the admins in this Space will be Super Admins'])

        <div class="large">
            <input class="btn" type="submit" value="Create">
        </div>

    </form>
@endsection
