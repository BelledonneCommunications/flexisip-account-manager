<p class="text-center pt-3">
    @if (config('app.account_email_unique'))
        Set or recover your account
    @else
        Set or recover your password
    @endif
        using your <a href="{{ route('account.recovery.show.email') }}">{{ __('Email') }}</a>
    @if (space()->phone_registration)
        or your <a href="{{ route('account.recovery.show.phone') }}">{{ __('Phone number') }}</a>
    @endif
</p>
<p class="text-center">
    …or login using an already authenticated device <a href="{{ route('account.authenticate.auth_token') }}">by flashing a QRcode</a>.
</p>