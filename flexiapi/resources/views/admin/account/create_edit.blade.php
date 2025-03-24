@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @if ($account->id)
        @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    @else
        <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
    @endif
@endsection

@section('content')
    @if ($account->id)
        <header>
            <h1><i class="ph">users</i> {{ $account->identifier }}</h1>
            <a class="btn btn-secondary oppose" href="{{ route('admin.account.delete', $account->id) }}">
                <i class="ph">trash</i>
                {{ __('Delete') }}
            </a>
        </header>
        @if ($account->updated_at)
            <p title="{{ $account->updated_at }}">{{ __('Updated on') }} {{ $account->updated_at->format('d/m/Y') }}
        @endif
        @include('admin.account.parts.tabs')
    @else
        <header>
            <h1><i class="ph">users</i> {{ __('Create') }}</h1>
            <a href="{{ route('admin.account.index') }}" class="btn btn-secondary oppose">{{ __('Cancel') }}</a>
            <input form="create_edit" class="btn" type="submit" value="{{ __('Create') }}">
        </header>
    @endif

    <form method="POST"
        action="{{ $account->id ? route('admin.account.update', $account->id) : route('admin.account.store') }}"
        id="create_edit" accept-charset="UTF-8">
        @csrf
        @method($account->id ? 'put' : 'post')

        <div>
            <input placeholder="Username" required="required" name="username" type="text"
                value="@if($account->id){{ $account->username }}@else{{ old('username') }}@endif"
                @if ($account->id) readonly @endif>
            <label for="username">{{ __('Username') }}</label>
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div class="select">
            <select name="domain" @if (auth()->user()?->superAdmin) required @else disabled @endif>
                @foreach ($domains as $space)
                    <option value="{{ $space->domain }}" @if ($account->domain == $space->domain) selected="selected" @endif>
                        {{ $space->domain }}</option>
                @endforeach
            </select>
            <label for="domain">{{ __('Domain') }}</label>
        </div>

        <div>
            <input placeholder="John Doe" name="display_name" type="text"
                value="@if($account->id){{ $account->display_name }}@else{{ old('display_name') }}@endif">
            <label for="display_name">{{ __('Display name') }}</label>
            @include('parts.errors', ['name' => 'display_name'])
        </div>
        <div></div>

        <div>
            <input placeholder="Password" name="password" type="password" value="" autocomplete="new-password"
                @if (!$account->id) required @endif>
            <label for="password">{{ __('Password') }} @if ($account->passwords()->count() > 0) ({{ __('Currently set') }}) @endif</label>
            <small>{{ __('Fill to change') }}</small>
            @include('parts.errors', ['name' => 'password'])
        </div>

        <div>
            <input placeholder="Password" name="password_confirmation" type="password" value="" autocomplete="off"
                @if (!$account->id) required @endif>
            <label for="password_confirmation">{{ __('Confirm password') }}</label>
            @include('parts.errors', ['name' => 'password_confirmation'])
        </div>

        <div>
            <input placeholder="Email" name="email" type="email"
                value="@if($account->id){{ $account->email }}@else{{ old('email') }}@endif">
            <label for="email">{{ __('Email') }}</label>
            @include('parts.errors', ['name' => __('email')])

            @if (!empty($account->email))
                <p class="oppose">
                    <a href="{{ route('admin.account.reset_password_email.create', $account) }}">
                        {{ __('Send an email to the user to reset the password') }}
                    </a>
                </p>
            @endif
        </div>

        <div>
            <input placeholder="+12123123" name="phone" type="text"
                value="@if($account->id){{ $account->phone }}@else{{ old('phone') }}@endif">
            <label for="phone">{{ __('Phone number') }}</label>
            @include('parts.errors', ['name' => 'phone'])
        </div>

        <h3 class="large">{{ __('Other information') }}</h3>

        @include('parts.form.toggle', ['object' => $account, 'key' => 'blocked', 'label' => __('Blocked')])
        @include('parts.form.toggle', ['object' => $account, 'key' => 'activated', 'label' => __('Enabled')])

        <div>
            <input name="role" value="admin" type="radio" @if ($account->admin) checked @endif>
            <p>{{ __('Admin') }}</p>
            <input name="role" value="end_user" type="radio" @if (!$account->admin) checked @endif>
            <p>{{ __('User') }}</p>
            <label>{{ __('Role') }}</label>
        </div>

        @if (space()?->intercom_features)
            <div class="select">
                <select name="dtmf_protocol">
                    @foreach ($protocols as $value => $name)
                        <option value="{{ $value }}" @if ($account->dtmf_protocol == $value) selected="selected" @endif>
                            {{ $name }}</option>
                    @endforeach
                </select>
                <label for="dtmf_protocol">DTMF Protocol</label>
            </div>
        @endif

        <div class="large">
            <input class="btn" type="submit" value="{{ __('Update') }}">
        </div>
    </form>

    <hr class="large">

    @if ($account->id)
        <h2 class="large">{{ __('Contacts') }}</h2>

        @foreach ($account->contacts as $contact)
            <p class="chip">
                <a href="{{ route('admin.account.edit', $account) }}">{{ $contact->identifier }}</a>
                <a href="{{ route('admin.account.contact.delete', [$account, $contact->id]) }}">
                    <i class="ph">x</i>
                </a>
            </p>
        @endforeach

        <a class="btn btn-tertiary" href="{{ route('admin.account.contact.create', $account) }}">{{ __('Add') }}</a>

        <h3 id="contacts_lists">{{ __('Contacts Lists') }}</h3>

        @if ($contacts_lists->isNotEmpty())
            <form method="POST" action="{{ route('admin.account.contacts_lists.attach', $account->id) }}"
                accept-charset="UTF-8">
                @csrf
                @method('post')

                <div class="select">
                    <select name="contacts_list_id" onchange="this.form.submit()">
                        <option>
                            {{ __('Contacts Lists') }}
                        </option>
                        @foreach ($contacts_lists as $contacts_list)
                            <option value="{{ $contacts_list->id }}">
                                {{ $contacts_list->title }}
                            </option>
                        @endforeach
                    </select>
                    <label for="contacts_list_id">{{ __('Add') }}</label>
                </div>
            </form>
            <br />
        @endif

        @foreach ($account->contactsLists as $contactsList)
            <p class="chip">
                <a
                    href="{{ route('admin.contacts_lists.edit', ['contacts_list_id' => $contactsList->id]) }}">{{ $contactsList->title }}</a>
                <a
                    href="{{ route('admin.account.contacts_lists.detach', ['account_id' => $account->id, 'contacts_list_id' => $contactsList->id]) }}">
                    <i class="ph">x</i>
                </a>
            </p>
        @endforeach

        <br />

        <h2 class="large" id="provisioning">{{ __('Provisioning') }}</h2>

        @if ($account->provisioning_token)
            <div>
                <img style="max-width: 15rem;" src="{{ route('provisioning.qrcode', $account->provisioning_token) }}">
            </div>

            <form class="inline">
                <div>
                    <input type="text" style="min-width: 40rem;" readonly
                        value="{{ route('provisioning.provision', $account->provisioning_token) }}">
                    <small>{{ __('The link can only be visited once') }}</small>
                </div>
                <div>
                    <a class="btn" href="{{ route('admin.account.provision', $account->id) }}">{{ __('Renew') }}</a>
                </div>
            </form>
        @else
            <a class="btn btn-light" href="{{ route('admin.account.provision', $account->id) }}">{{ __('Create') }}</a>
        @endif

        @if (space()?->intercom_features))
            <h2>{{ __('Actions') }}</h2>

            @if ($account->dtmf_protocol)
                <table>
                    <tbody>
                        @foreach ($account->actions as $action)
                            <tr>
                                <th scope="row">{{ $action->key }}</th>
                                <td>{{ $action->code }}</td>
                                <td>
                                    <a class="btn"
                                        href="{{ route('admin.account.action.edit', [$account, $action->id]) }}">{{ __('Edit') }}</a>
                                    <a class="btn"
                                        href="{{ route('admin.account.action.delete', [$account, $action->id]) }}">{{ __('Delete') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <a class="btn" href="{{ route('admin.account.action.create', $account) }}">{{ __('Add') }}</a>
            @else
                <p>To manage actions, you must configure the DTMF protocol in the account settings.</p>
            @endif

            <h2>{{ __('Types') }}</h2>

            <table>
                <tbody>
                    @foreach ($account->types as $type)
                        <tr>
                            <th scope="row">{{ $type->key }}</th>
                            <td>
                                <form method="POST"
                                    action="{{ route('admin.account.account_type.destroy', [$account, $type->id]) }}"
                                    accept-charset="UTF-8">
                                    @csrf
                                    @method('delete')
                                    <input class="btn" type="submit" value="{{ __('Delete') }}">
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <a class="btn" href="{{ route('admin.account.account_type.create', $account) }}">{{ __('Add') }}</a>
        @endif
    @endif
@endsection
