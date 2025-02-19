@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Accounts') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">users</i> {{ __('Accounts') }}</h1>
        @if ($space)
            <p>{{ $accounts->count()}} / @if ($space->max_accounts > 0){{ $space->max_accounts }} @else <i class="ph">infinity</i>@endif</p>
        @endif
        <a class="btn btn-secondary oppose" href="{{ route('admin.account.import.create') }}">
            <i class="ph">download-simple</i>
            {{ __('Import') }}
        </a>
        @if (space()?->intercom_features)
        <a class="btn btn-secondary" href="{{ route('admin.account.type.index') }}">
            <i class="ph">shapes</i>
            {{ __('Types') }}
        </a>
        @endif
        <a class="btn" href="{{ route('admin.account.create') }}">
            <i class="ph">plus</i>
            {{ __('Create') }}
        </a>
    </header>
    <div>
        <form class="inline" method="POST" action="{{ route('admin.account.search') }}" accept-charset="UTF-8">
            @csrf
            <input type="hidden" name="order_by" value="{{ request()->get('order_by', '') }}">
            <input type="hidden" name="order_sort" value="{{ request()->get('order_sort', '') }}">
            <div class="search large">
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text"
                    value="{{ request()->get('search', '') }}">
                <label for="search">{{ __('Search') }}</label>
            </div>
            <div class="large on_desktop"></div>
            @include('admin.account.parts.forms.select_domain')
            <div class="select">
                <select name="contacts_list" onchange="this.form.submit()">
                    <option value="">
                        {{ __('Select a contacts list') }}
                    </option>
                    @foreach ($contacts_lists as $key => $name)
                        <option value="{{ $key }}" @if (request()->get('contacts_list', '') == $key) selected="selected" @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <label for="domain">{{ __('Contacts List') }}</label>
            </div>
            <div>
                <input name="updated_date" type="date" value="{{ request()->get('updated_date', '') }}"
                    onchange="this.form.submit()">
                <label for="updated_date">{{ __('Updated on') }}</label>
            </div>
            <div class="oppose">
                <a href="{{ route('admin.account.index') }}" type="reset" class="btn btn-tertiary">{{ __('Reset') }}</a>
                <button type="submit" class="btn">{{ __('Search') }}</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                @include('parts.column_sort', ['key' => 'username', 'title' => __('Identifier')])
                <th>{{ __('Contacts Lists') }}</th>
                <th>Badges</th>
                @include('parts.column_sort', ['key' => 'updated_at', 'title' => __('Updated')])
            </tr>
        </thead>
        <tbody>
            @if ($accounts->isEmpty())
                <tr class="empty">
                    <td colspan="4">{{ __('Empty') }}</td>
                </tr>
            @endif
            @foreach ($accounts as $account)
                <tr>
                    <td>
                        <a href="{{ route('admin.account.edit', $account->id) }}">
                            {{ $account->identifier }}
                        </a>
                    </td>
                    <td>
                        @if ($account->contactsLists->isNotEmpty())
                            {{ $account->contactsLists->first()->title }}
                            @if ($account->contactsLists->count() > 1)
                                <span class="badge">+{{ $account->contactsLists->count() - 1 }}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if ($account->activated)
                            <span class="badge badge-success" title="Activated">Act.</span>
                        @endif
                        @if ($account->superAdmin)
                            <span class="badge badge-error" title="Admin">Super Adm.</span>
                        @elseif ($account->admin)
                            <span class="badge badge-primary" title="Admin">Adm.</span>
                        @endif
                        @if ($account->sha256Password)
                            <span class="badge badge-info">SHA256</span>
                        @endif
                        @if ($account->blocked)
                            <span class="badge badge-error">{{ __('Blocked') }}</span>
                        @endif
                    </td>
                    <td>{{ $account->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links('pagination::bootstrap-4') }}
@endsection
