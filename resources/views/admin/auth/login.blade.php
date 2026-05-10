<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Admin Login') }} - {{ \App\Models\Setting::get('app_name', config('app.name', 'MailPurse')) }}</title>

    <!-- Fonts -->
    @php
        $fontFamily = \App\Models\Setting::get('admin_font_family', 'Inter');
        $fontWeights = \App\Models\Setting::get('admin_font_weights', '400,500,600,700');
        $fontWeightsUrl = preg_replace('/\s*,\s*/', ';', $fontWeights);
        $fontFamilyUrl = str_replace(' ', '+', $fontFamily);
        $googleFontsUrl = "https://fonts.googleapis.com/css2?family={$fontFamilyUrl}:wght@{$fontWeightsUrl}&display=swap";
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $googleFontsUrl }}" rel="stylesheet" />
    <style>
        body {
            --app-font-family: '{{ $fontFamily }}', sans-serif;
            font-family: var(--app-font-family);
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.meta-pixel')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900" style="--app-font-family: '{{ $fontFamily }}', sans-serif; font-family: var(--app-font-family);">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    {{ __('Admin Login') }}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Sign in to your admin account') }}
                </p>
            </div>

            <form class="mt-8 space-y-6" method="POST" action="{{ route('admin.login') }}" data-turbo="false">
                @csrf

                @if($errors->any())
                    <div class="rounded-md bg-red-50 dark:bg-red-900/50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ __('There were errors with your submission') }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">{{ __('Email address') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-t-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('Email address') }}"
                            value="{{ old('email') }}"
                        >
                    </div>
                    <div>
                        <label for="password" class="sr-only">{{ __('Password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-b-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('Password') }}"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            {{ __('Remember me') }}
                        </label>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        {{ __('Sign in') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

