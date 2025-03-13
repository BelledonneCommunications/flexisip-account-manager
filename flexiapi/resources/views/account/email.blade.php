@extends('layouts.main')

@section('content')

<h2>{{ __('Change your email') }}</h2>

@if (!empty($account->email))
    <p>Currently: {{ $account->email }}</p>
@else
    <p>No email yet</p>
@endif

<form method="POST" action="{{ route('account.email.request_update') }}" accept-charset="UTF-8">
@csrf

<div>
   <input type="email" name="email" value="{{ old('email') }}" placeholder="bob@example.net" required>
    <label for="email">{{ __('Email') }}</label>
</div>
<div>
   <input type="email" name="email_confirmation" value="{{ old('email_confirm') }}" placeholder="bob@example.net" required>
    <label for="email_confirmation">{{ __('Confirm email') }}</label>
</div>

<input name="email_current" type="hidden" value="{{ $account->email }}">

<input class="btn btn-primary" type="submit" value="Change">
</form>

@endsection