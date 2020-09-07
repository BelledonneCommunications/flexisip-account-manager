@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        <div class="card mt-3">
            <div class="card-body">
                {!! Form::open(['route' => 'account.authenticate.email']) !!}
                    <div class="form-group">
                        {!! Form::label('email', 'Email') !!}
                        {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => 'bob@example.com', 'required']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('username', 'SIP Username') !!}
                        <div class=" input-group mb-3">
                            {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username', 'required']) !!}
                            <div class="input-group-append">
                                <span class="input-group-text" id="basic-addon2">{{ $domain }}</span>
                            </div>
                        </div>
                    </div>
                    @include('parts.captcha')
                    {!! Form::submit('Send the authentication link', ['class' => 'btn btn-primary btn-centered']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @endif
@endsection