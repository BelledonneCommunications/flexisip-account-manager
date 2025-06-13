<x-mail::message>
@yield('content')

{{ __('Best regards,') }}

{{ config('mail.signature') }}

<x-mail::panel>

{{ __('Don’t have the app yet?') }} [{{ __('Download Linphone')}}](https://www.linphone.org/en/download/)

{{ __('Need help?') }} [{{ __('Visit our user guide') }}](https://linphone.org/en/docs)

</x-mail::panel>

</x-mail::message>