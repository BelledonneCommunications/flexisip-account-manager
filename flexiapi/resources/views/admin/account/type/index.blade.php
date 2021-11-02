@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.type.index') }}">Types</a>
</li>
@endsection

@section('content')

<div class="row mb-2">
    <div class="col-sm">
        <a class="btn btn-success float-right" href="{{ route('admin.account.type.create') }}">Create</a>
        <h2>Types</h2>
    </div>
</div>

<table class="table table-responsive-md">
    <thead>
        <tr>
            <th scope="col">Key</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($types as $type)
            <tr>
                <td>
                    {{ $type->key }}
                </td>
                <td>
                    <a class="btn btn-sm mr-2" href="{{ route('admin.account.type.edit', [$type->id]) }}">Edit</a>
                    <a class="btn btn-sm mr-2" href="{{ route('admin.account.type.delete', [$type->id]) }}">Delete</a>
                </td>
            </tr>
        @endforeach

    </tbody>
</table>

@endsection