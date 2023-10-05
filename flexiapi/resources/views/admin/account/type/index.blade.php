@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item" aria-current="page">
        <a href="{{ route('admin.account.type.index') }}">Types</a>
    </li>
@endsection

@section('content')
    <header>
        <h1><i class="material-icons-outlined">category</i> Types</h1>
        <a class="btn oppose" href="{{ route('admin.account.type.create') }}">
            <i class="material-icons-outlined">add</i>
            New Type
        </a>
    </header>

    <table class="table">
        <thead>
            <tr>
                <th>Key</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($types as $type)
                <tr>
                    <td>
                        {{ $type->key }}
                    </td>
                    <td>
                        <a class="btn" href="{{ route('admin.account.type.edit', [$type->id]) }}">Edit</a>
                        <a class="btn btn-secondary" href="{{ route('admin.account.type.delete', [$type->id]) }}">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
