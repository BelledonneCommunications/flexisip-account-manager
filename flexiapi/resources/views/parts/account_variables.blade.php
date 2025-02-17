@if ($account)
    <script type="text/javascript">
        var account_infos = {
            sip: '{{ $account->identifier }}',
            username: '{{ $account->username }}',
            @if (!empty(space()?->account_proxy_registrar_address))
                registrar_address: '{{ space()?->account_proxy_registrar_address }}',
            @endif
            domain: '{{ $account->domain }}'
        }
    </script>
@endif
