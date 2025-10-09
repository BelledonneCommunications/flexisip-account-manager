@extends('mails.layout')

@section('content')
# {{ __('New voice message from :sipfrom', ['sipfrom' => $accountFile->sip_from]) }}

{{ __('New voice message') }}

{{ __('From') }}: {{ $accountFile->sip_from }}

{{ __('To') }}: {{ $accountFile->account->identifier }}

{{ __('Recorded at') }}: {{ $accountFile->created_at->toDateTimeString() }}

@endsection
