@extends('mails.layout')

@section('content')
# {{ __('Your space :space is expiring in :count days', [ 'space' => $space->name, 'count' => $space->daysLeft]) }}

{{ __('Hello') }},

{{ __('You are one of the administrators of the :space space configured on our service.', ['space' => $space->name]) }}

{{ __('We inform you that this space will expire on :date, in accordance with the expiration date defined in your subscription.',  [ 'date' => $space->expire_at->format('d-m-Y')]) }}.

{{ __('To ensure the continuity of your services (SIP calls, user accounts, configurations, etc.), we recommend renewing or updating your subscription before the expiration date.') }}

{{ __('If you have any questions or need assistance, feel free to contact our support team.') }}

@endsection