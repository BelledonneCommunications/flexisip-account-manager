Registration confirmed {{ config('app.name') }}

Hello,

@if (config('instance.confirmed_registration_text'))
{{ strip_tags(parsedown(config('instance.confirmed_registration_text'))) }}
@else
Your SIP account has been successfully created.
You can now configure this account on any SIP-compatible application using the following parameters:
@endif

    SIP address: sip:{{ $account->identifier }}
    Username: {{ $account->username }}
    Domain: {{ $account->domain }}

@if (!empty(config('app.proxy_registrar_address')))
    Proxy/registrar address:  sip:{{ config('app.proxy_registrar_address') }}
@endif
@if (!empty(config('app.transport_protocol_text')))
    Transport:  {{ config('app.transport_protocol_text') }}
@endif

Regards,
{{ config('mail.signature') }}

strip_tags