@extends('layouts.main', ['welcome' => true])

@section('content')
    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('account.creation_request_token.validate') }}" accept-charset="UTF-8">
@csrf

                <input name="account_creation_request_token" type="hidden" value="{{ $account_creation_request_token->token }}">
                @include('parts.captcha')
                <input class="btn btn-primary" type="submit" value="I'm not a robot">
            </form>
        </div>
    </div>
@endsection
