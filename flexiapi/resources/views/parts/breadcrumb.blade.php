@hasSection('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('account.panel') }}">Home</a></li>
            @yield('breadcrumb')
        </ol>
    </nav>
@endif