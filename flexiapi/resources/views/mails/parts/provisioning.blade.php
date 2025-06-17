To connect your account to the application, click on the following button:

<x-mail::button :url="route('provisioning.wizard', ['provisioning_token' => $account->provisioning_token])" color="primary">
    Connect my account
</x-mail::button>

You can also configure your device by scanning the QR code with the mobile app, or by pasting the link below into the desktop application.

![QRCode]({{ route('provisioning.qrcode', ['provisioning_token' => $account->provisioning_token]) }})
