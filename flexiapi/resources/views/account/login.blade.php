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
                        {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username@'.config('app.sip_domain'), 'required']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password', 'Password') !!}
                        {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'myPassword', 'required']) !!}
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                        {!! Form::submit('Authenticate', ['class' => 'btn btn-primary']) !!}
                        </div>

                        <div class="form-group col-md-6">
                            <p class="mb-1 text-right">
                                No account yet?
                                <a class="btn btn-secondary ml-2" href="{{ route('account.register') }}">Register
                            </a>
                        </p>
                        </div>
                    </div>
                {!! Form::close() !!}

                <p>You can also authenticate using your <a href="{{ route('account.login_email') }}">Email address</a> or your <a href="{{ route('account.login_phone') }}">Phone number</a></p>
            </div>
        </div>
    @endif
@endsection