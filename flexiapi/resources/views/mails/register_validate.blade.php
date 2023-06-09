<html>
    <head>
        <title>Account registered on {{ config('app.name') }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You just created an account on {{ config('app.name') }} using your email account.<br />
            Please enter the following code on the confirmation page:
        </p>
        <p>
            <h2>{{ $code }}</h2>
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>
