@extends('layouts.main')

@section('content')
    @include('parts.tabs', [
        'items' => [
            route('admin.statistics.show', ['type' => 'messages']) => 'Messages',
            route('admin.statistics.show', ['type' => 'accounts']) => 'Accounts',
        ],
    ])

    <header>
        <form class="inline" method="POST" action="{{ route('admin.statistics.edit') }}" accept-charset="UTF-8">
            @csrf
            @method('post')

            <input type="hidden" name="by" value="{{ $by }}">
            <input type="hidden" name="type" value="{{ $type }}">

            <div>
                <input type="date" name="from" value="{{ $request->get('from') }}" onchange="this.form.submit()">
                <label for="from">From</label>
            </div>
            <div>
                <input type="date" name="to" value="{{ $request->get('to') }}" onchange="this.form.submit()">
                <label for="to">To</label>
            </div>

            <div>
                <a href="{{ route('admin.statistics.show', ['by' => 'day', 'type' => $type] + $request->only(['from', 'to'])) }}"
                    class="chip @if ($by == 'day') selected @endif">Day</a>
                <a href="{{ route('admin.statistics.show', ['by' => 'week', 'type' => $type] + $request->only(['from', 'to'])) }}"
                    class="chip @if ($by == 'week') selected @endif">Week</a>
                <a href="{{ route('admin.statistics.show', ['by' => 'month', 'type' => $type] + $request->only(['from', 'to'])) }}"
                    class="chip @if ($by == 'month') selected @endif">Month</a>
                <a href="{{ route('admin.statistics.show', ['by' => 'year', 'type' => $type] + $request->only(['from', 'to'])) }}"
                    class="chip @if ($by == 'year') selected @endif">Year</a>
            </div>

            <a class="btn btn-secondary" href="{{ route('admin.statistics.show') }}">Reset</a>
            <a class="btn btn-tertiary" href="{{ route('admin.statistics.show', ['by' => $by, 'type' => $type, 'export' => true] + $request->only(['from', 'to'])) }}">
                <i class="material-icons">download</i> Export
            </a>
        </form>
    </header>

    @include('parts.graph')
@endsection
