@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
    @if ($account->id)
        <li class="breadcrumb-item" aria-current="page">
            <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->identifier }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Edit
        </li>
    @else
        <li class="breadcrumb-item active" aria-current="page">
            Create
        </li>
    @endif
@endsection

@section('content')

@if ($account->id)
    <h1>Edit an account</h1>
@else
    <h1>Create an account</h1>
@endif

{!! Form::model($account, [
    'route' => $account->id
        ? ['admin.account.update', $account->id]
        : ['admin.account.store'],
    'method' => $account->id
        ? 'put'
        : 'post'
]) !!}
    <div>
        {!! Form::text('username', $account->username, ['placeholder' => 'Username', 'required' => 'required']); !!}
        {!! Form::label('username', 'Username') !!}
    </div>
    <div>
        @if (config('app.admins_manage_multi_domains'))
            {!! Form::text('domain', $account->domain ?? config('app.sip_domain'), ['placeholder' => 'domain.com', 'required' => 'required']); !!}
        @else
            {!! Form::text('domain', $account->domain ?? config('app.sip_domain'), ['placeholder' => 'domain.com', 'disabled']); !!}
        @endif
        {!! Form::label('domain', 'Domain') !!}
    </div>

    <div>
        {!! Form::password('password', ['placeholder' => 'Password', 'required']); !!}
        {!! Form::label('password', ($account->id) ? 'Password (fill to change)' : 'Password') !!}
    </div>
    <div>
        {!! Form::checkbox('password_sha256', 'checked', $account->sha256Password) !!}
        {!! Form::label('password_sha256', 'Use a SHA-256 encrypted password') !!}
    </div>

    <div>
        {!! Form::email('email', $account->email, ['placeholder' => 'Email']); !!}
        {!! Form::label('email', 'Email') !!}
    </div>

    <div>
        {!! Form::text('display_name', $account->display_name, ['placeholder' => 'John Doe']); !!}
        {!! Form::label('display_name', 'Display Name') !!}
    </div>

    <div>
        {!! Form::text('phone', $account->phone, ['placeholder' => '+12123123']); !!}
        {!! Form::label('phone', 'Phone') !!}
    </div>

    <div class="select">
        {!! Form::select('dtmf_protocol', $protocols, $account->dtmf_protocol); !!}
        {!! Form::label('dtmf_protocol', 'DTMF Protocol') !!}
    </div>

    <hr />
    <div>
        {!! Form::submit(($account->id) ? 'Update' : 'Create', ['class' => 'btn oppose']) !!}
    </div>

{!! Form::close() !!}

@endsection