<h3>
    {{ __('Voicemails') }}
</h3>
@if ($account->uploadedVoicemails->isEmpty())
    <div class="empty"><i class="ph ph-voicemail"></i>
        <p>{{ __('No new voicemail') }}</p>
    </div>
@endif
<ul>
    @foreach ($account->uploadedVoicemails as $voicemail)
        <li>
            <div class="content">
                <p>{{ $voicemail->sip_from }}</p>
                <p>
                    {{ $voicemail->created_at }}
                    @if ($voicemail->url)
                        <a style="margin-left: 3px;" href="{{ $voicemail->download_url }}" download><i class="ph ph-download"></i></a>
                    @endif
                </p>
            </div>
            <div class="meta">
                <audio class="oppose" controls src="{{ $voicemail->url }}"></audio>
                <a type="button" class="oppose btn tertiary"
                @if ($account->admin)
                    href="{{ route('admin.account.file.delete', [$account, $voicemail->id]) }}" @else
                    href="{{ route('account.file.delete', [$voicemail->id]) }}"
                @endif
                >
                    <i class="ph ph-trash"></i>
                </a>
            </div>
        </li>
    @endforeach
</ul>