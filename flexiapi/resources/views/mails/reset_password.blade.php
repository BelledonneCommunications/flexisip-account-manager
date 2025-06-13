@extends('mails.layout')

@section('content')
# {{ __('Reset your password') }}

{{ __('Hello') }} {{ $account->identifier }},

{{ __('We received a request to reset your password for your account on :space.', ['space' => $account->space->name]) }}

{{ __('Click the button below to choose a new password:') }}

[{{ __('Reset my password') }}]({{ route('account.reset_password_email.change', $account->currentResetPasswordEmailToken->token) }})

{{ __('This link will expire in :hour hours.', ['hour' => config('app.reset_password_email_token_expiration_minutes')/60 ]) }}

@endsection
