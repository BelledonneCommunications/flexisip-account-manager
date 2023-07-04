@extends('layouts.main')

@section('content')
    <h2>Delete an account</h2>

    {!! Form::open(['route' => 'admin.account.destroy', 'method' => 'delete']) !!}

    <div class="large">
        <p>You are going to permanently delete the following account. Please confirm your action.<br />
            <b>{{ $account->identifier }}</b>
        </p>

        {!! Form::hidden('account_id', $account->id) !!}
    </div>
    <div>
        {!! Form::submit('Delete', ['class' => 'btn']) !!}

    </div>

    {!! Form::close() !!}
@endsection
