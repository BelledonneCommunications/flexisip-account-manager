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
                        {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => 'myemail@address.org', 'required']) !!}
                    </div>
                    @include('parts.captcha')
                    {!! Form::submit('Send the authentication link', ['class' => 'btn btn-primary btn-centered']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @endif
@endsection