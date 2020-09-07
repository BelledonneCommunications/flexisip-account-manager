@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        @if ($account->activated)
            <p>Please enter the PIN code was sent to your phone number to finish your authentication.</p>
        @else
            <p>To finish your registration process and set a password please enter the PIN code you just received by SMS.</p>
        @endif
        <div class="card mt-1">
            <div class="card-body">
                {!! Form::open(['route' => 'account.authenticate.phone_confirm']) !!}
                    <div class="form-group">
                        {!! Form::label('code', 'Code') !!}
                        {!! Form::hidden('account_id', $account->id) !!}
                        {!! Form::text('code', old('code'), ['class' => 'form-control', 'placeholder' => '1234', 'required']) !!}
                    </div>
                    {!! Form::submit('Login', ['class' => 'btn btn-primary btn-centered']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @endif
@endsection