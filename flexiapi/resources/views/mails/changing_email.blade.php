<html>
    <head>
        <title>Changing your email address</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>
            You requested to change your email address from {{ $account->email }} to {{ $account->emailChanged->new_email }} on {{ config('app.name') }}.
        </p>
        <p>
            To confirm this change please click on the following link:
            <a href="{{ route('account.email.update', ['hash' => $account->emailChanged->hash]) }}">
                {{ route('account.email.update', ['hash' => $account->emailChanged->hash]) }}
            </a>.
        </p>
        <p>
            If you are not at the origin of this change just ignore this message.
        </p>
        <p>
            Regards,<br />
            {{ config('mail.signature') }}
        </p>
    </body>
</html>
