@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">Activity</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">list</i> {{ $account->identifier }}</h1>
</header>

@include('admin.account.parts.tabs')

@if ($account->apiKey)
    <h3>Api Key</h3>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Created</th>
                <th>Last usage</th>
                <th>IP</th>
                <th>Requests</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $account->apiKey->key }}
                </td>
                <td>
                    {{ $account->apiKey->created_at }}
                </td>
                <td>
                    {{ $account->apiKey->last_used_at }}
                </td>
                <td>
                    {{ $account->apiKey->ip ?? '-' }}
                </td>
                <td>
                    {{ $account->apiKey->requests }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if ($account->accountCreationToken)
    <h3>Account Creation Token</h3>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Created</th>
                <th>Used</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <tr @if ($account->accountCreationToken->consumed()) class="disabled crossed" @endif>
                <td>****</td>
                <td>
                    {{ $account->accountCreationToken->created_at }}
                </td>
                <td>
                    {{ $account->accountCreationToken->created_at != $account->accountCreationToken->updated_at ? $account->accountCreationToken->updated_at : '-' }}
                </td>
                <td title="{{ $account->accountCreationToken->user_agent }}">
                    {{ $account->accountCreationToken->ip ? $account->accountCreationToken->ip : '-' }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if ($account->recoveryCodes->isNotEmpty())
    <h3>Recovery Codes</h3>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Created</th>
                <th>Used</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->recoveryCodes as $recoveryCode)
                <tr @if ($recoveryCode->consumed()) class="disabled crossed" @endif>
                    <td>****</td>
                    <td>
                        {{ $recoveryCode->created_at }}
                    </td>
                    <td>
                        {{ $recoveryCode->created_at != $recoveryCode->updated_at ? $recoveryCode->updated_at : '-' }}
                    </td>
                    <td title="{{ $recoveryCode->user_agent }}">
                        {{ $recoveryCode->ip ? $recoveryCode->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->phoneChangeCodes->isNotEmpty())
    <h3>Phone Change requests</h3>
    <table>
        <thead>
            <tr>
                <th>Phone</th>
                <th>Code</th>
                <th>Created</th>
                <th>Used</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->phoneChangeCodes as $phoneChangeCode)
                <tr @if ($phoneChangeCode->consumed()) class="disabled crossed" @endif>
                    <td>{{ $phoneChangeCode->phone }}</td>
                    <td>{{ $phoneChangeCode->code ?? '-' }}</td>
                    <td>
                        {{ $phoneChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $phoneChangeCode->created_at != $phoneChangeCode->updated_at ? $phoneChangeCode->updated_at : '-' }}
                    </td>
                    <td title="{{ $phoneChangeCode->user_agent }}">
                        {{ $phoneChangeCode->ip ? $phoneChangeCode->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->emailChangeCodes->isNotEmpty())
    <h3>Email Change requests</h3>
    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>Code</th>
                <th>Created</th>
                <th>Used</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->emailChangeCodes as $emailChangeCode)
                <tr @if ($emailChangeCode->consumed()) class="disabled crossed" @endif>
                    <td>{{ $emailChangeCode->email }}</td>
                    <td>{{ $emailChangeCode->code ?? '-' }}</td>
                    <td>
                        {{ $emailChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $emailChangeCode->created_at != $emailChangeCode->updated_at ? $emailChangeCode->updated_at : '-' }}
                    </td>
                    <td title="{{ $emailChangeCode->user_agent }}">
                        {{ $emailChangeCode->ip ? $emailChangeCode->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->provisioningTokens->isNotEmpty())
    <h3>Provisioning tokens</h3>
    <table>
        <thead>
            <tr>
                <th>Token</th>
                <th>Created</th>
                <th>Used</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->provisioningTokens as $provisioningToken)
                <tr @if ($provisioningToken->consumed()) class="disabled crossed" @endif>
                    <td>{{ $provisioningToken->token }}</td>
                    <td>
                        {{ $provisioningToken->created_at }}
                    </td>
                    <td>
                        {{ $provisioningToken->consumed() ? $provisioningToken->updated_at : '-' }}
                    </td>
                    <td title="{{ $provisioningToken->user_agent }}">
                        {{ $provisioningToken->ip ? $provisioningToken->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection