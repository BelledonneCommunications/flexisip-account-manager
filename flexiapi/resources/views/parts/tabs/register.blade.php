@if(config('app.phone_authentication'))
    @include('parts.tabs', ['items' => [
        'account.register.email' => 'Email registration',
        'account.register.phone' => 'Phone registration',
    ]])
@endif
