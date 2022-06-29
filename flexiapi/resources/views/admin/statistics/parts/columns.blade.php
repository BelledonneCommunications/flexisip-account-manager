<div class="bar first" style="flex-basis: {{ percent($slice['phone'] - $slice['activated_phone'], $max) }}%"
    data-value="{{ $slice['phone'] - $slice['activated_phone'] }}"
    title="Unactivated phone: {{ $slice['phone'] - $slice['activated_phone'] }}"></div>
<div class="bar first activated" style="flex-basis: {{ percent($slice['activated_phone'], $max) }}%"
    data-value="{{ $slice['activated_phone'] }}"
    title="Activated phone: {{ $slice['activated_phone'] }}"></div>
<div class="bar second" style="flex-basis: {{ percent($slice['email'] - $slice['activated_email'], $max) }}%"
    data-value="{{ $slice['email'] - $slice['activated_email'] }}"
    title="Unactivated email: {{ $slice['email'] - $slice['activated_email'] }}"></div>
<div class="bar second activated" style="flex-basis: {{ percent($slice['activated_email'], $max) }}%"
    data-value="{{ $slice['activated_email'] }}"
    title="Activated email: {{ $slice['activated_email'] }}"></div>