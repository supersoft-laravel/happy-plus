@extends('layouts.authentication.master')
@section('title', 'Login')

@section('css')
    <style>
        /* Normal text */
        input.form-control,
        input.form-control:focus {
            color: #fff !important;
            -webkit-text-fill-color: #fff !important;
            /* Chrome autofill fix */
        }

        /* Placeholder */
        input.form-control::placeholder {
            color: #fff !important;
            opacity: 1 !important;
        }

        /* Chrome / Safari */
        input.form-control::-webkit-input-placeholder {
            color: #fff !important;
        }

        /* Firefox */
        input.form-control::-moz-placeholder {
            color: #fff !important;
            opacity: 1 !important;
        }

        /* Edge */
        input.form-control:-ms-input-placeholder {
            color: #fff !important;
        }

        /* Chrome yellow autofill background fix */
        input:-webkit-autofill,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
            -webkit-text-fill-color: #fff !important;
        }
    </style>


@endsection

@section('content')

    <h4 class="mb-1" style="color: #fff;">{{ __('Welcome to') }} {{ \App\Helpers\Helper::getCompanyName() }}!</h4>
    <p class="mb-6" style="color: #fff;">{{ __('Please sign-in to your account and start the adventure') }}</p>

    <form id="formLogin" class="mb-6" action="{{ route('login.attempt') }}" method="POST">
        @csrf
        <div class="mb-6">
            <label style="color: #fff;" for="email_username" class="form-label">{{ __('Email/Username') }}</label><span
                class="text-danger">*</span>
            <input style="color: #fff;" type="text" class="form-control @error('email_username') is-invalid @enderror"
                id="email_username" name="email_username" placeholder="{{ __('Enter your email or username') }}" autofocus
                required />
            @error('email_username')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="mb-6 form-password-toggle">
            <label style="color: #fff;" class="form-label" for="password">{{ __('Password') }}</label><span
                class="text-danger">*</span>
            <div class="input-group input-group-merge">
                <input style="color: #fff;" type="password" id="password"
                    class="form-control @error('password') is-invalid @enderror" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" required />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="my-8">
            <div class="d-flex justify-content-between">
                <div class="form-check mb-0 ms-2">
                    <input style="color: #fff;" class="form-check-input" type="checkbox" name="remember" id="remember-me" />
                    <label style="color: #fff;" class="form-check-label" for="remember-me"> {{ __('Remember Me') }} </label>
                </div>
                {{-- @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                        <p style="color: #fff;" class="mb-0">{{ __('Forgot Password?') }}</p>
                    </a>
                @endif --}}
            </div>
        </div>
        <div class="my-8">
            @if (config('captcha.version') === 'v3')
                {!! \App\Helpers\Helper::renderRecaptcha('formLogin', 'register') !!}
            @elseif(config('captcha.version') === 'v2')
                <div class="form-field-block">
                    {!! app('captcha')->display() !!}
                    @if ($errors->has('g-recaptcha-response'))
                        <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
                    @endif
                </div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100">{{ __('Sign in') }}</button>
    </form>
@endsection

@section('script')
    {!! NoCaptcha::renderJs() !!}
@endsection
