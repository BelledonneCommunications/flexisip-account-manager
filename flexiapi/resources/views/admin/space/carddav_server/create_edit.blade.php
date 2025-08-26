@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('CardDav Server') }}</li>
@endsection

@section('content')
<header>
    <h1>
        <i class="ph ph-users"></i> {{ __('CardDav Server') }} -
        @if ($carddavServer->id)
            {{ __('Edit') }}
        @else
            {{ __('Create') }}
        @endif
    </h1>
</header>

<form method="POST"
action="{{ $carddavServer->id ? route('admin.spaces.carddavs.update', [$space->id, $carddavServer->id]) : route('admin.spaces.carddavs.store', $space->id) }}"
id="create_edit" accept-charset="UTF-8">
    @csrf
    @method($carddavServer->id ? 'put' : 'post')

    <div>
        <input placeholder="https://..." required="required" name="uri" type="url"
            value="@if($carddavServer->uri){{ $carddavServer->uri }}@else{{ old('uri') }}@endif">
        <label for="uri">Uri</label>
        @include('parts.errors', ['name' => 'uri'])
    </div>

    @include('parts.form.toggle', ['object' => $carddavServer, 'key' => 'enabled', 'label' => __('Enabled')])

    <hr class="large" />

    <div>
        <input placeholder="3" min="1" required="required" name="min_characters" type="number" value="@if($carddavServer->min_characters){{ $carddavServer->min_characters }}@else{{ old('min_characters') ?? 3 }}@endif">
        <label for="min_characters">{{ __('Min characters to search') }}</label>
        @include('parts.errors', ['name' => 'min_characters'])
    </div>

    <div>
        <input placeholder="0" min="0" required="required" name="results_limit" type="number" value="@if($carddavServer->results_limit){{ $carddavServer->results_limit }}@else{{ old('results_limit') ?? 0 }}@endif">
        <label for="results_limit">{{ __('Limit the number of results') }}</label>
        <span class="supporting">{{ __('Unlimited if set to 0') }}</span>
        @include('parts.errors', ['name' => 'results_limit'])
    </div>

    @include('parts.form.toggle', ['object' => $carddavServer, 'key' => 'use_exact_match_policy', 'label' => __('Use exact match policy'), 'supporting' => __('Whether match must be exact or approximate (ignoring case, accents…)')])

    <details class="large" @if ($errors->isNotEmpty())open @endif>
        <summary>
            <h3 class="large">
                {{ __('Other information') }}
            </h3>
        </summary>
        <section>
            <div>
                <input placeholder="5" min="1" required="required" name="timeout" type="number" value="@if($carddavServer->timeout){{ $carddavServer->timeout }}@else{{ old('timeout') ?? 3 }}@endif">
                <label for="timeout">{{ __('Request timeout in seconds') }}</label>
                @include('parts.errors', ['name' => 'timeout'])
            </div>

            <div>
                <input placeholder="500" min="100" required="required" name="delay" type="number" value="@if($carddavServer->delay){{ $carddavServer->delay }}@else{{ old('delay') ?? 500 }}@endif">
                <label for="delay">{{ __('Delay in milliseconds before submiting the request') }}</label>
                @include('parts.errors', ['name' => 'delay'])
            </div>

            <div>
                <input placeholder="username,domain..." name="fields_for_user_input" type="text"
                    value="{{ $carddavServer->fields_for_user_input ?? old('fields_for_user_input') }}">
                <label for="fields_for_user_input">{{ __('List of vcard fields to match with user input') }}</label>
                <span class="supporting">{{ __('Separated by commas') }}</span>
                @include('parts.errors', ['name' => 'fields_for_user_input'])
            </div>

            <div>
                <input placeholder="username,domain..." name="fields_for_domain" type="text"
                    value="{{ $carddavServer->fields_for_domain ?? old('fields_for_domain') }}">
                <label for="fields_for_domain">{{ __('List of vcard fields to match for SIP domain') }}</label>
                <span class="supporting">{{ __('Separated by commas') }}</span>
                @include('parts.errors', ['name' => 'fields_for_domain'])
            </div>
        </section>
    </details>

    <div class="large">
        @if ($carddavServer->id)
            <input class="btn" type="submit" value="{{ __('Update') }}">
        @else
            <input form="create_edit" class="btn" type="submit" value="{{ __('Create') }}">
        @endif
    </div>

</form>
@endsection