@extends('mails.layout')

@section('content')
# Reset your password on {{ $account->space->name }}

Hello {{ $account->identifier }},

You are invited to reset your {{ $account->identifier }} account password on {{ $account->space->name }} via your email account.

The following link will be valid for {{ config('app.reset_password_email_token_expiration_minutes')/60 }} hours.

<x-mail::button :url="route('account.reset_password_email.change', $account->currentResetPasswordEmailToken->token)" color="primary">
    Reset by email
</x-mail::button>
@endsection
