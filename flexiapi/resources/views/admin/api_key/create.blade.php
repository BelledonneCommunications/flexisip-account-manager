@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.api_keys.index') }}">{{ __('API Keys') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('Create') }}
    </li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">key</i> {{ __('Create') }}</h1>
        <a href="{{ route('admin.api_keys.index') }}" class="btn btn-secondary oppose">{{ __('Cancel') }}</a>
    </header>

    <form method="POST"
    action="{{ route('admin.api_keys.store') }}"
    accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input name="name" id="name" placeholder="My Key Name" required="required" type="text" value="{{ old('name') }}">
            <label for="name">{{ __('Name') }}</label>
            @include('parts.errors', ['name' => 'name'])
        </div>

        <div>
            <input placeholder="60" required="required" name="expires_after_last_used_minutes" type="number" value="{{ old('expires_after_last_used_minutes') ?? 60 }}">
            <label for="username">{{ __('Activity expiration delay') }}</label>
            @include('parts.errors', ['name' => 'expires_after_last_used_minutes'])
            <span class="supporting">{{ __('Number of minutes to expire the key after the last request.') }} {{ __('Unlimited if set to 0') }}</span>
        </div>

        <div class="large">
            <input class="btn" type="submit" value="{{ __('Create') }}">
        </div>
    </form>
@endsection
