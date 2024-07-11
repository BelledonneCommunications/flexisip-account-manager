Hello,

You are trying to authenticate to {{ config('app.name') }} using your email account.
Please enter the code bellow to finish the authentication process.

{{ $recovery_code }}

@if ($expiration_minutes > 0)
The code is only available {{ $expiration_minutes }} minutes.
@endif

You can as well configure your new device using the following code or by directly flashing the QRCode in the following link:

{{ $provisioning_qrcode}}

Regards,
{{ config('mail.signature') }}