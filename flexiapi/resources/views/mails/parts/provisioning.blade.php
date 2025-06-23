To connect your account to the application, click on the following link:

[{{__('Login to my account')}}]({{ route('provisioning.wizard', ['provisioning_token' => $account->provisioning_token]) }})

You can also configure your device by scanning the QR code with the mobile app, or by pasting the link below into the desktop application.

![QRCode]({{ route('provisioning.qrcode', ['provisioning_token' => $account->provisioning_token]) }})
