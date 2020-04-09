@extends('layouts.main')

@section('content')
    @if (Auth::check())
        <div class="alert alert-primary" role="alert">
            <a class="float-right" href="{{ route('logout') }}">Logout</a>
            You are already authenticated
        </div>
    @else
        <div class="card mt-3">
            <div class="card-body">
                {!! Form::open(['route' => 'account.authenticate']) !!}
                    <div class="form-group">
                        {!! Form::label('username', 'Username') !!}
                        {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username@sip.linphone.org', 'required']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password', 'Password') !!}
                        {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'myPassword', 'required']) !!}
                    </div>
                    {!! Form::submit('Authenticate', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @endif
@endsection