@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.show')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.groups.index', $space) }}">{{ __('Call groups') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        @if ($group->id)
            {{ __('Edit') }}
        @else
            {{ __('New group call') }}
        @endif
    </li>
@endsection

@section('content')
    @include('admin.space.head')

    <header>
        @if ($group->id)
            <h1><i class="ph ph-phone-incoming"></i> {{ $group->name }}</h1>
            <a class="btn secondary oppose" title="{{ __('Delete') }}"
                href="{{ route('admin.spaces.groups.delete', [$space, $group]) }}">
                <i class="ph ph-trash"></i>
            </a>
            <input form="create_edit_contacts_list" class="btn" type="submit" value="{{ __('Update') }}">
        @else
            <h1><i class="ph ph-phone-incoming"></i> {{ __('New group call') }}</h1>
            <input form="create_edit_contacts_list" class="btn oppose" type="submit" value="{{ __('Create') }}">
        @endif
    </header>

    @if ($group->id)
        <p title="{{ $group->updated_at }}">{{ __('Updated on') }} {{ $group->updated_at->format('d/m/Y') }}
    @endif

    <form method="POST" id="create_edit_contacts_list" 
        action="{{ $group->id ? route('admin.spaces.groups.update', [$space, $group->id]) : route('admin.spaces.groups.store', $space) }}" 
        accept-charset="UTF-8"
    >
        @csrf
        @method($group->id ? 'put' : 'post')
        <div>
            <input placeholder="{{ __('Name') }}" required="required" name="name" type="text" value="{{ $group->name ?? old('name') }}">
            <label for="name">{{ __('Group name') }}</label>
            @include('parts.errors', ['name' => 'name'])
        </div>

        <div>
            <input placeholder="ex : 200, support, sales..." @if($group->id) disabled @endif name="username" type="text" value="{{ $group->username ?? old('username') }}" required>
            <label for="username">{{ __('SIP Identifier') }}</label>
            @include('parts.errors', ['name' => 'username'])
            <span class="supporting">{{ __('Identité SIP finale : sip:<identifiant>@') . $space->domain }}</span>
        </div>

        <div class="select">
            <select name="strategy">
                @foreach (App\Group::GROUP_STRATEGIES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <label for="strategy">{{ __('Strategy') }}</label>
            @include('parts.errors', ['name' => 'strategy'])
        </div>
    </form>

    @if ($group->id)
        <hr>
        <form method="POST" action="{{ route('admin.spaces.groups.accounts.detach', [$space, $group]) }}" name="groups_accounts_destroy" accept-charset="UTF-8">
            @csrf
            @method('delete')

            <select name="account_ids[]" class="list_toggle" data-list-id="d{{ $group->id }}"></select>
            <input type="hidden" name="group_id" value="{{ $group->id }}">
        </form>

        <form method="GET" action="{{ route('admin.spaces.groups.edit', [$space, $group]) }}" name="groups_contacts_search" accept-charset="UTF-8">
            @csrf
            <div>
                <h2 class="center-flex"><i class="ph ph-users-three"></i>{{ __('Members') }} @if (count($accounts) == 0)
                    <span class="badge badge-error">Aucun members</span>
                @else
                    <span class="badge badge-success">{{ count($accounts) }}</span>
                @endif
                </h2>

            </div>
            <div class="center-flex oppose">
                <a class="btn secondary oppose" href="{{ route('admin.spaces.groups.accounts.add', [$space, $group]) }}">
                    <i class="ph ph-plus"></i> {{ __('Add members') }}
                </a>
            </div>
            <div class="search">
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text" value="{{ request()->input('search', '') }}">
                <label for="search">{{ __('Search') }}</label>
            </div>
            <div class="center-flex">
                <button type="submit" class="btn" title="{{ __('Search') }}"><i class="ph ph-magnifying-glass"></i></button>
                <a href="{{ route('admin.spaces.groups.edit', [$space, $group]) }}" type="reset" class="btn secondary oppose">{{ __('Reset') }}</a>
            </div>
        </form>
        <br/>
        <table class="large">
            <thead>
                <tr>
                    <th style="width:1%;"><input type="checkbox" onchange="Utils.toggleAll(this)"></th>
                    <th>{{ __('Username') }}</th>
                    <th>{{ __('SIP Adress') }}
                        <a class="btn small tertiary oppose"
                            onclick="Utils.clearStorageList('d{{ $group->id }}'); document.querySelector('form[name=groups_accounts_destroy]').submit()">
                            <i class="ph ph-trash"></i>
                            {{ __('Remove') }} <span class="list_toggle" data-list-id="d{{ $group->id }}"></span>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @if ($accounts->isEmpty())
                    <tr class="empty">
                        <td colspan="3">{{ __('No members — add some to activate this group') }}</td>
                    </tr>
                @endif
                @foreach ($accounts as $account)
                    <tr>
                        <td><input class="list_toggle" type="checkbox" data-list-id="d{{ $group->id }}" data-id="{{ $account->id }}"></td>
                        <td>{{ $account->username }}</td>
                        <td>{{ $account->identifier }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection