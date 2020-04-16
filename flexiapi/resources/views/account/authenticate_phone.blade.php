@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        <div class="card mt-3">
            <div class="card-body">
                {!! Form::open(['route' => 'account.authenticate_phone_confirm']) !!}
                    <div class="form-group">
                        {!! Form::label('code', 'Code') !!}
                        {!! Form::hidden('account_id', $account->id) !!}
                        {!! Form::text('code', old('code'), ['class' => 'form-control', 'placeholder' => '1234', 'required']) !!}
                    </div>
                    {!! Form::submit('Authenticate', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @endif
@endsection