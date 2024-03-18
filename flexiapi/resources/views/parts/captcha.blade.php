@if (captchaConfigured())
    <div class="large">
        {!! HCaptcha::renderJs() !!}
        {!! HCaptcha::display() !!}
        @include('parts.errors', ['name' => 'h-captcha-response'])
    </div>
@endif
