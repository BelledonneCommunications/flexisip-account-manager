@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.index') }}">Spaces</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.show', $space->id) }}">
            {{ $space->host }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Space Administration</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">globe-hemisphere-west</i> {{ $space->host }}</h1>
    </header>

    @include('admin.space.tabs')

    <form method="POST"
        action="{{ route('admin.spaces.administration.update', $space) }}"
        accept-charset="UTF-8">
        @csrf
        @method('put')

        <div>
            <input name="max_accounts" id="max_accounts" type="number" min="0" value="{{ $space->max_accounts }}">
            <label for="max_accounts">Max accounts of the space</label>
            <span class="supporting">Unlimited if set to 0</span>
            @include('parts.errors', ['name' => 'max_accounts'])
        </div>

        <div>
            <input name="expire_at" id="expire_at" type="date" @if ($space->expire_at) value="{{ $space->expire_at->toDateString() }}" @endif min="{{ \Carbon\Carbon::now()->toDateString() }}">
            <label for="expire_at">Expire at</label>
            <span class="supporting">Clear to never expire</span>
        </div>

        <div class="large">
            @include('parts.form.toggle', ['object' => $space, 'key' => 'super', 'label' => 'Super space', 'supporting' => 'All the admins in this Space will be Super Admins'])
        </div>

        <h3 class="large">Interface</h3>
        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'custom_theme', 'label' => 'Allow a custom CSS theme', 'supporting' => 'Check the README.md documentation to see how to setup the custom CSS file'])
        </div>
        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'web_panel', 'label' => 'Enable the web interface', 'supporting' => 'It will actually disable this page, be careful'])
        </div>

        <div class="large">
            <input class="btn" type="submit" value="Update">
        </div>
    </form>
@endsection
