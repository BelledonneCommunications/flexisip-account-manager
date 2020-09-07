<html>
    <head>
        <title>Authenticate on {{ config('app.name') }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You are trying to authenticate to {{ config('app.name') }} using your email account.<br />
            Please follow the unique link bellow to finish the authentication process.
        </p>
        <p>
            <a href="{{ $link }}">{{ $link }}</a>
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>