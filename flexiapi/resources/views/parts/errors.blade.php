@if (isset($errors) && isset($name) && count($errors->get($name)) > 0)
    @foreach ($errors->get($name) as $error)
        <small class="error">
            {{ $error }}
        </small>
    @endforeach
@elseif (isset($errors) && $errors->isNotEmpty() && is_int($errors->keys()[0]) && !isset($name))
    <ul class="errors">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

