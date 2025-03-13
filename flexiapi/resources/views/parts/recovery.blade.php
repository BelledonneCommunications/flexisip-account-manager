<p class="text-center pt-3">
    <i class="ph">envelope</i><a href="{{ route('account.recovery.show.email') }}">{{ __('Recover your account using your email') }}</a><br />
    @if (space()->phone_registration)
    <i class="ph">phone</i><a href="{{ route('account.recovery.show.phone') }}">{{ __('Recover your account using your phone number') }}</a><br />
    @endif
    <i class="ph">qr-code</i><a href="{{ route('account.authenticate.auth_token') }}">{{ __('Login using a QRCode') }}</a>
</p>