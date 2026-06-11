@extends('layouts.auth')

@section('title', 'Sign In — Bibliotheca')

@section('content')
<div class="min-h-screen flex">

    {{-- ── Left decorative panel (hidden on mobile) ──────────────────────── --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-2/5 relative bg-zinc-900 flex-col overflow-hidden select-none">

        {{-- Ambient gradient overlay --}}
        <div class="absolute inset-0 bg-gradient-to-br from-amber-950/30 via-zinc-900 to-zinc-950 pointer-events-none"></div>

        {{-- Signature element: vertical book-spine typography --}}
        <div class="absolute inset-y-0 right-0 w-16 flex items-center justify-center border-l border-white/5">
            <span
                class="text-zinc-800 font-serif font-bold tracking-[0.4em] text-sm uppercase"
                style="writing-mode: vertical-rl; transform: rotate(180deg);"
            >Bibliotheca</span>
        </div>

        {{-- Panel content --}}
        <div class="relative z-10 flex flex-col justify-between h-full px-12 py-14 pr-20">

            {{-- Brand mark --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-500 rounded-sm flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-zinc-950" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                        <path d="M6 4h12v2H6zm0 14h12v2H6z"/>
                    </svg>
                </div>
                <span class="text-white font-serif text-xl tracking-wide">Bibliotheca</span>
            </div>

            {{-- Headline --}}
            <div class="space-y-6">
                <p class="text-amber-500/60 text-xs font-medium tracking-[0.2em] uppercase">
                    Member Access
                </p>
                <h1 class="text-white font-serif text-4xl xl:text-5xl font-light leading-tight">
                    Every great<br>
                    reader keeps<br>
                    <em class="not-italic text-amber-400">a library.</em>
                </h1>
                <p class="text-zinc-500 text-sm leading-relaxed max-w-xs">
                    Access your reservations, reading history, and curated recommendations from our collection.
                </p>
            </div>

            {{-- Footer detail --}}
            <p class="text-zinc-700 text-xs tracking-wide">
                &copy; {{ date('Y') }} Bibliotheca. All rights reserved.
            </p>
        </div>
    </div>

    {{-- ── Right form panel ────────────────────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-zinc-950">
        <div class="w-full max-w-sm">

            {{-- Mobile brand mark --}}
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-8 h-8 bg-amber-500 rounded-sm flex items-center justify-center">
                    <svg class="w-4 h-4 text-zinc-950" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 4h12v2H6zm0 14h12v2H6zM9 8h2v8H9zm4 0h2v8h-2z"/>
                    </svg>
                </div>
                <span class="text-white font-serif text-lg tracking-wide">Bibliotheca</span>
            </div>

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-white tracking-tight">Welcome back</h2>
                <p class="text-zinc-500 text-sm mt-1.5">Sign in to continue to your library</p>
            </div>

            {{-- Global error summary --}}
            @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
                <div class="mb-6 px-4 py-3 rounded-lg bg-red-950/50 border border-red-500/20">
                    @foreach ($errors->all() as $error)
                        <p class="text-red-400 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Login form --}}
            <form
                id="login-form"
                action="{{ route('auth.login.submit') }}"
                method="POST"
                novalidate
            >
                @csrf

                {{-- Email --}}
                <div class="mb-5">
                    <label for="email" class="block text-xs font-medium text-zinc-400 mb-2 uppercase tracking-wider">
                        Email address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full px-4 py-3 bg-zinc-800/80 border rounded-lg text-white text-sm placeholder:text-zinc-600
                               focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500/60
                               transition-colors duration-150
                               {{ $errors->has('email') ? 'border-red-500/50' : 'border-zinc-700/60' }}"
                    >
                    @error('email')
                        <p class="mt-2 text-xs text-red-400" id="email-error">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-red-400 hidden" id="email-client-error"></p>
                </div>

                {{-- Password --}}
                <div class="mb-7">
                    <label for="password" class="block text-xs font-medium text-zinc-400 mb-2 uppercase tracking-wider">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-3 bg-zinc-800/80 border rounded-lg text-white text-sm placeholder:text-zinc-600
                               focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500/60
                               transition-colors duration-150
                               {{ $errors->has('password') ? 'border-red-500/50' : 'border-zinc-700/60' }}"
                    >
                    @error('password')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-red-400 hidden" id="password-client-error"></p>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    id="submit-btn"
                    class="w-full py-3 px-6 bg-amber-500 hover:bg-amber-400 active:bg-amber-600
                           text-zinc-950 text-sm font-semibold rounded-lg
                           transition-colors duration-150
                           flex items-center justify-center gap-2
                           disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span id="btn-label">Sign in</span>
                    <svg
                        id="btn-spinner"
                        class="hidden w-4 h-4 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </form>

            {{-- Register link --}}
            <p class="text-center mt-6 text-zinc-500 text-sm">
                New to Bibliotheca?
                <a
                    href="{{ route('auth.register') }}"
                    class="text-amber-400 hover:text-amber-300 font-medium transition-colors duration-150 ml-1"
                >Create an account</a>
            </p>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var form    = document.getElementById('login-form');
    var btn     = document.getElementById('submit-btn');
    var label   = document.getElementById('btn-label');
    var spinner = document.getElementById('btn-spinner');

    var emailInput          = document.getElementById('email');
    var passwordInput       = document.getElementById('password');
    var emailClientError    = document.getElementById('email-client-error');
    var passwordClientError = document.getElementById('password-client-error');

    function showError(el, message) {
        el.textContent = message;
        el.classList.remove('hidden');
    }

    function clearError(el) {
        el.textContent = '';
        el.classList.add('hidden');
    }

    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    form.addEventListener('submit', function (e) {
        var valid = true;

        clearError(emailClientError);
        clearError(passwordClientError);

        var emailVal    = emailInput.value.trim();
        var passwordVal = passwordInput.value;

        if (emailVal === '') {
            showError(emailClientError, 'An email address is required.');
            valid = false;
        } else if (! isValidEmail(emailVal)) {
            showError(emailClientError, 'Please enter a valid email address.');
            valid = false;
        }

        if (passwordVal === '') {
            showError(passwordClientError, 'A password is required.');
            valid = false;
        }

        if (! valid) {
            e.preventDefault();
            return;
        }

        // Show loading state
        btn.disabled  = true;
        label.textContent = 'Signing in…';
        spinner.classList.remove('hidden');
    });
})();
</script>
@endsection