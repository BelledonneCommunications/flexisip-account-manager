@extends('layouts.main')

@section('content')
    <header>
        <h1><i class="ph ph-key"></i> {{ __('API Keys') }}</h1>
        <a href="{{ route('admin.api_keys.create') }}" class="btn oppose">
            <i class="ph ph-plus"></i>
            {{ __('Create') }}
        </a>
    </header>

    @include('admin.parts.settings_tabs')

    <table>
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Key') }}</th>
                <th>{{ __('Created') }}</th>
            </tr>
        </thead>
        <tbody>
            @if ($api_keys->isEmpty())
                <tr class="empty">
                    <td colspan="3">{{ __('Empty') }}</td>
                </tr>
            @endif
            @foreach ($api_keys as $api_key)
                <tr>
                    <td>{{ $api_key->name }}
                        <br />
                        <small>
                            {{ __('Requests') }}: {{ $api_key->requests }}
                        </small>

                    </td>
                    <td>
                        <code>{{ $api_key->key }}</code><br />
                        <small>{{ __('Activity expiration delay') }}: {{ $api_key->expires_after_last_used_minutes ? $api_key->expires_after_last_used_minutes . ' min' : __('Never')}} | {{ __('Last used') }}: {{ $api_key->last_used_at ?? __('Never') }}</small>
                    </td>
                    <td>{{ $api_key->created_at }}
                        <a class="btn secondary small oppose" href="{{ route('admin.api_keys.delete', $api_key->key) }}"><i class="ph ph-trash</i>"></a>
                        <small>
                            <a href="{{ route('admin.account.show', $api_key->account->id) }}">
                                {{ __('By') }}: {{ $api_key->account->identifier }}
                            </a>
                        </small>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
