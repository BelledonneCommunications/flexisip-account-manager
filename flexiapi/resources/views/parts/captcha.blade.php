@if (captchaConfigured())
    <div class="large">
        <script src="https://hcaptcha.com/1/api.js?recaptchacompat=off" async="" defer=""></script>
        {!! HCaptcha::display() !!}
        @include('parts.errors', ['name' => 'h-captcha-response'])
    </div>
@endif
