@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.show', $account) }}">{{ $account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Contacts
</li>
<li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')

<h2>Delete an account contact</h2>

{!! Form::open(['route' => ['admin.account.contact.destroy', $account], 'method' => 'delete']) !!}

<p>You are going to remove the following contact from the contact list. Please confirm your action.</p>
<p><b>{{ $contact->identifier }}</b></p>

{!! Form::hidden('account_id', $account->id) !!}
{!! Form::hidden('contact_id', $contact->id) !!}

{!! Form::submit('Remove', ['class' => 'btn btn-danger btn-centered']) !!}
{!! Form::close() !!}

@endsection