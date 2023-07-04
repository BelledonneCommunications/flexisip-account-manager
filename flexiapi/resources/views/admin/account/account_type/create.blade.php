@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.edit', $account->id) }}">{{ $account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Types
</li>
@endsection

@section('content')

<h2>Add a Type to the Account</h2>

@if ($account_types->count() == 0)
    <p>
        No Account Type to add
    </p>
@else

{!! Form::model($account, [
    'route' => ['admin.account.account_type.store', $account->id],
    'method' => 'post'
]) !!}
    <div class="form-row">
        <div class="form-group col-md-12">
            {!! Form::label('account_type_id', 'Account Type') !!}
            {!! Form::select('account_type_id', $account_types, null, ['class' => 'form-control']); !!}
        </div>
    </div>

{!! Form::submit('Add', ['class' => 'btn btn-success btn-centered']) !!}
{!! Form::close() !!}

@endif

@endsection