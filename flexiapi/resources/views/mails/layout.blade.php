<x-mail::message>
@yield('content')

{{ __('Best regards,') }}

{{ config('mail.signature') }}

<x-mail::panel>

{{ __('Don’t have the app yet?') }} @if (isset($account)) [{{ __('Login to my account with Linphone') }}]({{ $account->provisioning_wizard_url }}) @else [{{ __('Download Linphone')}}](https://www.linphone.org/en/download/) @endif

<br />

{{ __('Need help?') }} [{{ __('Visit our user guide') }}](https://linphone.org/en/docs)

</x-mail::panel>

</x-mail::message>