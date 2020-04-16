@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
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

                <br />
                <p>You can also authenticate using your <a href="{{ route('account.login_email') }}">Email address</a> or your <a href="{{ route('account.login_phone') }}">Phone number</a></p>
            </div>
        </div>
    @endif
@endsection