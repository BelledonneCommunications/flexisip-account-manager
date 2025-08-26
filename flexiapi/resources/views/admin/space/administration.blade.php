@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.show')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Administration') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-globe-hemisphere-west"></i> {{ $space->name }}</h1>
    </header>

    @include('admin.space.tabs')

    <form method="POST"
        action="{{ route('admin.spaces.administration.update', $space) }}"
        accept-charset="UTF-8">
        @csrf
        @method('put')

        <div>
            <input name="name" id="name" placeholder="My Space Name" type="text" value="{{ $space->name }}">
            <label for="name">{{ __('Name') }}</label>
            @include('parts.errors', ['name' => 'name'])
        </div>

        <div>
            <input name="max_accounts" id="max_accounts" type="number" min="0" value="{{ $space->max_accounts }}">
            <label for="max_accounts">{{ __('Max accounts') }}</label>
            <span class="supporting">{{ __('Unlimited if set to 0') }}</span>
            @include('parts.errors', ['name' => 'max_accounts'])
        </div>

        <div>
            <input name="expire_at" id="expire_at" type="date" @if ($space->expire_at) value="{{ $space->expire_at->toDateString() }}" @endif min="{{ \Carbon\Carbon::now()->toDateString() }}">
            <label for="expire_at">{{ __('Expiration') }}</label>
            <span class="supporting">{{ __('Clear to never expire') }}</span>
        </div>

        <div class="large">
            @include('parts.form.toggle', ['object' => $space, 'key' => 'super', 'label' => __('Super Space'), 'supporting' => __('All the admins will be super admins')])
        </div>

        <h3 class="large">{{ __('Integration') }}</h3>

        <div class="large">
            @include('parts.form.toggle', ['object' => $space, 'key' => 'carddav_user_credentials', 'label' => __('Enable user credentials for CardDav servers')])
        </div>

        <h3 class="large">Interface</h3>
        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'custom_theme', 'label' => __('Allow a custom CSS theme'), 'supporting' => __('Check the README.md documentation')])
        </div>
        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'web_panel', 'label' => __('Enable the web interface'), 'supporting' => __('It might actually disable this page, be careful')])
        </div>

        <div class="large">
            <input class="btn" type="submit" value="{{ __('Update') }}">
        </div>
    </form>
@endsection
