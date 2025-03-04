<html>
    <head>
        <title>Register on {{ space()->name }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You just created an account on {{ space()->name }} using your email account.<br />
            Please follow the unique link bellow to set up your password and finish the registration process.
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
