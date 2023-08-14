@extends('layouts.main', ['welcome' => true])

@section('content')

<section>
    <h1><i class="material-icons-outlined">account_circle</i> Account recovery</h1>
    <form method="POST" action="{{ route('account.recovery.confirm') }}" accept-charset="UTF-8">
@csrf

        <p class="large">Enter the pin code you received to recover your account.</p>
        <div class="large">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" autofocus class="digit" name="number_1" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" class="digit" name="number_2" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" class="digit" name="number_3" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" class="digit" name="number_4" type="number" min="0" max="9">
            @include('parts.errors', ['name' => 'code'])

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