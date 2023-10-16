<div>
    <form class="inline" method="POST" action="{{ route('admin.statistics.edit') }}" accept-charset="UTF-8">
        @csrf
        @method('post')

        <input type="hidden" name="by" value="{{ $request->get('by', 'day') }}">
        <input type="hidden" name="type" value="{{ $type }}">

        <div>
            <input type="date" name="from" value="{{ $request->get('from') }}" onchange="this.form.submit()">
            <label for="from">From</label>
        </div>
        <div>
            <input type="date" name="to" value="{{ $request->get('to') }}" onchange="this.form.submit()">
            <label for="to">To</label>
        </div>

        <div class="large on_desktop"></div>

        @if (config('app.admins_manage_multi_domains'))
            <div class="select">
                <select name="domain" onchange="this.form.submit()">
                    <option value="">
                        Select a domain
                    </option>
                    @foreach ($domains as $d)
                        <option value="{{ $d }}"
                            @if (request()->get('domain', '') == $d) selected="selected" @endif>
                            {{ $d }}
                        </option>
                    @endforeach
                </select>
                <label for="domain">Domain</label>
            </div>
        @endif

        <div class="select">
            <select name="contacts_list" onchange="this.form.submit()">
                <option value="">
                    Select a contacts list
                </option>
                @foreach ($contacts_lists as $key => $name)
                    <option value="{{ $key }}"
                        @if (request()->get('contacts_list', '') == $key) selected="selected" @endif>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            <label for="contacts_list">Contacts list</label>
        </div>

        <div>
            <a href="{{ route('admin.statistics.show', ['by' => 'day', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'day') selected @endif">Day</a>
            <a href="{{ route('admin.statistics.show', ['by' => 'week', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'week') selected @endif">Week</a>
            <a href="{{ route('admin.statistics.show', ['by' => 'month', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'month') selected @endif">Month</a>
            <a href="{{ route('admin.statistics.show', ['by' => 'year', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'year') selected @endif">Year</a>
        </div>

        <div class="oppose">
            <a class="btn btn-secondary" href="{{ route('admin.statistics.show') }}">Reset</a>
            <a class="btn btn-tertiary"
                href="{{ route('admin.statistics.show', ['by' => $request->get('by', 'day'), 'type' => $type, 'export' => true] + $request->only(['from', 'to', 'domain'])) }}">
                <i class="material-icons-outlined">download</i> Export
            </a>
        </div>
    </form>
</div>
