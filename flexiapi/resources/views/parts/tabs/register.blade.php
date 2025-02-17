@if(space()->phone_registration)
    @include('parts.tabs', ['items' => [
        route('account.register.phone') => 'Phone registration',
        route('account.register.email') => 'Email registration',
    ]])
@endif
