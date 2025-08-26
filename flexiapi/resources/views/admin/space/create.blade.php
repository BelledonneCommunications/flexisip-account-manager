@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.index')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-user-rectangle"></i> {{ __('Create') }}</h1>
        <a href="{{ route('admin.spaces.index') }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
    </header>

    <form method="POST"
        action="{{ route('admin.spaces.store') }}"
        accept-charset="UTF-8">
        @csrf
        @method('post')

        <div>
            <input name="name" id="name" placeholder="My Space Name" required="required" type="text" value="{{ $space->name ?? old('name') }}">
            <label for="name">{{ __('Name') }}</label>
            @include('parts.errors', ['name' => 'name'])
        </div>

        <div>
            <input placeholder="subdomain" name="host" type="text" pattern="{{ $space::HOST_REGEX}}" style="width: 60%"
                value="{{ $space->host ?? old('host') }}" onchange="copyValueTo(this, this.form.querySelector('input[name=domain]'), '.{{ config('app.root_host') }}')">
            <input placeholder=".{{ config('app.root_host') }}" style="position: absolute; width: calc(40% - 1rem); margin-left: 1rem;" disabled>
            <label for="username">{{ __('Subdomain') }}</label>
            @include('parts.errors', ['name' => 'host'])
            @include('parts.errors', ['name' => 'full_host'])
            <span class="supporting">{{ __('Cannot be changed once created.') }} {{ __('Leave empty to create a root Space.') }}</span>
        </div>

        <div class="large">
            <input placeholder="domain.sip" required="required" name="domain" type="text" pattern="{{ $space::DOMAIN_REGEX}}" value="{{ $space->domain ?? old('domain') }}">
            <label for="username">{{ __('SIP Domain') }}</label>
            @include('parts.errors', ['name' => 'domain'])
            <span class="supporting">{{ __('Cannot be changed once created.') }}</span>
        </div>

        @include('parts.form.toggle', ['object' => $space, 'key' => 'super', 'label' => __('Super space'), 'supporting' => __('All the admins will be super admins')])

        <div>
            <input placeholder="realp.sip" name="account_realm" type="text" pattern="{{ $space::DOMAIN_REGEX}}" value="{{ $space->account_realm ?? old('account_realm') }}">
            <label for="username">{{ __('Account realm') }}</label>
            @include('parts.errors', ['name' => 'account_realm'])
            <span class="supporting">{{ __('Leave empty if similar to the domain') }}</span>
        </div>


        <div class="large">
            <input class="btn" type="submit" value="{{ __('Create') }}">
        </div>

    </form>
@endsection
