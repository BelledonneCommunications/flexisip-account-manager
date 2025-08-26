@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-users"></i> {{ $account->identifier }}</h1>
    </header>
    @include('admin.account.parts.tabs')

    <div class="grid">
        <div class="card">
            <a class="btn small oppose" href="{{ route('admin.account.edit', $account) }}">
                <i class="ph ph-pencil"></i>
                {{ __('Edit') }}
            </a>
            <h3>
                @if ($account->updated_at)
                    <small class="oppose" title="{{ $account->updated_at }}">{{ __('Updated on') }} {{ $account->updated_at->format('d/m/Y') }}</small>
                @endif
                {{ __('Information') }}
            </h3>

            <p><i class="ph ph-user"></i> {{ __('SIP Adress') }}: sip:{{ $account->identifier }}</p>
            @if ($account->email)
                <p><i class="ph ph-envelope"></i> {{ __('Email') }}: {{ $account->email }}</p>
            @endif
            @if ($account->phone)
                <p><i class="ph ph-phone"></i> {{ __('Phone') }}: {{ $account->phone }}</p>
            @endif
            @if ($account->passwords()->count() > 0)
                <p><i class="ph ph-password"></i> {{ __('Password') }}: **********</p>
            @endif

            <p>
                <i class="ph ph-globe-hemisphere-west"></i>
                {{ __('Space') }}: <a href="{{ route('admin.spaces.show', $account->space->id) }}">{{ $account->domain }}</a>
            </p>
            <p>
                @include('admin.account.parts.badges', ['account' => $account])
            </p>
        </div>
        <div class="card">
            <h3>
                {{ __('Manage') }}
            </h3>
            <table>
                <tbody>
                    <tr @if (empty($account->email))class="disabled"@endif>
                        <td>{{ __('Send an email to the user to reset the password') }}</td>
                        <td class="actions">
                            <a class="btn secondary small" href="{{ route('admin.account.reset_password_email.create', $account) }}">
                                <i class="ph ph-paper-plane-right"></i>
                            </a>
                        </td>
                    </tr>
                    <tr @if (empty($account->email))class="disabled"@endif>
                        <td>{{ __('Send an email to the user with provisioning information') }}</td>
                        <td class="actions">
                            <a class="btn secondary small" href="{{ route('admin.account.provisioning_email.create', $account) }}">
                                <i class="ph ph-paper-plane-right"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ __('Delete') }}
                        </td>
                        <td class="actions">
                            <a class="btn tertiary small" href="{{ route('admin.account.delete', $account->id) }}">
                                <i class="ph ph-trash"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <a class="btn small oppose" href="{{ route('admin.account.external.show', $account) }}">
                <i class="ph ph-plus"></i>
                @if ($account->external){{ __('Edit') }}@else{{ __('Create') }}@endif
            </a>
            <h3>
                {{ __('External Account') }}
            </h3>
            @if ($account->external)
                @if ($account->external->username)
                    <p><i class="ph ph-user"></i> {{ __('Username') }}: {{ $account->external->username }}</p>
                @endif
                @if ($account->external->domain)
                    <p><i class="ph ph-hard-drive"></i> {{ __('Domain') }}: {{ $account->external->domain }}</p>
                @endif
                @if ($account->external->password)
                    <p><i class="ph ph-password"></i> {{ __('Password') }}: **********</p>
                @endif
            @else
                <p>{{ __('Empty') }}</p>
            @endif
        </div>

        <div class="card">
            <a class="btn small oppose" href="{{ route('admin.account.provision', $account->id) }}">
                <i class="ph ph-repeat"></i>
                {{ __('Renew') }}
            </a>
            <h3 class="large" id="provisioning">{{ __('Provisioning') }}</h3>

            @if ($account->provisioning_token)
                <div>
                    <img style="max-width: 15rem;" src="{{ $account->provisioning_qrcode_url }}">
                </div>

                <form class="inline">
                    <div>
                        <input type="text" style="min-width: 40rem;" readonly
                            value="{{ $account->provisioning_url }}">
                        <small>{{ __('The link can only be visited once') }}</small>
                    </div>
                </form>
                <p>
                    <i class="ph ph-app-window"></i>
                    <a target="_blank" href="{{ $account->provisioning_wizard_url }}">{{ __('Provisioning wizard URL') }}</a>
                </p>
            @else
                <a class="btn btn-light" href="{{ route('admin.account.provision', $account->id) }}">{{ __('Create') }}</a>
            @endif
        </div>

        @if ($account->space->carddav_user_credentials)
            <div class="card large" id="carddavs">
                @if ($account->remainingCardDavCredentialsCreatable->count() > 0)
                    <a class="btn small oppose" href="{{ route('admin.account.carddavs.create', $account) }}">
                        <i class="ph ph-plus"></i>
                        {{ __('Add') }}
                    </a>
                @endif
                <h3>
                    {{ __('CardDav credentials') }}
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('CardDav Server') }}</th>
                            <th>{{ __('Username') }}</th>
                            <th>{{ __('Domain') }}</th>
                            <th>{{ __('Algorithm') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($account->carddavServers->isEmpty())
                            <tr class="empty">
                                <td colspan="5">{{ __('Empty') }}</td>
                            </tr>
                        @else
                            @foreach ($account->carddavServers as $carddavServer)
                                <tr>
                                    <td class="line">{{ $carddavServer->name }}</td>
                                    <td class="line">{{ $carddavServer->pivot->username }}</td>
                                    <td class="line">{{ $carddavServer->pivot->domain }}</td>
                                    <td class="line">{{ $carddavServer->pivot->algorithm }}</td>
                                    <td class="actions">
                                        <a type="button" class="btn small tertiary" href="{{ route('admin.account.carddavs.delete', [$account, $carddavServer]) }}">
                                            <i class="ph ph-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

        @endif

        <div class="card large">
            <h3>
                {{ __('Devices') }}
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>User Agent</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if ($devices->isEmpty())
                        <tr class="empty">
                            <td colspan="2">{{ __('Empty') }}</td>
                        </tr>
                    @else
                        @foreach ($devices as $device)
                            <tr>
                                <td class="line">{{ $device->user_agent }}</td>
                                <td class="actions">
                                    <a type="button" class="btn small tertiary" href="{{ route('admin.account.device.delete', [$account->id, $device->uuid]) }}">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="card large">
            <a class="btn small oppose" href="{{ route('admin.account.dictionary.create', $account) }}">
                <i class="ph ph-plus"></i>
                {{ __('Add') }}
            </a>
            <h3>
                {{ __('Dictionary') }}
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Key') }}</th>
                        <th>{{ __('Value') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if ($account->dictionaryEntries->isEmpty())
                        <tr class="empty">
                            <td colspan="3">{{ __('Empty') }}</td>
                        </tr>
                    @endif
                    @foreach ($account->dictionaryEntries as $dictionaryEntry)
                        <tr>
                            <td class="line">{{ $dictionaryEntry->key }}</td>
                            <td class="line">{{ $dictionaryEntry->value }}</td>
                            <td class="actions">
                                <a type="button"
                                   class="btn secondary small"
                                   href="{{ route('admin.account.dictionary.edit', [$account, $dictionaryEntry->key]) }}">
                                    <i class="ph ph-pencil"></i>
                                </a>
                                <a type="button"
                                   class="btn small tertiary"
                                   href="{{ route('admin.account.dictionary.delete', [$account, $dictionaryEntry->key]) }}">
                                   <i class="ph ph-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @if (space()?->intercom_features)
        <div class="card" id="actions">
            @if ($account->dtmf_protocol)
                <a class="btn small oppose" href="{{ route('admin.account.action.create', $account) }}">
                    <i class="ph ph-plus"></i>{{ __('Add') }}
                </a>
            @else
                <a class="btn small oppose" href="{{ route('admin.account.edit', $account) }}">
                    <i class="ph ph-pencil"></i>
                    {{ __('Edit') }}
                </a>
            @endif
            <h3>
                {{ __('Actions') }}
                @if ($account->dtmf_protocol)
                    <small class="oppose">{{ $account->dtmf_protocol}}</small>
                @endif
            </h3>

            @if ($account->dtmf_protocol)
                <table>
                    <tbody>
                        @if ($account->actions->isEmpty())
                            <tr class="empty">
                                <td colspan="2">{{ __('Empty') }}</td>
                            </tr>
                        @endif
                        @foreach ($account->actions as $action)
                            <tr>
                                <td scope="row">{{ $action->key }}</td>
                                <td>{{ $action->code }}</td>
                                <td class="actions">
                                    <a class="btn small secondary"
                                        href="{{ route('admin.account.action.edit', [$account, $action->id]) }}">
                                        <i class="ph ph-pencil"></i>
                                    </a>
                                    <a class="btn small tertiary"
                                        href="{{ route('admin.account.action.delete', [$account, $action->id]) }}">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>To manage actions, you must configure the DTMF protocol in the account settings.</p>
            @endif
        </div>

        <div class="card" id="types">
            <a class="btn small oppose" href="{{ route('admin.account.account_type.create', $account) }}">
                <i class="ph ph-plus"></i>{{ __('Add') }}
            </a>

            <h3>{{ __('Types') }}</h3>

            <table>
                <tbody>
                    @if ($account->types->isEmpty())
                        <tr class="empty">
                            <td colspan="2">{{ __('Empty') }}</td>
                        </tr>
                    @endif
                    @foreach ($account->types as $type)
                        <tr>
                            <td scope="row">{{ $type->key }}</td>
                            <td class="actions">
                                <form method="POST"
                                    action="{{ route('admin.account.account_type.destroy', [$account, $type->id]) }}"
                                    accept-charset="UTF-8">
                                    @csrf
                                    @method('delete')
                                    <button class="btn small tertiary" type="submit" title="{{ __('Delete') }}">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    </div>
@endsection
