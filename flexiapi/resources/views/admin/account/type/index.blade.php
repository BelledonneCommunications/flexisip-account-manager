@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item" aria-current="page">
        <a href="{{ route('admin.account.type.index') }}">{{ __('Types') }}</a>
    </li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">shapes</i> {{ __('Types') }}</h1>
        <a class="btn oppose" href="{{ route('admin.account.type.create') }}">
            <i class="ph">plus</i>
            {{ __('Create') }}
        </a>
    </header>

    <table>
        <thead>
            <tr>
                <th>{{ __('Key') }}</th>
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
                        <a class="btn" href="{{ route('admin.account.type.edit', [$type->id]) }}">
                            {{ __('Edit') }}
                        </a>
                        <a class="btn btn-secondary" href="{{ route('admin.account.type.delete', [$type->id]) }}">
                            {{ __('Delete') }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
