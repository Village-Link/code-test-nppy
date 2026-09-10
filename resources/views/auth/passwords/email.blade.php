@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header">Reset Password</div>
        <div class="card-body">
            <p class="auth-intro">Enter your email and we will send you a password reset link.</p>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autocomplete="email" autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                <p class="text-center mt-4 mb-0"><a href="{{ route('login') }}" class="fw-bold"><i class="bi bi-arrow-left me-1"></i>Back to login</a></p>
            </form>
        </div>
    </div>
</div>
@endsection
