<p class="text-center pt-3">
    <i class="ph ph-envelope"></i><a href="{{ route('account.recovery.show.email') }}">{{ __('Recover your account using your email') }}</a><br />
    @if (space()->phone_registration)
    <i class="ph ph-phone"></i>{{ __('Use the mobile app to recover your account using your phone number') }}<br />
    @endif
    <i class="ph ph-qr-code"></i><a href="{{ route('account.authenticate.auth_token') }}">{{ __('Login using a QRCode') }}</a>
</p>