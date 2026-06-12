@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.show')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.groups.index', $space) }}">{{ __('Call groups') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.groups.edit', [$space, $group]) }}">{{ $group->name }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Add') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-users-three"></i> {{ $group->name }}</h1>

        <a href="{{ route('admin.spaces.groups.edit', [$space, $group]) }}" class="btn secondary oppose">{{ __('Cancel') }}</a>

        <form method="POST" action="{{ route('admin.spaces.groups.accounts.attach', [$space, $group]) }}"
            name="groups_accounts_attach" accept-charset="UTF-8">
            @csrf
            @method('post')

            <select name="account_ids[]" class="list_toggle" data-list-id="a{{ $group->id }}"></select>
            <input type="hidden" name="group_id" value="{{ $group->id }}">
        </form>
    </header>

    <div>
        <form class="inline" method="POST" action="{{ route('admin.spaces.groups.accounts.attach', [$space, $group]) }}"
            accept-charset="UTF-8">
            @csrf
            <div class="search">
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text"
                    value="{{ request()->input('search', '') }}">
                <label for="search">{{ __('Search') }}</label>
            </div>
            <div class="large">
                <button type="submit" class="btn" title="{{ __('Search') }}">
                    <i class="ph ph-magnifying-glass"></i>
                </button>
                <a href="{{ route('admin.spaces.groups.accounts.add', [$space, $group]) }}" type="reset"
                    class="btn secondary">{{ __('Reset') }}</a>
            </div>
            <div class="oppose">
                <a class="btn "
                    onclick="Utils.clearStorageList('a{{ $group->id }}'); document.querySelector('form[name=groups_accounts_attach]').submit()">
                    <i class="ph ph-plus"></i>
                    Add <span class="list_toggle" data-list-id="a{{ $group->id }}"></span> members
                </a>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:1%;">
                    <input type="checkbox" onchange="Utils.toggleAll(this)">
                </th>
                <th>{{ __('Username') }}</th>
                @include('parts.column_sort', [
                    'key' => 'updated_at',
                    'title' => __('Updated'),
                    'uriParams' => ['group' => $group, 'space' => $space],
                ])
            </tr>
        </thead>
        <tbody>
            @if ($accounts->isEmpty())
                <tr class="empty">
                    <td colspan="3">{{ __('Empty') }}</td>
                </tr>
            @endif
            @foreach ($accounts as $account)
                <tr>
                    <td>
                        <input class="list_toggle" type="checkbox" data-list-id="a{{ $group->id }}"
                            data-id="{{ $account->id }}">
                    </td>
                    <td>
                        {{ $account->identifier }}
                    </td>
                    <td>{{ $account->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links('pagination::bootstrap-4') }}
@endsection
