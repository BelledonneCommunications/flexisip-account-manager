@extends('layouts.main')

@section('content')
    <header>
        <h1><i class="ph">users</i> {{ __('Users') }}</h1>
        @if ($space)
            <p>{{ $accounts->count()}} / @if ($space->max_accounts > 0){{ $space->max_accounts }} @else <i class="ph">infinity</i>@endif</p>
        @endif
        <a class="btn secondary oppose" href="{{ route('admin.account.import.create') }}">
            <i class="ph">upload-simple</i>
            {{ __('Import') }}
        </a>
        @if (space()?->intercom_features)
        <a class="btn secondary" href="{{ route('admin.account.type.index') }}">
            <i class="ph">shapes</i>
            {{ __('Types') }}
        </a>
        @endif
        <a class="btn" @if ($space && $space->isFull())disabled @endif href="{{ route('admin.account.create') }}">
            <i class="ph">user-plus</i>
            {{ __('New user') }}
        </a>
    </header>
    <div>
        <form class="inline" method="POST" action="{{ route('admin.account.search') }}" accept-charset="UTF-8">
            @csrf
            <input type="hidden" name="order_by" value="{{ request()->get('order_by', '') }}">
            <input type="hidden" name="order_sort" value="{{ request()->get('order_sort', '') }}">
            <div class="search large">
                <input placeholder="{{ __('Search by username') }}" name="search" type="text"
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
                <a href="{{ route('admin.account.index') }}" type="reset" class="btn tertiary">{{ __('Reset') }}</a>
                <button type="submit" class="btn">{{ __('Search') }}</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                @include('parts.column_sort', ['key' => 'username', 'title' => __('Identifier')])
                <th></th>
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
                    <td style="width: 50%">
                        <a href="{{ route('admin.account.show', $account->id) }}">
                            {{ $account->identifier }}
                        </a>
                    </td>
                    <td>
                        @include('admin.account.parts.badges', ['account' => $account])
                    </td>
                    <td>{{ $account->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links('pagination::bootstrap-4') }}
@endsection
