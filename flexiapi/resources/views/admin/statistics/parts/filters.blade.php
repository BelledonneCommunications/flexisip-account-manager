<div>
    <form class="inline" method="POST" action="{{ route('admin.statistics.edit') }}" accept-charset="UTF-8">
        @csrf
        @method('post')

        <input type="hidden" name="by" value="{{ $request->get('by', 'day') }}">
        <input type="hidden" name="type" value="{{ $type }}">

        <div>
            <input type="date" name="from" value="{{ $request->get('from') }}" onchange="this.form.submit()">
            <label for="from">{{ __('From') }}</label>
        </div>
        <div>
            <input type="date" name="to" value="{{ $request->get('to') }}" onchange="this.form.submit()">
            <label for="to">{{ __('To') }}</label>
        </div>

        <div class="large">
            <a href="{{ route('admin.statistics.show', ['by' => 'day', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'day') selected @endif">{{ __('Day') }}</a>
            <a href="{{ route('admin.statistics.show', ['by' => 'week', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'week') selected @endif">{{ __('Week') }}</a>
            <a href="{{ route('admin.statistics.show', ['by' => 'month', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'month') selected @endif">{{ __('Month') }}</a>
            <a href="{{ route('admin.statistics.show', ['by' => 'year', 'type' => $type] + $request->only(['from', 'to', 'domain', 'contacts_list'])) }}"
                class="chip @if ($request->get('by', 'day') == 'year') selected @endif">{{ __('Year') }}</a>
        </div>

        @include('admin.account.parts.forms.select_domain')

        <div class="select">
            <select name="contacts_list" onchange="this.form.submit()">
                <option value="">
                    {{ __('Select a contacts list') }}
                </option>
                @foreach ($contacts_lists as $key => $name)
                    <option value="{{ $key }}"
                        @if (request()->get('contacts_list', '') == $key) selected="selected" @endif>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            <label for="contacts_list">{{ __('Contacts Lists') }}</label>
        </div>

        <div class="oppose large">
            <a class="btn btn-secondary" href="{{ route('admin.statistics.show') }}">{{ __('Reset') }}</a>
            <a class="btn btn-tertiary"
                href="{{ route('admin.statistics.show', ['by' => $request->get('by', 'day'), 'type' => $type, 'export' => true] + $request->only(['from', 'to', 'domain'])) }}">
                <i class="ph">download-simple</i> {{ __('Export') }}
            </a>
        </div>
    </form>
</div>
