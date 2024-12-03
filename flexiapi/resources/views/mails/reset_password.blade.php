<html>
    <head>
        <title>Reset your password on {{ config('app.name') }}</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You are invited to reset your {{ $token->account->identifier }} account password on  {{ config('app.name') }} via your email account.
        </p>
        <p>The following link will be valid for {{ config('app.reset_password_email_token_expiration_minutes')/60 }} hours.</p>
        <p>
            <a href="{{ route('account.reset_password_email.change', $token->token) }}">
                {{ route('account.reset_password_email.change', $token->token) }}
            </a>
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>
