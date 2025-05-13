@extends('mails.layout')

@section('content')
# {{ $space->name }} is expiring in {{ $space->daysLeft }} days

You are one of the administrator of the {{ $space->name }} space configured on our service.

We inform you that this Space is officialy expiring on **{{ $space->expire_at->format('d-m-Y') }}**.

After that day you and your registered users will not be able to use the features provided by your subscription anymore.

Be sure to renew your subscription if you would like to continue to use our services.

@endsection
