<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Loan Management') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="{{ auth()->check() ? 'app-body' : 'guest-body' }}">
    @auth
        @php($unreadCount = auth()->user()->unreadLoanNotifications()->count())

        <div id="app" class="app-frame">
            @include('layouts.sidebar')

            <div class="app-shell">
                @include('layouts.navbar')

                <main class="app-content">
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <div id="app" class="guest-shell">
            <div class="auth-layout">
                <section class="auth-showcase">
                    <div class="showcase-content">
                        <h1>Welcome to Loan Management System</h1>
                        <p>Securely apply for loans, follow application decisions, and track repayment schedules in one place.</p>
                    </div>
                    <small class="showcase-footer">&copy; {{ date('Y') }} Loan Management. All rights reserved.</small>
                </section>

                <main class="auth-panel">
                    <div class="auth-panel-inner">
                        <a href="{{ url('/') }}" class="auth-panel-brand">
                            <span class="brand-mark"><i class="bi bi-bank"></i></span>
                            <span>Loan Management</span>
                        </a>
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    @endauth

    @if (! request()->routeIs('login', 'register') && (session('success') || session('status') || session('error') || $errors->any()))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.Swal.fire({
                    @if (session('success') || session('status'))
                    icon: 'success',
                    title: 'Success',
                    text: @json(session('success') ?? session('status')),
                    confirmButtonColor: '#8a5a08'
                    @elseif (session('error'))
                    icon: 'error',
                    title: 'Error',
                    text: @json(session('error')),
                    confirmButtonColor: '#b42318'
                    @else
                    icon: 'error',
                    title: 'Please check your information',
                    text: @json($errors->first()),
                    confirmButtonColor: '#b42318'
                    @endif
                });
            });
        </script>
    @endif
</body>
</html>
