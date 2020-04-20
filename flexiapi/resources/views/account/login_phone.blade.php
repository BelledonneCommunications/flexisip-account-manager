@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        <div class="card mt-3">
            <div class="card-body">
                {!! Form::open(['route' => 'account.authenticate_phone']) !!}
                    <div class="form-group">
                        {!! Form::label('phone', 'Phone') !!}
                        {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '+123456789', 'required']) !!}
                    </div>
                    {!! Form::submit('Send the authentication code by SMS', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @endif
@endsection