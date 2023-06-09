Hello,

You just created an account on {{ config('app.name') }} using your email account.

Please enter the following code on the confirmation page:

{{ $code }}

Regards,
{{ config('mail.signature') }}