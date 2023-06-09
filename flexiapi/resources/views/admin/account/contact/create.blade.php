@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Contacts
</li>
@endsection

@section('content')

<h2>Add a Contact to the Account</h2>

{!! Form::model($account, [
    'route' => ['admin.account.contact.store', $account->id],
    'method' => 'post'
]) !!}
    <div class="form-row">
        <div class="form-group col-md-12">
            {!! Form::label('sip', 'Adresse SIP') !!}
            {!! Form::text('sip', null, ['class' => 'form-control', 'placeholder' => 'username@server.com']); !!}
        </div>
    </div>

{!! Form::submit('Add', ['class' => 'btn btn-success btn-centered']) !!}
{!! Form::close() !!}

@endsection