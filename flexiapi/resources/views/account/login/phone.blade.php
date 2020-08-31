@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        <div class="card mt-3">
            <div class="card-body">
                {!! Form::open(['route' => 'account.authenticate.phone']) !!}
                    <div class="form-group">
                        {!! Form::label('phone', 'Phone') !!}
                        {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '+123456789', 'required']) !!}
                    </div>
                    @include('parts.captcha')
                    {!! Form::submit('Send the authentication code by SMS', ['class' => 'btn btn-primary btn-centered']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @endif
@endsection