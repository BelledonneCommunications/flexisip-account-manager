@extends('mails.layout')

@section('content')
# {{ __('Welcome to :space', ['space' => $account->space->name]) }}

{{ __('Thank you for registering on :space.', ['space' => $account->space->name]) }}

{{ __('To complete your registration, please enter the verification code below in the registration form:') }}

## {{ $account->emailChangeCode()->first()->code }}

@if (config('app.recovery_code_expiration_minutes') > 0)
{{ __('This code is valid for :minutes minutes.', ['minutes' => config('app.recovery_code_expiration_minutes')]) }}
@endif

@endsection
