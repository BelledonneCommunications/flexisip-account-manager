@if(space()->phone_registration)
    @include('parts.tabs', ['items' => [
        route('account.register.phone') => __('Phone registration'),
        route('account.register.email') => __('Email registration'),
    ]])
@endif
