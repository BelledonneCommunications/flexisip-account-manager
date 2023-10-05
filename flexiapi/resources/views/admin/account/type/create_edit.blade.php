@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.type.index') }}">Types</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        @if ($type->id)Edit @else Create @endif
    </li>
@endsection

@section('content')
    @if ($type->id)
        <h2>Edit an account type</h2>
    @else
        <h2>Create an account type</h2>
    @endif

    <form method="POST"
        action="{{ $type->id ? route('admin.account.type.update', $type->id) : route('admin.account.type.store') }}"
        accept-charset="UTF-8">
        @method($type->id ? 'put' : 'post')
        @csrf
        <div>
            <input type="text" name="key" value="{{ $type->key }}" placeholder="type_key">
            <label for="key">Key</label>
        </div>

        <div>
            <input class="btn btn-success" type="submit" value="{{ $type->id ? 'Update' : 'Create' }}">
        </div>
    </form>
@endsection
