@extends('layouts.main', ['welcome' => true])

@section('content')

<section>
    <h1><i class="material-icons">account_circle</i> Account recovery</h1>
    <form method="POST" action="{{ route('account.recovery.confirm') }}" accept-charset="UTF-8">
@csrf

        <p class="large">Enter the pin code you received to recover your account.</p>
        <div class="large">
            <input placeholder="1234" name="code" type="text" value="{{ old('code') }}">
            <label for="code">Code</label>
            <input name="account_id" type="hidden" value="{{ $account_id }}">
        </div>
        <div class="large">
            <input class="btn oppose" type="submit" value="Login">
        </div>
    </form>
</section>
<section class="on_desktop">
    <img src="/img/lock.svg">
</section>
@endsection