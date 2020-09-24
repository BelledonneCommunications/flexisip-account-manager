@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        {!! Form::open(['route' => 'account.authenticate']) !!}
            <div class="form-row">
                <div class="form-group col-md-6">
                    @if (config('app.phone_authentication'))
                        {!! Form::label('username', 'Username or phone number') !!}
                        {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username or phone number', 'required']) !!}
                    @else
                        {!! Form::label('username', 'Username') !!}
                        {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username', 'required']) !!}
                    @endif
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('password', 'Password') !!}
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'myPassword', 'required']) !!}
                </div>
            </div>

            {!! Form::submit('Login', ['class' => 'btn btn-primary btn-centered mt-1']) !!}

        {!! Form::close() !!}

        @include('parts.password_recovery')
    @endif

    <hr />

    <p class="text-center">
        No account yet?
        <a class="btn btn-secondary ml-2" href="{{ route('account.register') }}">Register</a>
    </p>

@endsection