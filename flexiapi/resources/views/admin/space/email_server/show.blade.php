@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.index') }}">{{ __('Spaces') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.show', $space) }}">
            {{ $space->name }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.integration', $space) }}">{{ __('Integration') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Email Server') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">envelope</i> {{ $space->name }}</h1>
    </header>


    <form method="POST"
        action="{{ route('admin.spaces.email.store', $space->id) }}"
        id="show" accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input placeholder="hostname.tld" required="required" name="host" type="text"
                value="@if($emailServer->id){{ $emailServer->host }}@else{{ old('host') }}@endif">
            <label for="host">{{ __('Hostname') }}</label>
            @include('parts.errors', ['name' => 'host'])
        </div>
        <div>
            <input placeholder="25" name="port" type="number" min="0"
                @if($emailServer->id)value="{{ $emailServer->port }}"@else value="25"@endif">
            <label for="port">{{ __('Port') }}</label>
            @include('parts.errors', ['name' => 'port'])
        </div>
        <div>
            <input placeholder="username" name="username" type="text"
                value="@if($emailServer->id){{ $emailServer->username }}@else{{ old('username') }}@endif">
            <label for="username">{{ __('Username') }}</label>
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div>
            <input placeholder="password" name="password" type="text"
                value="@if($emailServer->id){{ $emailServer->password }}@else{{ old('password') }}@endif">
            <label for="password">{{ __('Password') }}</label>
            @include('parts.errors', ['name' => 'password'])
        </div>

        <div>
            <input placeholder="username@domain.tld" name="from_address" type="email"
                value="@if($emailServer->id){{ $emailServer->from_address }}@else{{ old('from_address') }}@endif">
            <label for="from_address">{{ __('From Address') }}</label>
            @include('parts.errors', ['name' => 'from_address'])
        </div>
        <div>
            <input placeholder="John Doe" name="from_name" type="text"
                value="@if($emailServer->id){{ $emailServer->from_name }}@else{{ old('from_name') }}@endif">
            <label for="from_name">{{ __('From Name') }}</label>
            @include('parts.errors', ['name' => 'from_name'])
        </div>

        <div>
            <input placeholder="The Company Team" name="signature" type="text"
                value="@if($emailServer->id){{ $emailServer->signature }}@else{{ old('signature') }}@endif">
            <label for="signature">{{ __('Signature') }}</label>
            @include('parts.errors', ['name' => 'signature'])
        </div>

    </form>

    <br />

    <input form="show" class="btn" type="submit" value="@if($emailServer->id){{ __('Update') }}@else{{ __('Create') }}@endif">
@endsection
