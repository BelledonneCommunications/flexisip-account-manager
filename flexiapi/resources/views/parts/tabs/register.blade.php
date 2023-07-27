@if(config('app.phone_authentication'))
    @include('parts.tabs', ['items' => [
        route('account.register.phone') => 'Phone registration',
        route('account.register.email') => 'Email registration',
    ]])
@endif
