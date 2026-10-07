@extends('layouts.main')

@section('breadcrumb')
    @if (auth()->user()->superAdmin)
        @include('admin.parts.breadcrumb.spaces.show')
    @else
        <li class="breadcrumb-item">
            <a href="{{ route('admin.spaces.me') }}">
                {{ $space->name }}
            </a>
        </li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">{{ __('Configuration') }}</li>
@endsection

@section('content')
    @include('admin.space.head')
    @include('admin.space.tabs')

    <form method="POST" action="{{ route('admin.spaces.configuration.update', $space->domain) }}" accept-charset="UTF-8"
        enctype="multipart/form-data">
        @csrf
        @method('put')

        <h3 class="large">{{ __('Platform customization') }}</h3>

        <div class="image-picker-group">
            <div class="picker">
                <canvas @if($space->logo) data-existing="{{ asset('storage/img/' . $space->logo) }}" @endif> </canvas>
                <input name="logo" type="file" accept="image/*" onchange="imagePicker.onLoad(this)">
                <input class="btn secondary" type="button" value="{{ __('Delete') }}" onclick="imagePicker.onDelete(this)" @if(!$space->logo) style="display:none;" @endif>
                <input type="hidden" name="logo_delete" value="0">
                @include('parts.errors', ['name' => 'logo'])
            </div>
        </div>
        <div class="color-picker-group">
            <input id="theme_hue" name="theme_hue" type="hidden" value="{{$space->theme_hue}}">
            <div class="picker">
                <div class="color-div"></div>
                <div class="color-selector">
                    <div class="color-input">
                        <input id="hex-color"type="text" value="" onblur="colorPicker.onInputChange(this)">
                        <label for="color-input">{{ __('Hex') }}</label>
                        <i class="ph ph-arrow-counter-clockwise" onclick="colorPicker.onReset(this, {{$space->theme_hue}})"></i>
                    </div>
                    <input class="color" type="range" min="0" max="360" value="{{$space->theme_hue}}" oninput="colorPicker.onSliderChange(this)">
                </div>
            </div>
        </div>

        <div class="large">
            <textarea name="copyright_text" id="copyright_text">{{ $space->copyright_text }}</textarea>
            <label for="copyright_text">{{ __('Copyright text') }}</label>
            @include('parts.errors', ['name' => 'copyright_text'])
        </div>

        <div class="large">
            <textarea name="intro_registration_text" id="intro_registration_text">{{ $space->intro_registration_text }}</textarea>
            <label for="intro_registration_text">{{ __('Registration introduction') }}</label>
            <span class="supporting">{{ __('Markdown text') }}</span>
            @include('parts.errors', ['name' => 'intro_registration_text'])
        </div>

        <div>
            <input name="newsletter_registration_address" id="newsletter_registration_address"
                placeholder="email@server.tld" type="email" value="{{ $space->newsletter_registration_address }}">
            <label for="newsletter_registration_address">{{ __('Newsletter registration email address') }}</label>
            <span class="supporting">{{ __('An email will be sent to this email when someone join the newsletter') }}</span>
            @include('parts.errors', ['name' => 'newsletter_registration_address'])
        </div>

        <div>
            <input name="account_proxy_registrar_address" id="account_proxy_registrar_address" placeholder="server.tld"
                value="{{ $space->account_proxy_registrar_address }}">
            <label for="account_proxy_registrar_address">{{ __('Account proxy registrar address') }}</label>
            <span class="supporting">{{ __('Will be used for informational purpose in the user panel and communication emails') }}</span>
            @include('parts.errors', ['name' => 'account_proxy_registrar_address'])
        </div>

        <h3 class="large">{{ __('Remote provisioning') }}</h3>

        <div class="large">
            <textarea name="custom_provisioning_entries" id="custom_provisioning_entries">{{ $space->custom_provisioning_entries }}</textarea>
            @include('parts.errors', ['name' => 'custom_provisioning_entries'])

            <div id="ini_helper">
                <div>
                    <span class="supporting">{{ __('In INI format. This field complement the settings configured in the other forms.') }}</span>
                    <span class="supporting">
                        <a target="_blank"  href="https://cheatsheets.zip/ini.html">{{ __('Checkout the cheatsheets to know how to format things correctly.') }}</a>
                    </span>
                    <span class="supporting">
                        {{ __('A complete documentation describing all the elements and sections presents in this field are available on the following link:') }}
                        <a target="_blank"  href="https://download.linphone.org/snapshots/docs/liblinphone/latest/c/group__group__provisioning__configuration__key.html">Provisioning configuration keys documentation.</a>
                    </span>
                </div>
                <div>
                    <pre data-type="{{ __('Exemple') }}"><code><span style="color: var(--grey-4)">; Here are the comments</span>
<span style="color: var(--color-green)">[section]</span>
<span style="color: var(--color-blue)">key</span>=Value
<span style="color: var(--color-blue)">sip_adress_with_comment</span>=sip:voip@sip.server.org<span style="color: var(--grey-4)">;transport=tcp</span>
<span style="color: var(--color-blue)">sip_adress</span>=<span style="color: var(--color-orange)">"sip:voip@sip.server.org;transport=tcp"</span>
<span style="color: var(--color-blue)">enabled</span>=1</code></pre>
                </div>
            </div>

            <label for="custom_provisioning_entries">{{ __('Custom entries') }}</label>
        </div>

        <div>
            @include('parts.form.toggle', [
                'object' => $space,
                'key' => 'custom_provisioning_overwrite_all',
                'label' => __('Allow client settings to be overwritten by the provisioning ones'),
            ])
        </div>

        <h3 class="large">{{ __('Features') }}</h3>
        <div>
            @include('parts.form.toggle', [
                'object' => $space,
                'key' => 'public_registration',
                'label' => __('Public registration'),
            ])
        </div>
        <div>
            @include('parts.form.toggle', [
                'object' => $space,
                'key' => 'phone_registration',
                'label' => __('Phone registration'),
            ])
        </div>
        <div>
            @include('parts.form.toggle', [
                'object' => $space,
                'key' => 'intercom_features',
                'label' => __('Intercom features'),
            ])
        </div>

        <div class="large">
            <input class="btn" type="submit" value="{{ __('Update') }}">
        </div>
    </form>
    <script src="{{ asset('scripts/colorPicker.js') }}"></script>
@endsection
