<div class="checkbox">
    <input
        id="{{ $key }}"
        type="checkbox"
        @if ((!isset($reversed) && $object->$key) || (isset($reversed) && $reversed && !$object->$key)) checked @endif name="{{ $key }}"
        @if(isset($attributes))
            @foreach($attributes as $name => $value)
                {{ $name }}="{{ $value }}"
            @endforeach
        @endif
        >

    <label for="{{ $key }}"></label>
    <div>
        <p>{{ $label }}</p>
        @if (isset($supporting))
            <span class="supporting">{{ $supporting }}</span>
        @endif
    </div>
</div>
