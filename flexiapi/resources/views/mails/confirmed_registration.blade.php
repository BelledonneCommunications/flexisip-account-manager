<html>
    <head>
        <title>Registration confirmed {{ config('app.name') }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            Your SIP account has been successfully created using {{ config('app.name') }}.<br />
            You can now configure this account on any SIP-compatible application using the following parameters:<br />
            <br />

            <b>SIP address:</b> sip:{{ $account->identifier }}<br />
            <b>Username:</b> {{ $account->username }}<br />
            <b>Domain:</b> {{ $account->domain }}<br />
            <br />
            @if (!empty(config('app.proxy_registrar_address')))
                <b>Proxy/registrar address: </b> sip:{{ config('app.proxy_registrar_address') }}<br />
            @endif
            @if (!empty(config('app.transport_protocol')))
                <b>Transport: </b> {{ config('app.transport_protocol') }} <br />
            @endif
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>
