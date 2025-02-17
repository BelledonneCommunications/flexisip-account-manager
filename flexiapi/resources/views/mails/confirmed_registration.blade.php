<html>
    <head>
        <title>Registration confirmed {{ config('app.name') }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            @if (space()->confirmed_registration_text)
                {{ strip_tags(parsedown(space()->confirmed_registration_text)) }}
            @else
                Your SIP account has been successfully created.<br />
                You can now configure this account on any SIP-compatible application using the following parameters:<br />
                <br />
            @endif

            <b>SIP address:</b> sip:{{ $account->identifier }}<br />
            <b>Username:</b> {{ $account->username }}<br />
            <b>Domain:</b> {{ $account->domain }}<br />
            <br />
            @if (!empty(space()?->account_proxy_registrar_address))
                <b>Proxy/registrar address: </b> sip:{{ space()?->account_proxy_registrar_address }}<br />
            @endif
            @if (!empty(config('app.transport_protocol_text')))
                <b>Transport: </b> {{ config('app.transport_protocol_text') }} <br />
            @endif
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>
