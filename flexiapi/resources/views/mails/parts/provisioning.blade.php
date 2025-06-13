{{ __('We are pleased to inform you that your account has been successfully created.') }}

{{ __('To start using your account, click the button below:') }}

[{{ __('Login to my account') }}]({{ $account->provisioning_wizard_url }})

{{ __('You can also connect your account to the mobile app by scanning the QR code with the app') }}

![QRCode]({{ $account->provisioning_qrcode_url }})
