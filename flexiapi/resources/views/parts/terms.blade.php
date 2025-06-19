<div class="large checkbox">
    <input id="terms" name="terms" type="checkbox">
    <label for="terms"></label>
    <div>
        <p>
            <i class="ph ph-file-text"></i><a href="{{ config('app.terms_of_use_url') }}">{{ __('I accept the Terms and Conditions') }}</a>
            @if (config('app.privacy_policy_url', null) != null)
                <br /><i class="ph ph-file-lock"></i><a href="{{ config('app.privacy_policy_url') }}">{{ __('I accept the Privacy policy') }}</a>
            @endif
        </p>
        @include('parts.errors', ['name' => 'terms'])
    </div>
</div>

@include('parts.captcha')
