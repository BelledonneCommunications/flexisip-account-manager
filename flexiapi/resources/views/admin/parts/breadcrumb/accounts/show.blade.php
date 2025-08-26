@include('admin.parts.breadcrumb.accounts.index')
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->identifier }}</a>
</li>