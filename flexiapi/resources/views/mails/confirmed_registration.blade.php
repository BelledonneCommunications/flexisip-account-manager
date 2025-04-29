@extends('mails.layout')

@section('content')
# Welcome to {{ $account->space->name }}

Hello {{ $account->identifier }},

@if ($account->space->confirmed_registration_text)
{{ strip_tags(parsedown($account->space->confirmed_registration_text)) }}
@else
Your SIP account has been successfully created.

You can now configure this account on any SIP-compatible application using the following parameters:
@endif

* SIP address: sip:{{ $account->identifier }}
* Username: {{ $account->username }}
* Domain: {{ $account->domain }}
@if (!empty($account->space->account_proxy_registrar_address))
* Proxy/registrar address: sip:{{ $account->space->account_proxy_registrar_address }}
@endif
@if (!empty(config('app.transport_protocol_text')))
* Transport: {{ config('app.transport_protocol_text') }}
@endif

@endsection
