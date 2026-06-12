@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.show')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Call groups') }}</li>
@endsection

@section('content')
    @include('admin.space.head')
    @include('admin.space.tabs')

    <header>
        <h1><i class="ph ph-phone-incoming"></i> {{ __('Call groups') }}</h1>
        <a class="btn oppose" href="{{ route('admin.spaces.groups.create', $space) }}">
            <i class="ph ph-plus"></i>
            {{ __('New group') }}
        </a>
    </header>
    <table>
        <thead>
            <tr>
                @include('parts.column_sort', ['key' => 'name', 'title' => __('Name')])
                @include('parts.column_sort', ['key' => 'username', 'title' => __('SIP identity')])
                @include('parts.column_sort', ['key' => 'strategy', 'title' => __('Strategy')]) 
                @include('parts.column_sort', ['key' => 'accounts_count', 'title' => __('Members')])
                @include('parts.column_sort', ['key' => 'updated_at', 'title' => __('Updated')])
            </tr>
        </thead>
        <tbody>
            @if ($groups->isEmpty())
                <tr class="empty">
                    <td colspan="5">{{ __('Empty') }}</td>
                </tr>
            @endif
            @foreach ($groups as $group)
                <tr>
                    <td>
                        <a href="{{ route('admin.spaces.groups.edit', [$space, $group->id]) }}">
                            {{ $group->name }}
                        </a>
                    </td>

                    <td>{{ $group->username . '@' . $space->domain }}</td>
                    <td>{{ $group->strategy }}</td>
                    <td>{{ $group->accounts_count }}</td>
                    <td>{{ $group->updated_at}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $groups->links('pagination::bootstrap-4') }}

@endsection