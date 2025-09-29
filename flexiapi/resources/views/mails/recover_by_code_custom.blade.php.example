@extends('mails.layout')

@section('content')
# {{ __('Account Recovery Request') }}

{{ __('We received a request to recover your account on :space', ['space' => $account->space->name]) }}

{{ __('To proceed, please enter the verification code below:') }}

## {{ $account->recovery_code }}

@if (config('app.recovery_code_expiration_minutes') > 0)
{{ __('This code is valid for :minutes minutes.', ['minutes' => config('app.recovery_code_expiration_minutes')]) }}
@endif

@endsection
