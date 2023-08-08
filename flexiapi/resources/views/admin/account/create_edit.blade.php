@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    @if ($account->id)
        <header>
            <h1><i class="material-icons-outlined">people</i> {{ $account->identifier }}</h1>
            <a href="{{ route('admin.account.index') }}" class="btn btn-secondary oppose">Cancel</a>
            <a class="btn btn-secondary" href="{{ route('admin.account.delete', $account->id) }}">
                <i class="material-icons-outlined">delete</i>
                Delete
            </a>
            <input form="create_edit" class="btn" type="submit" value="Update">
        </header>
        <p title="{{ $account->updated_at }}">Updated on {{ $account->updated_at->format('d/m/Y') }}

            @include('parts.tabs', [
                'items' => [
                    route('admin.account.edit', $account->id, ['type' => 'messages']) => 'Information',
                    route('admin.account.device.index', $account->id, ['type' => 'accounts']) => 'Devices',
                ],
            ])
        @else
        <header>
            <h1><i class="material-icons-outlined">people</i> Create an account</h1>
            <a href="{{ route('admin.account.index') }}" class="btn btn-secondary oppose">Cancel</a>
            <input form="create_edit" class="btn" type="submit" value="Create">
        </header>
    @endif

    <form method="POST"
        action="{{ $account->id ? route('admin.account.update', $account->id) : route('admin.account.store') }}"
        id="create_edit" accept-charset="UTF-8">
        @csrf
        @method($account->id ? 'put' : 'post')
        <h2>Connexion</h2>
        <div>
            <input placeholder="Username" required="required" name="username" type="text"
                value="{{ $account->username }}" @if ($account->id) readonly @endif>
            <label for="username">Username</label>
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div>
            <input placeholder="domain.com" @if (config('app.admins_manage_multi_domains')) required @else disabled @endif name="domain"
                type="text" value="{{ $account->domain ?? config('app.sip_domain') }}"
                @if ($account->id) readonly @endif>
            <label for="domain">Domain</label>
        </div>

        <div>
            <input placeholder="John Doe" name="display_name" type="text" value="{{ $account->display_name }}">
            <label for="display_name">Display Name</label>
            @include('parts.errors', ['name' => 'display_name'])
        </div>
        <div></div>

        <div>
            <input placeholder="Password" name="password" type="password" value="" autocomplete="new-password">
            <label for="password">{{ $account->id ? 'Password (fill to change)' : 'Password' }}</label>
            @include('parts.errors', ['name' => 'password'])
        </div>

        <div>
            <input placeholder="Password" name="password_confirmation" type="password" value="" autocomplete="off">
            <label for="password_confirmation">Confirm password</label>
            @include('parts.errors', ['name' => 'password_confirmation'])
        </div>

        <div>
            <input placeholder="Email" name="email" type="email" value="{{ $account->email }}">
            <label for="email">Email</label>
            @include('parts.errors', ['name' => 'email'])
        </div>

        <div>
            <input placeholder="+12123123" name="phone" type="text" value="{{ $account->phone }}">
            <label for="phone">Phone</label>
            @include('parts.errors', ['name' => 'phone'])
        </div>

        <h2>Other information</h2>

        <div>
            <input name="activated" value="true" type="radio" @if ($account->activated) checked @endif>
            <p>Enabled</p>
            <input name="activated" value="false" type="radio" @if (!$account->activated) checked @endif>
            <p>Disabled</p>
            <label>Status</label>
        </div>

        <div>
            <input name="role" value="admin" type="radio" @if ($account->admin) checked @endif>
            <p>Admin</p>
            <input name="role" value="end_user" type="radio" @if (!$account->admin) checked @endif>
            <p>End user</p>
            <label>Role</label>
        </div>

        <div class="select">
            <select name="dtmf_protocol">
                @foreach ($protocols as $value => $name)
                    <option value="{{ $value }}" @if ($account->dtmf_protocol == $value) selected="selected" @endif>
                        {{ $name }}</option>
                @endforeach
            </select>
            <label for="dtmf_protocol">DTMF Protocol</label>
        </div>

    </form>

    <hr class="large">

    @if ($account->id)
        <h2 id="contacts_lists">Contacts Lists</h2>

        @if ($contacts_lists->isNotEmpty())
            <form method="POST" action="{{ route('admin.account.contacts_lists.attach', $account->id) }}"
                accept-charset="UTF-8">
                @csrf
                @method('post')

                <div class="select">
                    <select name="contacts_list_id" onchange="this.form.submit()">
                        <option>
                            Select a contacts list
                        </option>
                        @foreach ($contacts_lists as $contacts_list)
                            <option value="{{ $contacts_list->id }}">
                                {{ $contacts_list->title }}
                            </option>
                        @endforeach
                    </select>
                    <label for="contacts_list_id">Add a Contacts lists</label>
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
                    <i class="material-icons-outlined">close</i>
                </a>
            </p>
        @endforeach

        <h2>Individual contacts</h2>

        @foreach ($account->contacts as $contact)
            <p class="chip">
                <a href="{{ route('admin.account.edit', $account) }}">{{ $contact->identifier }}</a>
                <a href="{{ route('admin.account.contact.delete', [$account, $contact->id]) }}">
                    <i class="material-icons-outlined">close</i>
                </a>
            </p>
        @endforeach

        <br />
        <a class="btn btn-tertiary" href="{{ route('admin.account.contact.create', $account) }}">Add</a>

        <hr class="large">

        <h2 id="provisioning">Provisioning</h2>

        @if ($account->provisioning_token)
            <p>Share the following picture with the user or the one-time-use link bellow.</p>

            <img style="max-width: 15rem;" src="{{ route('provisioning.qrcode', $account->provisioning_token) }}">

            <form class="inline">
                <div>
                    <input type="text" style="min-width: 40rem;" readonly
                        value="{{ route('provisioning.show', $account->provisioning_token) }}">
                    <small>The following link can only be visited once</small>
                </div>
                <div>
                    <a class="btn" href="{{ route('admin.account.provision', $account->id) }}">Renew the provision
                        link</a>
                    <small>The current one will be unavailable</small>
                </div>
            </form>
            <p class="mt-3">
            </p>
        @else
            <p class="mt-3">
                <a class="btn btn-light" href="{{ route('admin.account.provision', $account->id) }}">Generate a provision
                    link</a>
            </p>
        @endif

        <h2>External Account</h2>

        @if ($account->externalAccount)
            <p>
                <b>Identifier:</b> {{ $account->externalAccount->identifier }}<br />
            </p>
        @else
            <a class="btn @if ($external_accounts_count == 0) disabled @endif"
                href="{{ route('admin.account.external_account.attach', $account->id) }}">Attach an External Account
                ({{ $external_accounts_count }} left)</a>
        @endif

        <h2>Actions</h2>

        @if ($account->dtmf_protocol)
            <table class="table">
                <tbody>
                    @foreach ($account->actions as $action)
                        <tr>
                            <th scope="row">{{ $action->key }}</th>
                            <td>{{ $action->code }}</td>
                            <td>
                                <a class="btn"
                                    href="{{ route('admin.account.action.edit', [$account, $action->id]) }}">Edit</a>
                                <a class="btn"
                                    href="{{ route('admin.account.action.delete', [$account, $action->id]) }}">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <a class="btn" href="{{ route('admin.account.action.create', $account) }}">Add</a>
        @else
            <p>To manage actions, you must configure the DTMF protocol in the account settings.</p>
        @endif

        <h2>Types</h2>

        <table class="table">
            <tbody>
                @foreach ($account->types as $type)
                    <tr>
                        <th scope="row">{{ $type->key }}</th>
                        <td>
                            <form method="POST" action="{{ route('admin.account.account_type.destroy', [$account, $type->id]) }}" accept-charset="UTF-8">
@csrf
@method('delete')
                            <input class="btn" type="submit" value="Delete">
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a class="btn" href="{{ route('admin.account.account_type.create', $account) }}">Add</a>
    @endif

@endsection
