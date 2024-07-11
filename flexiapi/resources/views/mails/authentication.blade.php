<html>
    <head>
        <title>Authenticate on {{ config('app.name') }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You are trying to authenticate to {{ config('app.name') }} using your email account.<br />
            Please enter the code bellow to finish the authentication process.
        </p>
        <p>
            <h2>{{ $recovery_code }}</h2>
        </p>
        @if ($expiration_minutes > 0)
            <p>
                The code is only available {{ $expiration_minutes }} minutes.
            </p>
        @endif
        <p>
            You can as well configure your new device using the following code or by directly flashing the QRCode:<br />

            <img src="{{ $provisioning_qrcode}}"><br />
            <a href="{{ $provisioning_link }}">Provisioning link</a>
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>