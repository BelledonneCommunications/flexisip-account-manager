<div class="checkbox">
    <input id="{{ $key }}" type="checkbox" @if ($object->$key || (isset($reversed) && $reversed && !$object->$key)) checked @endif name="{{ $key }}">
    <label for="{{ $key }}"></label>
    <div>
        <p>{{ $label }}</p>
        @if (isset($supporting))
            <span class="supporting">{{ $supporting }}</span>
        @endif
    </div>
</div>