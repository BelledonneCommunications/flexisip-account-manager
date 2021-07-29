@extends('layouts.account')

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
    <h2>Edit an account</h2>
@else
    <h2>Create an account</h2>
@endif

{!! Form::model($account, [
    'route' => $account->id
        ? ['admin.account.update', $account->id]
        : ['admin.account.store'],
    'method' => $account->id
        ? 'put'
        : 'post'
]) !!}
    <div class="form-row">
        <div class="form-group col-md-12">
            {!! Form::label('username', 'Username') !!}
            <div class="input-group">
                {!! Form::text('username', $account->username, ['class' => 'form-control', 'placeholder' => 'Username']); !!}
                <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon1">@ {{ config('app.sip_domain') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            {!! Form::label('password', ($account->id) ? 'Password (fill to change)' : 'Password') !!}
            {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Password']); !!}
            <div class="form-check mt-3">
                {!! Form::checkbox('password_sha256', 'checked', $account->sha256Password, ['class' => 'form-check-input']) !!}
                {!! Form::label('password_sha256', 'Use a SHA-256 encrypted password', ['class' => 'form-check-label']) !!}
            </div>
        </div>
    </div>

    <hr />

    <div class="form-row">
        <div class="form-group col-md-12 mb-0">
            <h4>Optional</h4>
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('email', 'Email') !!}
            {!! Form::email('email', $account->email, ['class' => 'form-control', 'placeholder' => 'Email']); !!}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('phone', 'Phone') !!}
            {!! Form::text('phone', $account->phone, ['class' => 'form-control', 'placeholder' => '+12123123']); !!}
        </div>
    </div>

{!! Form::submit(($account->id) ? 'Update' : 'Create', ['class' => 'btn btn-danger btn-centered']) !!}
{!! Form::close() !!}

@endsection