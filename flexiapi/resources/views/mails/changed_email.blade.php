<html>
    <head>
        <title>Changing your email address</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You have changed your email address to the current one on {{ config('app.name') }}.
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>