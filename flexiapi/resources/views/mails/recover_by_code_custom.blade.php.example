@extends('mails.layout')

@section('content')
# Authenticate on {{ $account->space->name }}

Hello {{ $account->identifier }},

You are trying to authenticate to {{ $account->space->name }} using your email account.

Please enter the code bellow to finish the authentication process.

## {{ $account->recovery_code }

@if (config('app.recovery_code_expiration_minutes') > 0)
The code is only available {{ config('app.recovery_code_expiration_minutes') }} minutes.
@endif

@include('mails.parts.provisioning')

@endsection
