@extends('mails.layout')

@section('content')
# Account registration on  {{ $account->space->name }}

Hello {{ $account->identifier }},

You just created an account on {{ $account->space->name }} using your email account.

Please enter the following code on the confirmation page:

## {{ $account->emailChangeCode()->first()->code }}
@endsection
