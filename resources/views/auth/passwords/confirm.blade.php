@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header">Confirm Password</div>
        <div class="card-body">
            <p class="auth-intro">Please confirm your password before continuing.</p>
            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf
                <div class="mb-4">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Confirm Password</button>
                @if (Route::has('password.request'))
                    <p class="text-center mt-4 mb-0"><a href="{{ route('password.request') }}" class="fw-bold">Forgot your password?</a></p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
