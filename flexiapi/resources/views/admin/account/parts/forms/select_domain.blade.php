@if (auth()->user() && auth()->user()->superAdmin && count($domains) > 0)
<div class="select">
    <select name="domain" onchange="this.form.submit()">
        <option value="">
            {{ __('Select a domain') }}
        </option>
        @foreach ($domains as $d)
            <option value="{{ $d }}"
                @if (request()->get('domain', '') == $d) selected="selected" @endif>
                {{ $d }}
            </option>
        @endforeach
    </select>
    <label for="domain">{{ __('Domain') }}</label>
</div>
@endif
