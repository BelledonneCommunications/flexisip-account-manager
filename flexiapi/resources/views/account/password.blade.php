@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Change') }}</li>
@endsection

@section('content')
    <header>
        @if ($account->passwords()->count() > 0)
            <h1><i class="ph ph-lock"></i> {{ __('Edit') }}</h1>
        @else
            <h1><i class="ph ph-lock"></i> {{ __('Create') }}</h1>
        @endif

        <a href="{{ route('account.dashboard') }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        <input form="password_update" class="btn" type="submit" value="{{ __('Edit') }}">
    </header>

    <form id="password_update" method="POST" action="{{ route('account.password.update') }}" accept-charset="UTF-8">
        @csrf

        <div>
            <input type="password" name="password" required>
            <label for="password">{{ __('Password') }}</label>
            @include('parts.errors', ['name' => 'password'])
        </div>
        <div>
            <input type="password" name="password_confirmation" required>
            <label for="password_confirmation">{{ __('Confirm password') }}</label>
        </div>
    </form>
@endsection
