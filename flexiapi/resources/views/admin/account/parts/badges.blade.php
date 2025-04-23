@if ($account->activated)
<span class="badge badge-success" title="{{ __('Activated') }}"><i class="ph">check</i></span>
@endif
@if ($account->superAdmin)
<span class="badge badge-error" title="{{ __('Super Admin') }}">Super Adm.</span>
@elseif ($account->admin)
<span class="badge badge-primary" title="{{ __('Admin') }}">Adm.</span>
@endif
@if ($account->blocked)
<span class="badge badge-error" title="{{ __('Blocked') }}"><i class="ph">prohibit</i></span>
@endif