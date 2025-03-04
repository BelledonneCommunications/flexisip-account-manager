Hello,

You just created an account on {{ space()->name }} using your email account.

Please enter the following code on the confirmation page:

{{ $code }}

Regards,
{{ config('mail.signature') }}