@if (isset($errors) && $errors->isNotEmpty())
    <ul class="errors">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
