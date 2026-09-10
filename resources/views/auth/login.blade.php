@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header">Welcome back</div>
        <div class="card-body">
            <p class="auth-intro">Sign in to continue to your loan management account.</p>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autocomplete="email" autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small fw-bold">Forgot password?</a>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                @if (Route::has('register'))
                    <p class="text-center text-muted mt-4 mb-0">New customer? <a href="{{ route('register') }}" class="fw-bold">Create an account</a></p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
