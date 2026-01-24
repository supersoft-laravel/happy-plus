@extends('layouts.mails.master')

@section('title', 'Password Reset OTP')

@section('css')
@endsection

@section('content')
    <div class="container text-center" style="max-width: 600px; margin: 0 auto;">
        <p>{{ __('Hi') }} <strong>{{ $user->name }}</strong>,</p>
        <p>{{ __('Your OTP for resetting your password is:') }}</p>

        <div class="bg-light border rounded py-4 my-4 w-100 text-center">
            <h1 class="display-4 fw-bold text-primary mb-0" style="letter-spacing: 8px;">
                {{ $otp }}
            </h1>
        </div>

        <p class="mt-3">{{ __('This code will expire in 10 minutes. Please do not share it with anyone.') }}</p>
        <p class="mt-3">{{ __('Thank you') }}</p>
    </div>
@endsection

@section('script')
@endsection

