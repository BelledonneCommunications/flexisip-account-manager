@extends('layouts.main', ['welcome' => true])

@section('content')
    <div class="card mt-3">
        <div class="card-body">
            {!! Form::open(['route' => 'account.creation_request_token.validate']) !!}
                {!! Form::hidden('account_creation_request_token', $account_creation_request_token->token) !!}
                @include('parts.captcha')
                {!! Form::submit('I\'m not a robot', ['class' => 'btn btn-primary btn-centered']) !!}
            {!! Form::close() !!}
        </div>
    </div>
@endsection