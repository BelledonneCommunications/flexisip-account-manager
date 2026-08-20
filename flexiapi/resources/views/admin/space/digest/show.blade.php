@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Digest configuration') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-key"></i> {{ $space->name }}</h1>
    </header>

    <form method="POST" action="{{ route('admin.spaces.digest.store', $space->domain) }}" id="show" accept-charset="UTF-8">
        @csrf
        @method('post')
        @php
            $existingAccounts = ($space->accounts()->count() > 0);
        @endphp
        <div>
            <input name="realm" @if ($existingAccounts) disabled @endif id="realm"
                placeholder="server.tld" value="{{ $digestAuthenticationConfiguration->realm }}">
            <label for="realm">{{ __('Account realm') }}</label>
            <span class="supporting">{{ __('A custom realm for the Space accounts') }}</span>
            @if ($existingAccounts)
                <span class="supporting">{{ __('This field cannot be edited as the Space already have existing accounts using it.') }}</span>
            @endif
            @include('parts.errors', ['name' => 'realm'])
        </div>
        <div class="select">
            <select name="default_password_algorithm">
                @foreach (App\PasswordAlgorithm::cases() as $algorithm)
                    <option value="{{ $algorithm }}" @selected($algorithm == $digestAuthenticationConfiguration->default_password_algorithm)>
                        {{ $algorithm }}
                    </option>
                @endforeach
            </select>
            <label for="default_password_algorithm">{{ __('Password Hashing Algorithm') }}</label>
            <span class="supporting">{{ __('Note: changing the password hashing algorithm will not affect existing passwords. It will only apply to new passwords created after this change.') }}</span>
        </div>
    </form>

    <br />

    <input form="show" class="btn" type="submit" value="@if ($space->id) {{ __('Update') }}@else{{ __('Create') }} @endif">


@endsection
