@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.index')
    @if ($account->id)
        <li class="breadcrumb-item">
            <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->identifier }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
    @else
        <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
    @endif
@endsection

@section('content')
    @if ($account->id)
        <header>
            <h1><i class="ph ph-users"></i> {{ $account->identifier }}</h1>
        </header>
        @if ($account->updated_at)
            <p title="{{ $account->updated_at }}">{{ __('Updated on') }} {{ $account->updated_at->format('d/m/Y') }}
        @endif
        @include('admin.account.parts.tabs')
    @else
        <header>
            <h1><i class="ph ph-users"></i> {{ __('New user') }}</h1>
            <a href="{{ route('admin.account.index') }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        </header>
    @endif

    <form method="POST"
        action="{{ $account->id ? route('admin.account.update', $account->id) : route('admin.account.store') }}"
        id="create_edit" accept-charset="UTF-8">
        @csrf
        @method($account->id ? 'put' : 'post')

        <div>
            <input placeholder="Username" required="required" name="username" type="text"
                value="@if($account->id){{ $account->username }}@else{{ old('username') }}@endif"
                @if ($account->id) readonly @endif>
            <label for="username">{{ __('Username') }}</label>
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div class="select">
            <select name="domain" @if (auth()->user()?->superAdmin) required @else disabled @endif>
                @foreach ($domains as $space)
                    <option value="{{ $space->domain }}" @if ($account->domain == $space->domain || old('domain') == $space->domain) selected="selected" @endif>
                        {{ $space->domain }}</option>
                @endforeach
            </select>
            <label for="domain">{{ __('Domain') }}</label>
        </div>

        <div>
            <input placeholder="John Doe" name="display_name" type="text"
                value="@if($account->id){{ $account->display_name }}@else{{ old('display_name') }}@endif">
            <label for="display_name">{{ __('Display name') }}</label>
            @include('parts.errors', ['name' => 'display_name'])
        </div>
        <div></div>

        <div>
            <input placeholder="Password" name="password" type="password" value="" autocomplete="new-password"
                @if (!$account->id) required @endif>
            <label for="password">{{ __('Password') }} @if ($account->passwords()->count() > 0) ({{ __('Currently set') }}) @endif</label>
            <small>{{ __('Fill to change') }}</small>
            @include('parts.errors', ['name' => 'password'])
        </div>

        <div>
            <input placeholder="Password" name="password_confirmation" type="password" value="" autocomplete="off"
                @if (!$account->id) required @endif>
            <label for="password_confirmation">{{ __('Confirm password') }}</label>
            @include('parts.errors', ['name' => 'password_confirmation'])
        </div>

        <div>
            <input placeholder="Email" name="email" type="email"
                value="@if($account->id){{ $account->email }}@else{{ old('email') }}@endif">
            <label for="email">{{ __('Email') }}</label>
            @include('parts.errors', ['name' => __('email')])
        </div>

        <div>
            <input placeholder="+12123123" name="phone" type="text"
                value="@if($account->id){{ $account->phone }}@else{{ old('phone') }}@endif">
            <label for="phone">{{ __('Phone number') }}</label>
            @include('parts.errors', ['name' => 'phone'])
        </div>

        <h3 class="large">{{ __('Other information') }}</h3>

        @include('parts.form.toggle', ['object' => $account, 'key' => 'blocked', 'label' => __('Blocked')])
        @include('parts.form.toggle', ['object' => $account, 'key' => 'activated', 'label' => __('Enabled')])

        <div>
            <input name="role" value="admin" type="radio" @if ($account->admin) checked @endif>
            <p>{{ __('Admin') }}</p>
            <input name="role" value="end_user" type="radio" @if (!$account->admin) checked @endif>
            <p>{{ __('User') }}</p>
            <label>{{ __('Role') }}</label>
        </div>

        @if (space()?->intercom_features)
            <div class="select">
                <select name="dtmf_protocol">
                    @foreach ($protocols as $value => $name)
                        <option value="{{ $value }}" @if ($account->dtmf_protocol == $value) selected="selected" @endif>
                            {{ $name }}</option>
                    @endforeach
                </select>
                <label for="dtmf_protocol">DTMF Protocol</label>
            </div>
        @endif

        <div class="large">
            @if ($account->id)
                <input class="btn" type="submit" value="{{ __('Update') }}">
            @else
                <input form="create_edit" class="btn" type="submit" value="{{ __('Create') }}">
            @endif
        </div>
    </form>
@endsection
