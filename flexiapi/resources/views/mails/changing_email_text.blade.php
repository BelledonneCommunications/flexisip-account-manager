Hello,

You requested to change your email address from {{ $account->email }} to {{ $account->emailChanged->new_email }} on {{ config('app.name') }}.

To confirm this change please click on the following link: {{ route('account.email.request_update', $account->emailChanged->hash) }}.

If you are not at the origin of this change just ignore this message.

Regards,
{{ config('mail.signature') }}