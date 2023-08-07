@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('admin.account.type.index') }}">Types</a>
</li>
@endsection

@section('content')

@if ($type->id)
    <h2>Edit an account type</h2>
@else
    <h2>Create an account type</h2>
@endif

{!! Form::model($type, [
    'route' => $type->id
        ? ['admin.account.type.update', $type->id]
        : ['admin.account.type.store'],
    'method' => $type->id
        ? 'put'
        : 'post'
]) !!}
    <div class="form-row">
        <div class="form-group col-md-12">
            {!! Form::label('key', 'Key') !!}
            {!! Form::text('key', $type->key, ['class' => 'form-control', 'placeholder' => 'type_key']); !!}
        </div>
    </div>

{!! Form::submit(($type->id) ? 'Update' : 'Create', ['class' => 'btn btn-success btn-centered']) !!}
{!! Form::close() !!}

@endsection