@extends('mails.layout')

@section('content')
Hello,

The following email address wants to register to the mailing list: {{ $account->email }}.
@endsection
