@extends('layouts.main', ['welcome' => true])

@section('content')

<section>
    <h1><i class="material-icons">account_circle</i> Account recovery</h1>
    {!! Form::open(['route' => 'account.recovery.confirm']) !!}
        <p class="large">Enter the pin code you received to recover your account.</p>
        <div class="large">
            {!! Form::text('code', old('code'), ['placeholder' => '1234', 'required']) !!}
            {!! Form::label('code', 'Code') !!}
            {!! Form::hidden('account_id', $account_id) !!}
        </div>
        <div class="large">
            {!! Form::submit('Login', ['class' => 'btn oppose']) !!}
        </div>
    {!! Form::close() !!}
</section>
<section class="on_desktop">
    <img src="/img/lock.svg">
</section>
@endsection