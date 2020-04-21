<html>
    <head>
        <title>Register on {{ config('app.name') }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You just created an account on {{ config('app.name') }} using your email account.<br />
            Please follow the unique link bellow to finish the registration process.
        </p>
        <p>
            <a href="{{ $link }}">{{ $link }}</a>
        </p>
        <p>
            Regards,<br />
            The Linphone team.
        </p>
    </body>
</html>
