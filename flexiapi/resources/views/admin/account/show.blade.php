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
            <div class="header">
                <h3><i class="ph ph-info"></i>{{ __('Information') }}</h3>
                <div>
                    <a class="btn small secondary" href="{{ route('admin.account.edit', $account) }}">
                        <i class="ph ph-pencil"></i>
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>
            <ul>
                <li>
                    <span class="icon"><i class="ph ph-user"></i></span>
                    <div class="content">
                        <span class="title"> {{ __('SIP Adress') }} </span>
                        <span class="subtitle"> sip:{{ $account->identifier }}</span>
                    </div>
                    <div class="meta"> @include('admin.account.parts.badges', ['account' => $account])</div>
                </li>
                @if ($account->passwords()->count() > 0)
                <li>
                    <span class="icon"><i class="ph ph-password"></i></span>
                    <div class="content">
                        <span class="title"> {{ __('Password') }} </span>
                        <span class="subtitle">**********</span>
                    </div>
                </li>
                @endif
                @if ($account->email)
                <li>
                    <span class="icon"><i class="ph ph-envelope"></i></span>
                    <div class="content">
                        <span class="title"> {{ __('Email') }} </span>
                        <span class="subtitle">{{ $account->email }}</span>
                    </div>
                </li>
                @endif
                @if ($account->phone)
                <li>
                    <span class="icon"><i class="ph ph-phone"></i></span>
                    <div class="content">
                        <span class="title"> {{ __('Phone number') }} </span>
                        <span class="subtitle">{{ $account->phone }}</span>
                    </div>
                </li>
                @endif
                <li>
                    <span class="icon"><i class="ph ph-globe-hemisphere-west"></i></span>
                    <div class="content">
                        <span class="title">{{ __('Space') }}</span>
                        <span class="subtitle"><p><a href="{{ route('admin.spaces.show', $account->space->id) }}">{{ $account->domain }}</a></p></span>
                    </div>
                </li>
                <li>
                    <span class="icon"><i class="ph ph-check-circle"></i></span>
                    <div class="content">
                        <span class="title"> {{ __('Status') }} </span>
                        <span class="subtitle">
                            @if ($account->updated_at)
                            {{  __('Updated on') . ' ' . $account->updated_at->format('d/m/Y') }}
                            @endif
                        </span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="card">
            <div class="header">
                <h3>
                    <i class="ph ph-user-circle-gear"></i>
                    {{ __('Manage') }}
                </h3>
            </div>
            <ul>
                <li @if (empty($account->email))class="disabled" @endif>
                    <span class="icon"><i class="ph ph-password"></i></span>
                    <div class="content"> 
                        <span class="title">{{ __('Reset password') }}</span>
                        <span class="subtitle">{{ __('Send an email to the user to reset the password') }}</span>
                    </div>
                    <div class="meta">
                        <a class="btn secondary small" href="{{ route('admin.account.reset_password_email.create', $account) }}">
                            <i class="ph ph-paper-plane-right"></i>
                        </a>
                    </div>
                </li>
                <li @if (empty($account->email))class="disabled" @endif>
                    <span class="icon"><i class="ph ph-faders"></i></span>
                    <div class="content"> 
                        <span class="title">{{ __('Provisioning') }}</span>
                        <span class="subtitle">{{ __('Send an email to the user with provisioning information') }}</span>
                    </div>
                    <div class="meta">
                        <a class="btn secondary small" href="{{ route('admin.account.provisioning_email.create', $account) }}">
                            <i class="ph ph-paper-plane-right"></i>
                        </a>
                    </div>
                </li>
                <li>
                    <span class="icon color red"><i class="ph ph-trash"></i></span>
                    <div class="content"> 
                        <span class="title">{{ __('Delete') }}</span>
                        <span class="subtitle">{{ __("Delete the user's account") }}</span>
                    </div>
                    <div class="meta">
                        <a class="btn danger secondary small" href="{{ route('admin.account.delete', $account->id) }}">
                            <i class="ph ph-trash"></i>
                        </a>
                    </div>
                </li>
            </ul>
        </div>

        <div class="card">
            <div class="header">
                <h3>
                    <i class="ph ph-user-circle-plus"></i>
                    {{ __('External Account') }}
                </h3>
                <a class="btn small secondary oppose" href="{{ route('admin.account.external.show', $account) }}">
                    <i class="ph ph-plus"></i>
                    @if ($account->external){{ __('Edit') }}@else{{ __('Create') }}@endif
                </a>
            </div>
            @if ($account->external)
                <ul>
                    @if ($account->external->username)
                    <li>
                        <span class="icon"><i class="ph ph-user"></i></span>
                        <div class="content">
                            <span class="title">{{ __('Username') }}</span>
                            <span class="subtitle">{{ $account->external->username }}</span>
                        </div>
                    </li>
                    @endif
                    @if ($account->external->domain)
                        <li>
                            <span class="icon"><i class="ph ph-hard-drive"></i></span>
                            <div class="content">
                                <span class="title">{{ __('Domain') }}</span>
                                <span class="subtitle">{{ $account->external->domain }}</span>
                            </div>
                        </li>
                    @endif
                    @if ($account->external->password)
                        <li>
                            <span class="icon"><i class="ph ph-password"></i> </span>
                            <div class="content">
                                <span class="title">{{ __('Password') }}</span>
                                <span class="subtitle">**********</span>
                            </div>
                        </li>
                    @endif
                </ul>
            @else
                <div class="empty">
                    <i class="ph ph-user-circle-plus"></i>
                    <p>{{ __('No external accounts configured') }}</p>
                </div>
            @endif
        </div>

        <div class="card">
            
            <div class="header">
                <h3 class="large" id="provisioning"><i class="ph ph-faders"></i> {{ __('Provisioning') }}</h3>
                <a class="btn small secondary oppose" href="{{ route('admin.account.provision', $account->id) }}">
                    <i class="ph ph-repeat"></i>
                    {{ __('Renew') }}
                </a>
            </div>

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
                    {{ __('CardDAV credentials') }}
                </h3>
                @if ($account->carddavServers->isEmpty())
                    <div class="empty">
                        <i class="ph ph-address-book"></i>
                        <p>{{ __('No CardDAV account is configured') }}</p>
                    </div>
                @else
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('CardDav Server') }}</th>
                            <th>{{ __('Username') }}</th>
                            <th>{{ __('Realm') }}</th>
                            <th>{{ __('Algorithm') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($account->carddavServers as $carddavServer)
                            <tr>
                                <td class="line">
                                    {{ $carddavServer->name }}
                                    <br>
                                    <small>{{ $carddavServer->uri }}</small>
                                </td>
                                <td class="line">{{ $carddavServer->pivot->username }}</td>
                                <td class="line">{{ $carddavServer->pivot->realm }}</td>
                                <td class="line">{{ $carddavServer->pivot->algorithm }}</td>
                                <td class="actions">
                                    <a type="button" class="btn small tertiary" href="{{ route('admin.account.carddavs.delete', [$account, $carddavServer]) }}">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        @endif

        <div class="card large">
            <h3>
                {{ __('Devices') }}
            </h3>
             @if ($devices->isEmpty())
                <div class="empty"><i class="ph ph-devices"></i>
                    <p>{{ __('No device') }}</p>
                </div>
            @else
                <ul>
                    @foreach ($devices as $device)
                        <li>
                            <span class="icon"> <i class=" ph {{ $device->app_icon }}"></i></span>
                            <div class="content">
                                <span class="title">{{ $device->app_label . ' - ' . $device->version }}</span>
                                <span class="subtitle">{{ $device->user_agent }}</span>
                            </div>
                            <div class="meta">
                                <span class="subtitle">{{ __(':time ago', ['time' => $device->update_time->diffForHumans(now(), true)]) }} </span>
                                <a type="button" class="btn small oppose secondary" href="{{ route('admin.account.device.delete', [$account->id, $device->uuid]) }}">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <dialog id="dictionary_clear" closedby="any">
            <h2>{{ __('Clear') }}</h2>
            <p>{{ __('Are you sure you want to clear all the dictionary items?') }}</p>

            <a class="btn small oppose" href="{{ route('admin.account.dictionary.clear', $account) }}">{{ __('Clear') }}</a>
            <button class="btn small oppose secondary" commandfor="dictionary_clear" command="close" onclick="document.querySelector('#dictionary_clear').close()">{{ __('Cancel') }}</button>
        </dialog>

        <div class="card large">
            <a class="btn small oppose" href="{{ route('admin.account.dictionary.create', $account) }}">
                <i class="ph ph-plus"></i>
                {{ __('Add') }}
            </a>
            <button class="btn small secondary oppose" onclick="document.querySelector('#dictionary_clear').showModal()">
                <i class="ph ph-broom"></i>
                {{ __('Clear') }}
            </button>
            <h3>
                <i class="ph ph-book-open-text"></i>
                {{ __('Dictionary') }}
            </h3>
            @if ($account->dictionaryEntries->isEmpty())
                <div class="empty">
                    <i class="ph ph-book-open-text"></i>
                    <p>{{ __('Empty') }}</p>
                </div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Key') }}</th>
                        <th>{{ __('Value') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
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
            @endif
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
