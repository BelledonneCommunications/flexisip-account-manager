@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-phone"></i> {{ $account->identifier }}</h1>
    </header>
    @include('admin.account.parts.tabs')

    <div class="grid">
        <div class="card">
            <h3>
                {{ __('Voicemails') }}
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if ($account->uploadedVoicemails->isEmpty())
                        <tr class="empty">
                            <td colspan="2">{{ __('Empty') }}</td>
                        </tr>
                    @endif
                    @foreach ($account->uploadedVoicemails as $voicemail)
                        <tr>
                            <td>
                                {{ $voicemail->created_at }}
                                @if ($voicemail->url)
                                    <a style="margin-left: 1rem;" href="{{ $voicemail->download_url }}" download>
                                        <i class="ph ph-download"></i>
                                    </a>
                                @endif
                                @if ($voicemail->sip_from)
                                    <br/>
                                    <small>{{ $voicemail->sip_from }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($voicemail->url)
                                    <audio class="oppose" controls src="{{ $voicemail->url }}"></audio>
                                    <a type="button"
                                        class="oppose btn tertiary"
                                        href="{{ route('admin.account.file.delete', [$account, $voicemail->id]) }}">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            @include('admin.account.call_forwardings.edit', ['account' => $account])
        </div>
    </div>
@endsection