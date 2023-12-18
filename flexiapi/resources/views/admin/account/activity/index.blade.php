@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.edit', $account) }}">{{ $account->identifier }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Activity</li>
@endsection

@section('content')

<header>
    <h1><i class="material-icons-outlined">view_list</i> {{ $account->identifier }}</h1>
</header>

@include('admin.account.parts.tabs')

@if ($account->recoveryCodes->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Recovery Codes</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->recoveryCodes as $recoveryCode)
                <tr @if ($recoveryCode->consumed()) class="disabled" @endif>
                    <td @if ($recoveryCode->consumed())class="crossed" @endif>****</td>
                    <td>
                        {{ $recoveryCode->created_at }}
                    </td>
                    <td>
                        {{ $recoveryCode->created_at != $recoveryCode->updated_at ? $recoveryCode->updated_at : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->phoneChangeCodes->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Phone Change requests</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->phoneChangeCodes as $phoneChangeCode)
                <tr @if ($phoneChangeCode->consumed()) class="disabled" @endif>
                    <td @if ($phoneChangeCode->consumed())class="crossed" @endif>****</td>
                    <td>
                        {{ $phoneChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $phoneChangeCode->created_at != $phoneChangeCode->updated_at ? $phoneChangeCode->updated_at : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->emailChangeCodes->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Email Change requests</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->emailChangeCodes as $emailChangeCode)
                <tr @if ($emailChangeCode->consumed()) class="disabled" @endif>
                    <td @if ($emailChangeCode->consumed())class="crossed" @endif>****</td>
                    <td>
                        {{ $emailChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $emailChangeCode->created_at != $emailChangeCode->updated_at ? $emailChangeCode->updated_at : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->provisioningTokens->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Provisioning Tokens</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->provisioningTokens as $provisioningToken)
                <tr @if ($provisioningToken->used) class="disabled" @endif>
                    <td @if ($provisioningToken->used) class="crossed" @endif>{{ $provisioningToken->token }}</td>
                    <td>
                        {{ $provisioningToken->created_at }}
                    </td>
                    <td>
                        {{ $provisioningToken->updated_at }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection