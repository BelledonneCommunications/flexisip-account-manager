@if(space()->phone_registration)
    @include('parts.tabs', ['items' => [
        route('account.register.phone') => __('By phone'),
        route('account.register.email') => __('By email'),
    ]])
@endif
