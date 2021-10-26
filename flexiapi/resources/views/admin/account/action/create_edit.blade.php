@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Actions
</li>
@endsection

@section('content')

@if ($action->id)
    <h2>Edit an account action</h2>
@else
    <h2>Create an account action</h2>
@endif

{!! Form::model($action, [
    'route' => $action->id
        ? ['admin.account.action.update', $action->account->id, $action->id]
        : ['admin.account.action.store', $account->id],
    'method' => $action->id
        ? 'put'
        : 'post'
]) !!}
    <div class="form-row">
        <div class="form-group col-md-12">
            {!! Form::label('key', 'Key') !!}
            {!! Form::text('key', $action->key, ['class' => 'form-control', 'placeholder' => 'action_key']); !!}
        </div>
        <div class="form-group col-md-12">
            {!! Form::label('code', 'Code') !!}
            {!! Form::text('code', $action->code, ['class' => 'form-control', 'placeholder' => '12ab45']); !!}
        </div>
        <div class="form-group col-md-12">
            {!! Form::label('protocol', 'Protocol') !!}
            {!! Form::select('protocol', $protocols, $action->protocol, ['class' => 'form-control']); !!}
        </div>
    </div>

{!! Form::submit(($action->id) ? 'Update' : 'Create', ['class' => 'btn btn-success btn-centered']) !!}
{!! Form::close() !!}

@endsection