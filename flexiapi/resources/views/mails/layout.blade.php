<x-mail::message>
@yield('content')

Regards,

{{ config('mail.signature') }}

<x-mail::panel>
You don’t have the app yet? [Download Linphone](https://www.linphone.org/en/docs/install-linphone/)

Need help? [Visit our user guide](https://linphone.org/en/docs)
</x-mail::panel>

</x-mail::message>
