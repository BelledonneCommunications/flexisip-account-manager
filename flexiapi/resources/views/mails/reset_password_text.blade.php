Hello,

You are invited to reset your {{ $token->account->identifier }} account password on {{ space()->name }} via your email account.

The following link will be valid for {{ config('app.reset_password_email_token_expiration_minutes')/60 }} hours.

{{ route('account.reset_password_email.change', $token->token) }}

Regards,
{{ config('mail.signature') }}