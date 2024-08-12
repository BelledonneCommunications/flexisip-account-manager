<div>
    @if (!isset($reverse) || !$reverse)
        <input name="{{ $key }}" value="true" type="radio" @if ($object->$key) checked @endif>
        @if (isset($reverse) && $reverse)<p>Disabled</p>@else<p>Enabled</p>@endif
    @endif
    <input name="{{ $key }}" value="false" type="radio" @if (!$object->$key) checked @endif>
    @if (isset($reverse) && $reverse)<p>Enabled</p>@else<p>Disabled</p>@endif
    <label>{{ $label }}</label>
    @if (isset($reverse) && $reverse)
        <input name="{{ $key }}" value="true" type="radio" @if ($object->$key) checked @endif>
        @if (isset($reverse) && $reverse)<p>Disabled</p>@else<p>Enabled</p>@endif
    @endif
</div>