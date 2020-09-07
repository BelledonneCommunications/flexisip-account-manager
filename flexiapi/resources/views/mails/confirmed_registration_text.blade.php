Registration confirmed {{ config('app.name') }}

Hello,

Your SIP account has been successfully created using {{ config('app.name') }}.
You can now configure this account on any SIP-compatible application using the following parameters:

    SIP address: sip:{{ $account->identifier }}
    Username: {{ $account->username }}
    Domain: {{ $account->domain }}

@if (!empty(config('app.proxy_registrar_address')))
    Proxy/registrar address:  sip:{{ config('app.proxy_registrar_address') }}
@endif
@if (!empty(config('app.transport_protocol')))
    Transport:  {{ config('app.transport_protocol') }}
@endif

Regards,
{{ config('mail.signature') }}