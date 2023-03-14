@if (config('app.web_panel'))
    <p class="text-center pt-3">
        @if (config('app.account_email_unique'))
            Set or recover your account
        @else
            Set or recover your password
        @endif
            using your <a href="{{ route('account.login_email') }}">Email address</a>
        @if (config('app.phone_authentication'))
            or your <a href="{{ route('account.login_phone') }}">Phone number</a>
        @endif
    </p>
    <p class="text-center">
        …or login using an already authenticated device <a href="{{ route('account.authenticate.auth_token') }}">by flashing a QRcode</a>.
    </p>
@endif