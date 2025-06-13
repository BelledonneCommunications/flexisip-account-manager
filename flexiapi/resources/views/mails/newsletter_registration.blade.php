@extends('mails.layout')

@section('content')
{{ __('Hello') }},

{{ __('The following email address wants to register to the mailing list:') }} {{ $account->email }}.
@endsection
