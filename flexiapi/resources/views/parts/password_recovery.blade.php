<p class="text-center pt-3">
    Set or recover your password using your <a href="{{ route('account.login_email') }}">Email address</a>
    @if (config('app.phone_authentication'))
        or your <a href="{{ route('account.login_phone') }}">Phone number</a>
    @endif
</p>