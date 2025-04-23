@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Contacts') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">users</i> {{ $account->identifier }}</h1>
    </header>
    @include('admin.account.parts.tabs')

    <a class="btn small oppose" href="{{ route('admin.account.contact.create', $account) }}">
        <i class="ph">plus</i> {{ __('Add') }}
    </a>
    <h3>
        {{ __('Contacts') }}
    </h3>
    <table>
        <tbody>
            @if ($account->contacts->isEmpty())
                <tr class="empty">
                    <td colspan="2">{{ __('Empty') }}</td>
                </tr>
            @else
                @foreach ($account->contacts as $contact)
                    <tr>
                        <td>
                            <a href="{{ route('admin.account.edit', $account) }}">{{ $contact->identifier }}</a>
                        </td>
                        <td class="actions">
                            <a type="button" class="btn small tertiary" href="{{ route('admin.account.contact.delete', [$account, $contact->id]) }}">
                                <i class="ph">trash</i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

        <hr />

    <h3 id="contacts_lists">{{ __('Contacts Lists') }}</h3>

    <table>
        <tbody>
        @foreach ($account->contactsLists as $contactsList)
            <tr>
                <td>
                    <a href="{{ route('admin.contacts_lists.edit', ['contacts_list_id' => $contactsList->id]) }}">{{ $contactsList->title }}</a>
                    <small>{{ $contactsList->contacts_count }} {{ __('Contacts') }}</small>
                </td>
                <td class="actions">
                    <a type="button" class="btn small tertiary"  href="{{ route('admin.account.contacts_lists.detach', ['account_id' => $account->id, 'contacts_list_id' => $contactsList->id]) }}">
                        <i class="ph">trash</i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if ($contacts_lists->isNotEmpty())
        <div class="card">
            <div class="grid">
                <div>
                    <h4><i class="ph">plus</i> {{ __('Add') }}</h4>
                    <p>{{ __('Add existing contacts lists to display them in the user applications.') }}</p>
                </div>
                <div>
                    <form method="POST" action="{{ route('admin.account.contacts_lists.attach', $account->id) }}"
                        accept-charset="UTF-8">
                        @csrf
                        @method('post')

                        <div class="select">
                            <select name="contacts_list_id" onchange="this.form.submit()">
                                <option>
                                    {{ __('Contacts Lists') }}
                                </option>
                                @foreach ($contacts_lists as $contacts_list)
                                    <option value="{{ $contacts_list->id }}">
                                        {{ $contacts_list->title }}
                                    </option>
                                @endforeach
                                <label></label>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection