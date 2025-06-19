@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item" aria-current="page">
        <a href="{{ route('admin.account.type.index') }}">{{ __('Types') }}</a>
    </li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-shapes"></i> {{ __('Types') }}</h1>
        <a class="btn oppose" href="{{ route('admin.account.type.create') }}">
            <i class="ph ph-plus"></i>
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
                    <td class="actions">
                        <a class="btn small secondary" href="{{ route('admin.account.type.edit', [$type->id]) }}">
                            <i class="ph ph-pencil"></i>
                        </a>
                        <a class="btn tertiary small" href="{{ route('admin.account.type.delete', [$type->id]) }}">
                            <i class="ph ph-trash"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
