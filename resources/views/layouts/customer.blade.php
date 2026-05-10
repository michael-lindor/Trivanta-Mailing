<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = trim($__env->yieldContent('title', 'Dashboard'));
    @endphp
    @php
        try {
            $siteTitle = \App\Models\Setting::get('site_title', \App\Models\Setting::get('app_name', config('app.name', 'MailPurse')));
            $faviconPath = \App\Models\Setting::get('site_favicon');
            $metaDescription = \App\Models\Setting::get('meta_description');
            $metaKeywords = \App\Models\Setting::get('meta_keywords');
            $siteMeta = \App\Models\Setting::get('site_meta');
        } catch (\Throwable $e) {
            $siteTitle = config('app.name', 'MailPurse');
            $faviconPath = null;
            $metaDescription = null;
            $metaKeywords = null;
            $siteMeta = null;
        }

        if (!is_string($siteTitle) || trim($siteTitle) === '') {
            $siteTitle = config('app.name', 'MailPurse');
        }

        $brandingDisk = (string) config('filesystems.branding_disk', 'public');
        $faviconUrl = null;
        if (is_string($faviconPath) && trim($faviconPath) !== '') {
            $faviconUrl = $brandingDisk === 'public'
                ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($faviconPath, '/'))
                : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($faviconPath);
        }
    @endphp

    @if(is_string($metaDescription) && trim($metaDescription) !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if(is_string($metaKeywords) && trim($metaKeywords) !== '')
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    
    @if(is_string($faviconUrl) && trim($faviconUrl) !== '')
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif

    @if(is_string($siteMeta) && trim($siteMeta) !== '' && preg_match('/<\s*(meta|link|script|style|base|noscript)\b/i', $siteMeta))
        @php
            $siteMetaSafe = '';
            if (preg_match_all('/<\s*(meta|link|base)\b[^>]*\/?>/i', $siteMeta, $m1)) {
                $siteMetaSafe .= implode("\n", $m1[0]) . "\n";
            }
            if (preg_match_all('/<\s*(script|style|noscript)\b[^>]*>.*?<\s*\/\s*\\1\s*>/is', $siteMeta, $m2)) {
                $siteMetaSafe .= implode("\n", $m2[0]) . "\n";
            }
            $siteMetaSafe = trim($siteMetaSafe);
        @endphp
        @if($siteMetaSafe !== '')
            {!! $siteMetaSafe !!}
        @endif
    @endif

    <title>{{ __($pageTitle) }} - {{ $siteTitle }}</title>

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
            font-family: '{{ $fontFamily }}', sans-serif;
        }
    </style>

    <script>
        window.__mailpurseSupportedGoogleFontFamilies = @json(config('mailpurse.fonts.supported_google_families', []));
    </script>

    @php
        try {
            $brandColor = \App\Models\Setting::get('brand_color', '#3b82f6');
        } catch (\Throwable $e) {
            $brandColor = '#3b82f6';
        }

        $brandColor = is_string($brandColor) ? trim($brandColor) : '#3b82f6';
        if ($brandColor === '' || !preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $brandColor)) {
            $brandColor = '#3b82f6';
        }

        $brandHex = ltrim($brandColor, '#');
        if (strlen($brandHex) === 3) {
            $brandHex = $brandHex[0] . $brandHex[0] . $brandHex[1] . $brandHex[1] . $brandHex[2] . $brandHex[2];
        }
        $brandR = hexdec(substr($brandHex, 0, 2));
        $brandG = hexdec(substr($brandHex, 2, 2));
        $brandB = hexdec(substr($brandHex, 4, 2));
    @endphp
    <style>
        :root {
            --brand-color: {{ $brandColor }};
            --brand-rgb: {{ $brandR }}, {{ $brandG }}, {{ $brandB }};
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    <style>
        aside[data-sidebar="app"][data-collapsed="true"] nav p {
            display: none;
        }

        aside[data-sidebar="app"][data-collapsed="true"] nav a,
        aside[data-sidebar="app"][data-collapsed="true"] nav button {
            justify-content: center;
            gap: 0;
        }

        aside[data-sidebar="app"][data-collapsed="true"] nav button > svg {
            display: none;
        }

        aside[data-sidebar="app"][data-collapsed="true"] nav .border-l {
            display: none;
        }

        aside[data-sidebar="app"][data-collapsed="true"] .sidebar-logo-img {
            width: 44px;
            margin-left: 0.75rem;
            margin-right: 0.75rem;
        }

        aside[data-sidebar="app"][data-collapsed="true"] .sidebar-user-info {
            display: none;
        }

        @media (min-width: 1024px) {
            aside[data-sidebar="app"][data-collapsed="true"] .sidebar-header-row {
                justify-content: center;
            }
            aside[data-sidebar="app"][data-collapsed="true"] .sidebar-logo-area {
                display: none;
            }
        }

        #sidebar-collapsed-tooltip {
            position: fixed;
            background: #1e2433;
            color: #fff;
            padding: 5px 12px;
            border-radius: 7px;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 500;
            z-index: 99999;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
            display: none;
            transform: translateY(-50%);
        }
        #sidebar-collapsed-tooltip-arrow {
            position: fixed;
            border: 6px solid transparent;
            border-right-color: #1e2433;
            z-index: 99999;
            pointer-events: none;
            display: none;
            transform: translateY(-50%);
        }
    </style>

    @php
        try {
            $googleTagRaw = \App\Models\Setting::get('google_analytics_tracking_id');
        } catch (\Throwable $e) {
            $googleTagRaw = null;
        }

        $googleTagId = is_string($googleTagRaw) ? strtoupper(trim($googleTagRaw)) : null;
        if (!is_string($googleTagId) || !preg_match('/^(G-[A-Z0-9]{4,}|GTM-[A-Z0-9]{4,})$/', $googleTagId)) {
            $googleTagId = null;
        }

        $isGtmContainer = is_string($googleTagId) && str_starts_with($googleTagId, 'GTM-');
        $isGoogleTag = is_string($googleTagId) && str_starts_with($googleTagId, 'G-');
    @endphp
    @if($isGtmContainer)
        <script>
            (function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                const f = d.getElementsByTagName(s)[0];
                const j = d.createElement(s);
                const dl = l !== 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(i) + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '{{ $googleTagId }}');
        </script>
    @elseif($isGoogleTag)
        <!-- Global site tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($googleTagId) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{ $googleTagId }}');
        </script>
    @endif

    @include('partials.meta-pixel')
 </head>
 @php
     $disableMainScroll = request()->routeIs('customer.ai-tools.email-text-generator');
     $forceSidebarCollapsed = trim($__env->yieldContent('force-sidebar-collapsed')) !== '';
    $isCampaign = request()->routeIs('customer.campaigns.create') || request()->routeIs('customer.campaigns.edit');
     $appLogo = null;
 @endphp
 <body class="customer-theme font-sans antialiased bg-admin-main text-admin-text-primary {{ $disableMainScroll ? 'lg:h-screen lg:overflow-hidden' : '' }}" style="--app-font-family: '{{ $fontFamily }}', sans-serif; font-family: var(--app-font-family);">
     @if($isGtmContainer)
         <noscript>
             <iframe src="https://www.googletagmanager.com/ns.html?id={{ rawurlencode($googleTagId) }}" height="0" width="0" style="display:none;visibility:hidden"></iframe>
         </noscript>
     @endif 
     <div
         class="flex {{ $disableMainScroll ? 'lg:h-screen lg:overflow-hidden' : 'min-h-screen' }}"
         x-data="{
             sidebarOpen: false,
             sidebarCollapsed: @js($forceSidebarCollapsed) || (localStorage.getItem('customerSidebarCollapsed') === 'true'),
             toggleSidebarCollapsed() {
                 this.sidebarCollapsed = !this.sidebarCollapsed;
                 localStorage.setItem('customerSidebarCollapsed', this.sidebarCollapsed ? 'true' : 'false');
             }
         }"
         :class="sidebarCollapsed ? '{{ app('locale.direction')->dir() === 'rtl' ? 'lg:pr-[45px]' : 'lg:pl-[45px]' }}' : '{{ app('locale.direction')->dir() === 'rtl' ? 'lg:pr-64' : 'lg:pl-64' }}'"
     >
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="sidebarOpen = false"></div>
        <!-- Sidebar -->
        <aside
            data-sidebar="app"
            class="bg-white dark:bg-admin-sidebar fixed inset-y-0 left-0 z-40 h-screen w-64 border-r border-gray-100 dark:border-admin-border flex flex-col transform -translate-x-full lg:translate-x-0 transition-all duration-200"
            :data-collapsed="sidebarCollapsed ? 'true' : 'false'"
            :class="[(sidebarOpen ? 'translate-x-0' : ''), (sidebarCollapsed ? 'lg:w-auto' : 'lg:w-64')]"
        >
             <div class="h-full">
                 <div class="flex flex-col items-start justify-between p-1 relative h-full">
                     <div class="flex flex-col gap-4 items-start relative w-full flex-1 min-h-0">
                         <!-- Logo -->
                         <div class="sidebar-header-row flex items-center justify-between w-full">
                           <a href="{{ route('customer.dashboard') }}" class="sidebar-logo-area relative shrink-0">
                               @php
                                    $appLogo = null;

                                    $appLogoDark = null;
                                    $brandingDisk = (string) config('filesystems.branding_disk', 'public');

                                    try {
                                        $appLogo = \App\Models\Setting::get('app_logo');
                                        $appLogoDark = \App\Models\Setting::get('app_logo_dark');
                                    } catch (\Throwable $e) {
                                        $appLogo = null;
                                        $appLogoDark = null;
                                    }
                                @endphp
    
                                @if(isset($appLogo) && is_string($appLogo) && trim($appLogo) !== '')
                                    <img
                                        src="{{ $brandingDisk === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogo, '/')) : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($appLogo) }}"
                                        alt="{{ __('App Logo') }}"
                                        class="sidebar-logo-img block dark:hidden h-auto object-contain w-[150px] mx-1 my-1"
                                    />

                                    @if(isset($appLogoDark) && is_string($appLogoDark) && trim($appLogoDark) !== '')
                                        <img
                                            src="{{ $brandingDisk === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogoDark, '/')) : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($appLogoDark) }}"
                                            alt="{{ __('App Logo') }}"
                                            class="sidebar-logo-img hidden dark:block h-auto object-contain w-[150px] mx-1 my-1"
                                        />
                                    @endif
                                @else
                                    <span class="block text-xl font-bold text-admin-text-primary px-3 py-2">
                                        {{ \App\Models\Setting::get('app_name', config('app.name', 'MailPurse')) }}
                                    </span>
                                @endif
                            </a>

                             <!-- Desktop: sidebar collapse toggle (panel icon) -->
                             <button
                                 type="button"
                                 class="hidden lg:inline-flex items-center justify-center p-2 rounded-lg text-gray-500 dark:text-admin-text-secondary hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition-colors flex-shrink-0"
                                 @click="toggleSidebarCollapsed()"
                                 :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                             >
                                 <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                     <rect x="3" y="3" width="18" height="18" rx="2"/>
                                     <path d="M9 3v18"/>
                                 </svg>
                             </button>

                             <!-- Mobile: close button -->
                             <button
                                 type="button"
                                 class="lg:hidden p-2 rounded-md text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5"
                                 @click="sidebarOpen = false"
                                 aria-label="{{ __('Close sidebar') }}"
                             >
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                 </svg>
                             </button>
                         </div>
    
                        <!-- Navigation -->
                        <nav class="flex flex-col gap-6 items-start relative w-full flex-1 min-h-0 overflow-y-auto">
                            <div class="flex flex-col gap-1 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-2">{{ __('General') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 mx-auto m-0" :class="sidebarCollapsed ? 'w-auto' : 'w-full'">
                                    {{-- ADD DASHBOARD PAGE HERE --}}
                                    <a href="{{ route('customer.dashboard') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.dashboard') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gauge-icon lucide-gauge"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                                        </div> 
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Dashboard') }}</p>
                                    </a>                                    
                                    

                                    <a href="{{ route('customer.analytics.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.analytics.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-combined-icon lucide-chart-no-axes-combined"><path d="M12 16v5"/><path d="M16 14v7"/><path d="M20 10v11"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.707 0L2 15"/><path d="M4 18v3"/><path d="M8 14v7"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Analytics') }}</p>
                                    </a>

                                    <div x-data="{ open: {{ request()->routeIs('customer.templates.*') ? 'true' : 'false' }} }" class="w-full">
                                        <button type="button" @click="open = true; window.location.href = '{{ route('customer.templates.index') }}'" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.templates.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-template-icon lucide-layout-template"><rect width="18" height="7" x="3" y="3" rx="1"/><rect width="9" height="7" x="3" y="14" rx="1"/><rect width="5" height="7" x="16" y="14" rx="1"/></svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px] flex-1 text-left">{{ __('Templates') }}</p>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="open ? 'rotate-180' : ''">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div x-cloak x-show="open" class="mt-1 flex flex-col gap-1 {{ app('locale.direction')->dir() === 'rtl' ? 'mr-6 border-r pr-4' : 'ml-6 border-l pl-4' }} border-gray-200 dark:border-white/10">
                                            <a href="{{ route('customer.templates.index') }}" class="flex items-center w-full py-2 text-sm {{ (request()->routeIs('customer.templates.*') && (request()->query('type') === null || request()->query('type') === 'email')) ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Email templates') }}
                                            </a>
                                            <a href="{{ route('customer.templates.index', ['type' => 'footer']) }}" class="flex items-center w-full py-2 text-sm {{ (request()->routeIs('customer.templates.*') && request()->query('type') === 'footer') ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Footer templates') }}
                                            </a>
                                            <a href="{{ route('customer.templates.index', ['type' => 'signature']) }}" class="flex items-center w-full py-2 text-sm {{ (request()->routeIs('customer.templates.*') && request()->query('type') === 'signature') ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Signature') }}
                                            </a>
                                        </div>
                                    </div>

                                    @customercan('api.permissions.can_access_api')
                                        <a href="{{ route('customer.api.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.api.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-webhook-icon lucide-webhook"><path d="M18 16.98h-5.99c-1.1 0-1.95.94-2.48 1.9A4 4 0 0 1 2 17c.01-.7.2-1.4.57-2"/><path d="m6 17 3.13-5.78c.53-.97.1-2.18-.5-3.1a4 4 0 1 1 6.89-4.06"/><path d="m12 6 3.13 5.73C15.66 12.7 16.9 13 18 13a4 4 0 0 1 0 8"/></svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('API') }}</p>
                                        </a>
                                    @endcustomercan

                                    @customercan('ai_tools.permissions.can_access_ai_tools')
                                        <a href="{{ route('customer.ai-tools.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.ai-tools.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wand-icon lucide-wand"><path d="M15 4V2"/><path d="M15 16v-2"/><path d="M8 9h2"/><path d="M20 9h2"/><path d="M17.8 11.8 19 13"/><path d="M15 9h.01"/><path d="M17.8 6.2 19 5"/><path d="m3 21 9-9"/><path d="M12.2 6.2 11 5"/></svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('AI Tools') }}</p>
                                        </a>
                                    @endcustomercan

                                    @customercan('servers.permissions.can_access_delivery_servers')
                                        <a href="{{ route('customer.integrations.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.integrations.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-plus-icon lucide-package-plus"><path d="M12 22V12"/><path d="M16 17h6"/><path d="M19 14v6"/><path d="M21 10.535V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.729l7 4a2 2 0 0 0 2 .001l1.675-.955"/><path d="M3.29 7 12 12l8.71-5"/><path d="m7.5 4.27 8.997 5.148"/></svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Integrations') }}</p>
                                        </a>
                                    @endcustomercan
                                </div>
                            </div>
    
                            <div class="flex flex-col gap-1 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-2">{{ __('Marketing') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-auto mx-auto m-0" :class="sidebarCollapsed ? 'w-auto' : 'w-full'">
                                    <a href="{{ route('customer.campaigns.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.campaigns.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-megaphone-icon lucide-megaphone"><path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/><path d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14"/><path d="M8 6v8"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Campaigns') }}</p>
                                    </a>

                                    @if(\App\Models\Addon::isActive('cold-email-outreach'))
                                        @customercan('outreach.access')
                                        <a href="{{ route('customer.outreach.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.outreach.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Outreach') }}</p>
                                        </a>
                                        @endcustomercan
                                    @endif

                                    <a href="{{ route('customer.auto-responders.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.auto-responders.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-reply-icon lucide-message-circle-reply"><path d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"/><path d="m10 15-3-3 3-3"/><path d="M7 12h8a2 2 0 0 1 2 2v1"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Auto Responders') }}</p>
                                    </a>

                                    @customercan('automations.enabled')
                                        <a href="{{ route('customer.automations.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.automations.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-git-branch-plus-icon lucide-git-branch-plus"><path d="M6 3v12"/><path d="M18 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M6 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M15 6a9 9 0 0 0-9 9"/><path d="M18 15v6"/><path d="M21 18h-6"/></svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Automation') }}</p>
                                        </a>
                                    @endcustomercan
    
                                    @php
                                        $listsTab = (string) request()->input('tab', 'lists');
                                        $listsMenuOpen = request()->routeIs('customer.lists.*') || request()->routeIs('customer.forms.*') || request()->routeIs('customer.tags.*');
                                        $listsTabIsOverview = request()->routeIs('customer.lists.index') && $listsTab === 'overview';
                                        $listsTabIsLists = request()->routeIs('customer.lists.*') && in_array($listsTab, ['', 'lists'], true);
                                        $listsTabIsSegments = request()->routeIs('customer.lists.index') && $listsTab === 'segments';
                                        $listsTabIsTags = request()->routeIs('customer.tags.*') || (request()->routeIs('customer.lists.index') && $listsTab === 'tags');
                                        $listsTabIsForms = request()->routeIs('customer.forms.*') || (request()->routeIs('customer.lists.index') && $listsTab === 'forms');
                                    @endphp

                                    <div x-data="{ open: {{ $listsMenuOpen ? 'true' : 'false' }} }" class="w-full">
                                        <button type="button" @click="open = true; window.location.href = '{{ route('customer.lists.index', ['tab' => 'overview']) }}'" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ $listsMenuOpen ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-contact-round-icon lucide-contact-round"><path d="M16 2v2"/><path d="M17.915 22a6 6 0 0 0-12 0"/><path d="M8 2v2"/><circle cx="12" cy="12" r="4"/><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px] flex-1 text-left">{{ __('Lists') }}</p>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="open ? 'rotate-180' : ''">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div x-cloak x-show="open" class="mt-1 flex flex-col gap-1 {{ app('locale.direction')->dir() === 'rtl' ? 'mr-6 border-r pr-4' : 'ml-6 border-l pl-4' }} border-gray-200 dark:border-white/10">
                                            <a href="{{ route('customer.lists.index', ['tab' => 'overview']) }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsOverview ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Overview') }}
                                            </a>
                                            <a href="{{ route('customer.lists.index') }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsLists ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Lists') }}
                                            </a>
                                            <a href="{{ route('customer.lists.index', ['tab' => 'segments']) }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsSegments ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Segments') }}
                                            </a>
                                            <a href="{{ route('customer.tags.index') }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsTags ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Tags') }}
                                            </a>
                                            <a href="{{ route('customer.forms.index') }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsForms ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Forms & Popups') }}
                                            </a>
                                        </div>
                                    </div>

                                    <a href="{{ route('customer.email-validation.runs.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.email-validation.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-search-icon lucide-mail-search"><path d="M22 12.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h7.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M18 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><circle cx="18" cy="18" r="3"/><path d="m22 22-1.5-1.5"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Email Validation') }}</p>
                                    </a>

                                    {{-- SuperScrape: Lead Scraper --}}
                                    @if(\App\Models\Addon::isActive('super-scrape'))
                                        @customercan('scraper.permissions.can_access_scraper')
                                        <div x-data="{ open: {{ request()->routeIs('customer.scraper.*') ? 'true' : 'false' }} }" class="w-full">
                                            <button type="button" @click="open = !open" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.scraper.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                                                </div>
                                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px] flex-1 text-left">{{ __('Lead Scraper') }}</p>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="open ? 'rotate-180' : ''">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            <div x-cloak x-show="open" class="mt-1 flex flex-col gap-1 {{ app('locale.direction')->dir() === 'rtl' ? 'mr-6 border-r pr-4' : 'ml-6 border-l pl-4' }} border-gray-200 dark:border-white/10">
                                                @customercan('scraper.google.can_access')
                                                <a href="{{ route('customer.scraper.index') }}" class="flex items-center w-full py-2 text-sm {{ request()->routeIs('customer.scraper.*') ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                    {{ __('Google') }}
                                                </a>
                                                @endcustomercan

                                                @foreach(['Instagram', 'LinkedIn', 'TikTok', 'Facebook', 'X'] as $platform)
                                                <span class="flex items-center justify-between w-full py-2 text-sm text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                                    {{ $platform }}
                                                    <span class="text-[9px] font-bold uppercase tracking-wide bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 px-1.5 py-0.5 rounded">{{ __('Soon') }}</span>
                                                </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endcustomercan
                                    @endif
                                </div>
                            </div>
    
                            <div class="flex flex-col gap-1 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-2">{{ __('Delivery') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-auto mx-auto m-0" :class="sidebarCollapsed ? 'w-auto' : 'w-full'">
                                    <a href="{{ route('customer.delivery-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.delivery-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck-electric-icon lucide-truck-electric"><path d="M14 19V7a2 2 0 0 0-2-2H9"/><path d="M15 19H9"/><path d="M19 19h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62L18.3 9.38a1 1 0 0 0-.78-.38H14"/><path d="M2 13v5a1 1 0 0 0 1 1h2"/><path d="M4 3 2.15 5.15a.495.495 0 0 0 .35.86h2.15a.47.47 0 0 1 .35.86L3 9.02"/><circle cx="17" cy="19" r="2"/><circle cx="7" cy="19" r="2"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Delivery Servers') }}</p>
                                    </a>

                                    <a href="{{ route('customer.warmups.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.warmups.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-flame-icon lucide-flame"><path d="M12 3q1 4 4 6.5t3 5.5a1 1 0 0 1-14 0 5 5 0 0 1 1-3 1 1 0 0 0 5 0c0-2-1.5-3-1.5-5q0-2 2.5-4"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Email Warmups') }}</p>
                                    </a>
    
                                    @customercan('servers.permissions.can_access_bounce_servers')
                                    <a href="{{ route('customer.bounce-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.bounce-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-x-icon lucide-package-x"><path d="M12 22V12"/><path d="m16.5 14.5 5 5"/><path d="m16.5 19.5 5-5"/><path d="M21 10.5V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.729l7 4a2 2 0 0 0 2 .001l.13-.074"/><path d="M3.29 7 12 12l8.71-5"/><path d="m7.5 4.27 8.997 5.148"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Bounce Servers') }}</p>
                                    </a>
                                    @endcustomercan
 
                                    @customercan('servers.permissions.can_access_reply_servers')
                                    <a href="{{ route('customer.reply-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.reply-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-reply-icon lucide-message-square-reply"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/><path d="m10 8-3 3 3 3"/><path d="M17 14v-1a2 2 0 0 0-2-2H7"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Reply Servers') }}</p>
                                    </a>
                                    @endcustomercan
    
                                    <a href="{{ route('customer.bounced-emails.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.bounced-emails.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-warning-icon lucide-mail-warning"><path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M20 14v4"/><path d="M20 22v.01"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Bounced Emails') }}</p>
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-2">{{ __('Domains') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-auto mx-auto m-0" :class="sidebarCollapsed ? 'w-auto' : 'w-full'">
                                    <a href="{{ route('customer.tracking-domains.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.tracking-domains.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scan-icon lucide-scan"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Tracking Domains') }}</p>
                                    </a>
                                    <a href="{{ route('customer.sending-domains.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg p-2 {{ request()->routeIs('customer.sending-domains.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe-icon lucide-globe"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Sending Domains') }}</p>
                                    </a>
                                </div>
                            </div>
                        </nav>
                    </div>

                    <div class="w-full mt-auto shrink-0">
                        @php
                            $sidebarCustomer = auth()->guard('customer')->user();
                            $sidebarInitials = strtoupper(Str::substr($sidebarCustomer->first_name, 0, 1) . Str::substr($sidebarCustomer->last_name, 0, 1));
                            $sidebarAvatarUrl = $sidebarCustomer && $sidebarCustomer->avatar_path
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($sidebarCustomer->avatar_path, '/'))
                                : null;
                            $sidebarQuotaUsage = app(\App\Services\Billing\UsageService::class)->getUsage($sidebarCustomer);
                            $sidebarQuotaUsed = (int) ($sidebarQuotaUsage['emails_sent_this_month'] ?? 0);
                            $sidebarQuotaLimit = $sidebarCustomer->groupLimit('sending_quota.monthly_quota')
                                ?? ((int) ($sidebarCustomer->quota ?? 0) ?: null);
                            $sidebarQuotaPercent = $sidebarQuotaLimit && $sidebarQuotaLimit > 0
                                ? min(100, max(0, (int) round(($sidebarQuotaUsed / $sidebarQuotaLimit) * 100)))
                                : 0;
                            $sidebarSubscription = $sidebarCustomer->subscriptions()
                                ->whereIn('status', ['active', 'trialing', 'past_due', 'pending'])
                                ->latest()
                                ->first();
                            $sidebarPlanName = trim((string) ($sidebarSubscription?->plan_name ?? ''));
                            if ($sidebarPlanName === '') {
                                $sidebarPlanName = 'Free plan';
                            }
                            $sidebarCurrentPlanPrice = (float) ($sidebarSubscription?->price ?? 0);
                            $sidebarUpgradePlan = \App\Models\Plan::query()
                                ->where('is_active', true)
                                ->where('is_public', true)
                                ->where('price', '>', $sidebarCurrentPlanPrice)
                                ->orderBy('price')
                                ->first();
                            $activeLocales = collect(app(\App\Translation\LocaleJsonService::class)->listLocales());
                            $currentLocale = (string) app()->getLocale();
                            $currentLocale = trim($currentLocale) !== '' ? $currentLocale : 'en';
                            $localeLabelMap = [
                                'ar' => 'Arabic',
                                'de' => 'German',
                                'en' => 'English',
                                'es' => 'Spanish',
                                'fr' => 'French',
                                'hi' => 'Hindi',
                                'id' => 'Indonesian',
                                'it' => 'Italian',
                                'ja' => 'Japanese',
                                'ko' => 'Korean',
                                'pt' => 'Portuguese',
                                'pt-br' => 'Portuguese (Brazil)',
                                'ru' => 'Russian',
                                'tr' => 'Turkish',
                                'ur' => 'Urdu',
                                'zh' => 'Chinese',
                                'zh-cn' => 'Chinese (Simplified)',
                                'zh-tw' => 'Chinese (Traditional)',
                            ];
                        @endphp
                        <div class="p-2 bg-gray-100 dark:bg-blue-900 rounded-lg" x-show="!sidebarCollapsed">
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        
                                        <div class="min-w-0">
                                            <p class="truncate text-[13px] font-semibold text-[#101828] dark:text-admin-text-primary">{{ $sidebarPlanName }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('customer.billing.index') }}" class="shrink-0 text-xs font-semibold text-[#1E5FEA] transition-colors hover:text-[#184fc3]">
                                        {{ __('Upgrade') }}
                                    </a>
                                </div>
                                <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-[#12B76A]" style="width: {{ $sidebarQuotaPercent > 0 ? $sidebarQuotaPercent : 8 }}%; background-image: repeating-linear-gradient(135deg, rgba(255,255,255,0) 0, rgba(255,255,255,0) 7px, rgba(255,255,255,0.22) 7px, rgba(255,255,255,0.22) 12px);"></div>
                                </div>
                                <div class="mt-1 text-xs text-[#667085] dark:text-admin-text-secondary">
                                    @if($sidebarQuotaLimit)
                                        <span class="font-semibold text-[#344054] dark:text-admin-text-primary">{{ number_format($sidebarQuotaUsed) }}</span>
                                        {{ __('of') }}
                                        <span class="font-semibold text-[#344054] dark:text-admin-text-primary">{{ number_format($sidebarQuotaLimit) }}</span>
                                        {{ __('used this month') }}
                                    @else
                                        <span class="font-semibold text-[#344054] dark:text-admin-text-primary">{{ number_format($sidebarQuotaUsed) }}</span>
                                        {{ __('used this month') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="relative" x-data="{ accountMenuOpen: false }" @click.outside="accountMenuOpen = false">
                            <button
                                type="button"
                                @click="accountMenuOpen = !accountMenuOpen"
                                class="w-full flex items-center gap-3 rounded-2xl p-2 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors {{ app('locale.direction')->dir() === 'rtl' ? 'text-right' : 'text-left' }}"
                                :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            >
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#2f2f2f] text-xs font-semibold text-white">
                                    @if($sidebarAvatarUrl)
                                        <img src="{{ $sidebarAvatarUrl }}" alt="{{ $sidebarCustomer->full_name }}" class="h-full w-full object-cover">
                                    @else
                                        {{ $sidebarInitials }}
                                    @endif
                                </div>
                                <div class="sidebar-user-info min-w-0 flex-1" x-show="!sidebarCollapsed">
                                    <p class="text-sm font-semibold text-admin-text-primary truncate">
                                        {{ $sidebarCustomer->full_name }}
                                    </p>
                                    <p class="text-xs text-admin-text-secondary truncate">
                                        {{ $sidebarPlanName }}
                                    </p>
                                </div>
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-admin-text-secondary dark:border-admin-border" x-show="!sidebarCollapsed">
                                    <svg class="w-4 h-4 transition-transform" :class="accountMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            <div
                                x-cloak
                                x-show="accountMenuOpen"
                                x-transition
                                class="absolute z-50 rounded border border-gray-200 bg-white p-0 shadow-2xl dark:border-admin-border dark:bg-admin-sidebar"
                                :class="sidebarCollapsed
                                    ? '{{ app('locale.direction')->dir() === 'rtl' ? 'right-0 left-auto bottom-full mb-3 w-80 lg:right-full lg:bottom-0 lg:mb-0 lg:mr-3' : 'left-0 right-auto bottom-full mb-3 w-80 lg:left-full lg:bottom-0 lg:mb-0 lg:ml-3' }}'
                                    : '{{ app('locale.direction')->dir() === 'rtl' ? 'right-0 left-0 bottom-full mb-3 w-full' : 'left-0 right-0 bottom-full mb-3 w-full' }}'"
                            >
                                <div class="flex items-center gap-3 px-3 py-2 border-b border-gray-200 dark:border-admin-border">
                                    <div class="min-w-0 flex-1 text-sm text-admin-text-secondary truncate">
                                        {{ $sidebarCustomer->email }}
                                    </div>
                                    @if($sidebarUpgradePlan)
                                        <a
                                            href="{{ route('customer.billing.index') }}"
                                            class="shrink-0 inline-flex items-center rounded-full bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 px-3 py-1 text-xs font-semibold text-amber-950 shadow-sm transition-transform duration-200 hover:scale-105 animate-pulse"
                                        >
                                            {{ __('Upgrade') }}
                                        </a>
                                    @endif
                                </div>
  
                                <div class="py-2 space-y-1">
                                    <a href="{{ route('customer.settings.index') }}" class="flex items-center gap-3 p-2 text-sm {{ request()->routeIs('customer.settings.*') ? 'bg-[rgba(30,95,234,0.08)] text-[#1E5FEA] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M14 17H5"/><path d="M19 7h-9"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
                                        <span>{{ __('Settings') }}</span>
                                    </a>

                                    @if($activeLocales->count() > 1)
                                        @php
                                            $currentLocaleMeta = $activeLocales->firstWhere('code', $currentLocale);
                                            $currentLocaleName = is_string($currentLocaleMeta->name ?? null) ? trim((string) $currentLocaleMeta->name) : '';
                                            if ($currentLocaleName === '' || Str::lower($currentLocaleName) === Str::lower($currentLocale)) {
                                                $currentLocaleName = $localeLabelMap[Str::lower($currentLocale)] ?? Str::upper($currentLocale);
                                            }
                                        @endphp
                                        <div class="relative" x-data="{ languageFlyoutOpen: false }" @mouseenter="languageFlyoutOpen = true" @mouseleave="languageFlyoutOpen = false">
                                            <button
                                                type="button"
                                                @click="languageFlyoutOpen = !languageFlyoutOpen"
                                                class="flex w-full items-center gap-3 p-2 text-sm text-[#1b1b20] dark:text-admin-text-primary hover:bg-gray-50 dark:hover:bg-white/5 {{ app('locale.direction')->dir() === 'rtl' ? 'text-right' : 'text-left' }}"
                                                :class="languageFlyoutOpen ? 'bg-gray-100 dark:bg-white/10' : ''"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></svg>
                                                <span class="flex-1 {{ app('locale.direction')->dir() === 'rtl' ? 'text-right' : 'text-left' }}">{{ __('Language') }}</span>
                                                <span class="text-xs text-admin-text-secondary truncate max-w-[110px]">{{ $currentLocaleName }}</span>
                                                <svg class="h-4 w-4 shrink-0 text-admin-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app('locale.direction')->dir() === 'rtl' ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}" />
                                                </svg>
                                            </button>

                                            <div
                                                x-cloak
                                                x-show="languageFlyoutOpen"
                                                x-transition
                                                class="absolute top-0 z-[60] w-72 rounded border border-gray-200 bg-white p-0 shadow-2xl dark:border-admin-border dark:bg-admin-sidebar {{ app('locale.direction')->dir() === 'rtl' ? 'right-full mr-2' : 'left-full ml-2' }}"
                                            >
                                                <div class="max-h-80 overflow-y-auto space-y-1">
                                                    @foreach($activeLocales as $loc)
                                                        @php
                                                            $locCode = is_string($loc->code ?? null) ? trim((string) $loc->code) : '';
                                                            $locName = is_string($loc->name ?? null) ? trim((string) $loc->name) : '';
                                                            if ($locName === '' || Str::lower($locName) === Str::lower($locCode)) {
                                                                $locName = $localeLabelMap[Str::lower($locCode)] ?? Str::upper($locCode);
                                                            }
                                                        @endphp
                                                        <form method="POST" action="{{ route('customer.language.update') }}" data-turbo="false">
                                                            @csrf
                                                            <button
                                                                type="submit"
                                                                name="locale"
                                                                value="{{ $locCode }}"
                                                                class="flex w-full items-center gap-3 p-2 text-sm {{ app('locale.direction')->dir() === 'rtl' ? 'text-right' : 'text-left' }} {{ $currentLocale === $locCode ? 'bg-gray-100 text-[#1b1b20] dark:bg-white/10 dark:text-admin-text-primary' : 'text-[#1b1b20] hover:bg-gray-50 dark:text-admin-text-primary dark:hover:bg-white/5' }}"
                                                            >
                                                                <span class="flex-1 min-w-0">
                                                                    <span class="block truncate">{{ $locName }}</span>
                                                                    <span class="block text-xs text-admin-text-secondary uppercase mt-0.5">{{ $locCode }}</span>
                                                                </span>
                                                                @if($currentLocale === $locCode)
                                                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                @endif
                                                            </button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @customercan('support.permissions.can_access_support')
                                        <a href="{{ route('customer.support-tickets.index') }}" class="flex items-center gap-3 p-2 text-sm {{ request()->routeIs('customer.support-tickets.*') ? 'bg-[rgba(30,95,234,0.08)] text-[#1E5FEA] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm0 0a9 9 0 1 1 18 0m0 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M21 16v2a4 4 0 0 1-4 4h-5"/></svg>
                                            <span>{{ __('Support') }}</span>
                                        </a>
                                    @endcustomercan

                                    <div class="my-2 border-t border-gray-200 dark:border-admin-border"></div>

                                    <a href="{{ route('customer.billing.index') }}" class="flex items-center gap-3 p-2 text-sm {{ request()->routeIs('customer.billing.*') ? 'bg-[rgba(30,95,234,0.08)] text-[#1E5FEA] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
                                        <span>{{ __('Billing') }}</span>
                                    </a>

                                    <a href="{{ route('customer.affiliate.index') }}" class="flex items-center gap-3 p-2 text-sm {{ request()->routeIs('customer.affiliate.*') ? 'bg-[rgba(30,95,234,0.08)] text-[#1E5FEA] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5">
                                        <svg class="block h-[18px] w-[18px] shrink-0" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                            <path d="M3.75 15.75V3.75C3.75 2.92157 4.42157 2.25 5.25 2.25H12.75C13.5784 2.25 14.25 2.92157 14.25 3.75V15.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            <path d="M6 6.75H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M6 9.75H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M6 12.75H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        </svg>
                                        <span>{{ __('Affiliate') }}</span>
                                    </a>

                                    <a href="{{ route('customer.usage.index') }}" class="flex items-center gap-3 p-2 text-sm {{ request()->routeIs('customer.usage.*') ? 'bg-[rgba(30,95,234,0.08)] text-[#1E5FEA] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5">
                                        <svg class="block h-[18px] w-[18px] shrink-0" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                            <path d="M7.3125 2.25H4.3125C3.78916 2.25 3.5275 2.25 3.31457 2.31459C2.83517 2.46001 2.46001 2.83517 2.31459 3.31457C2.25 3.5275 2.25 3.78917 2.25 4.3125C2.25 4.83584 2.25 5.0975 2.31459 5.31043C2.46001 5.78983 2.83517 6.16499 3.31457 6.31041C3.5275 6.375 3.78916 6.375 4.3125 6.375H7.3125C7.83585 6.375 8.09752 6.375 8.31045 6.31041C8.78985 6.16499 9.165 5.78983 9.31042 5.31043C9.375 5.0975 9.375 4.83584 9.375 4.3125C9.375 3.78917 9.375 3.5275 9.31042 3.31457C9.165 2.83517 8.78985 2.46001 8.31045 2.31459C8.09752 2.25 7.83585 2.25 7.3125 2.25Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            <path d="M15.75 7.3125V4.3125C15.75 3.78916 15.75 3.5275 15.6854 3.31457C15.54 2.83517 15.1649 2.46001 14.6855 2.31459C14.4725 2.25 14.2109 2.25 13.6875 2.25C13.1642 2.25 12.9025 2.25 12.6896 2.31459C12.2102 2.46001 11.835 2.83517 11.6896 3.31457C11.625 3.5275 11.625 3.78916 11.625 4.3125V7.3125C11.625 7.83585 11.625 8.09752 11.6896 8.31045C11.835 8.78985 12.2102 9.165 12.6896 9.31042C12.9025 9.375 13.1642 9.375 13.6875 9.375C14.2109 9.375 14.4725 9.375 14.6855 9.31042C15.1649 9.165 15.54 8.78985 15.6854 8.31045C15.75 8.09752 15.75 7.83585 15.75 7.3125Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            <path d="M12.6896 15.6854C12.9025 15.75 13.1642 15.75 13.6875 15.75C14.2109 15.75 14.4725 15.75 14.6855 15.6854C15.1649 15.54 15.54 15.1649 15.6854 14.6855C15.75 14.4725 15.75 14.2109 15.75 13.6875C15.75 13.1642 15.75 12.9025 15.6854 12.6896C15.54 12.2102 15.1649 11.835 14.6855 11.6896C14.4725 11.625 14.2109 11.625 13.6875 11.625C13.1642 11.625 12.9025 11.625 12.6896 11.6896C12.2102 11.835 11.835 12.2102 11.6896 12.6896C11.625 12.9025 11.625 13.1642 11.625 13.6875C11.625 14.2109 11.625 14.4725 11.6896 14.6855C11.835 15.1649 12.2102 15.54 12.6896 15.6854Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            <path d="M6.375 8.625H5.25C3.83578 8.625 3.12868 8.625 2.68934 9.06435C2.25 9.5037 2.25 10.2108 2.25 11.625V12.75C2.25 14.1642 2.25 14.8713 2.68934 15.3106C3.12868 15.75 3.83578 15.75 5.25 15.75H6.375C7.7892 15.75 8.4963 15.75 8.93565 15.3106C9.375 14.8713 9.375 14.1642 9.375 12.75V11.625C9.375 10.2108 9.375 9.5037 8.93565 9.06435C8.4963 8.625 7.7892 8.625 6.375 8.625Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                        </svg>
                                        <span>{{ __('Usage') }}</span>
                                    </a>

                                    <button
                                        type="button"
                                        @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                                        class="flex w-full items-center gap-3 p-2 text-sm text-[#1b1b20] hover:bg-gray-50 dark:text-admin-text-primary dark:hover:bg-white/5"
                                    >
                                        <svg x-cloak x-show="!darkMode" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <svg x-cloak x-show="darkMode" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                        </svg>
                                        <span>{{ __('Theme') }}</span>
                                        <span class="ml-auto text-xs text-admin-text-secondary" x-text="darkMode ? 'Dark' : 'Light'"></span>
                                    </button>
                                </div>
  
                                <div class="border-t border-gray-200 py-1 dark:border-admin-border">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-3 p-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                                            <svg class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            <span>{{ __('Log out') }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            @php
                $pageHeaderTitle = trim($__env->yieldContent('page-title', $pageTitle));
                $hasPageActions = trim($__env->yieldContent('page-actions')) !== '';
                $hasCustomBreadcrumbs = trim($__env->yieldContent('breadcrumbs')) !== '';
                $hasPageTitleMeta = trim($__env->yieldContent('page-title-meta')) !== '';
                $hasPageTitleDetails = trim($__env->yieldContent('page-title-details')) !== '';
                $headerSearchQuery = trim((string) request()->query('q', ''));
                $hideTopHeader = trim($__env->yieldContent('hide-top-header')) !== ''
                    || request()->routeIs('customer.campaigns.create')
                    || request()->routeIs('customer.campaigns.edit');
            @endphp

            <div class="flex-1 flex flex-col overflow-hidden min-h-0" x-data="{ 
                    notificationsOpen: false
                }" @click.outside="notificationsOpen = false">
                <header class="sticky top-0 z-30 shrink-0 h-12 bg-white dark:bg-admin-main border-b border-admin-border flex items-center justify-between px-4 sm:px-6 gap-4" @if($hideTopHeader) style="display:none" @endif>
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <button
                            type="button"
                            class="lg:hidden p-2 rounded-md text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            @click.stop="sidebarOpen = true"
                            aria-label="Open sidebar"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <form action="{{ route('customer.search.index') }}" method="GET" class="hidden lg:block flex-1 max-w-xl">
                            <label for="customer-header-search" class="sr-only">{{ __('Search') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center text-admin-text-secondary">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                    </svg>
                                </span>
                                <input
                                    id="customer-header-search"
                                    type="search"
                                    name="q"
                                    value="{{ $headerSearchQuery }}"
                                    placeholder="{{ __('Search campaigns, lists, subscribers...') }}"
                                    class="block h-10 w-full border-0 bg-transparent pl-6 pr-0 py-0 text-sm text-admin-text-primary placeholder:text-admin-text-secondary outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0 dark:text-admin-text-primary dark:placeholder:text-admin-text-secondary"
                                >
                            </div>
                        </form>
                    </div>

                    <div class="flex items-center gap-4 shrink-0">
                        @php
                            $customer = auth('customer')->user();
                        @endphp
                        <div class="relative">
                            <button
                                type="button"
                                @click.stop="notificationsOpen = !notificationsOpen"
                                class="relative inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none dark:text-admin-text-secondary dark:hover:bg-white/5 dark:hover:text-admin-text-primary"
                                data-notifications-root
                                data-notifications-feed-url="{{ route('customer.notifications.feed') }}"
                                data-notifications-mark-all-read-url="{{ route('customer.notifications.mark-all-read') }}"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @php
                                    $unreadCount = auth('customer')->check()
                                        ? auth('customer')->user()->unreadNotifications()->count()
                                        : 0;
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500 text-white" data-notifications-badge>
                                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                    </span>
                                @endif
                            </button>

                            <div
                                x-cloak
                                x-show="notificationsOpen"
                                x-transition
                                class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-admin-sidebar ring-1 ring-admin-border z-20"
                            >
                                <div class="px-4 py-3 border-b border-admin-border flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-admin-text-primary">
                                        Notifications
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="text-xs text-admin-text-secondary hover:text-admin-text-primary" data-notifications-mark-all-read>
                                            Mark all read
                                        </button>
                                        <p class="text-xs text-admin-text-secondary" data-notifications-unread-label>
                                        @if($unreadCount > 0)
                                            {{ $unreadCount }} unread
                                        @else
                                            Up to date
                                        @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="max-h-80 overflow-y-auto divide-y divide-admin-border" data-notifications-list>
                                    @php
                                        $notifications = auth('customer')->check()
                                            ? auth('customer')->user()->notifications()->latest()->limit(10)->get()
                                            : collect();
                                    @endphp

                                    @forelse($notifications as $notification)
                                        <div class="px-4 py-3 text-sm {{ $notification->read_at ? 'bg-admin-sidebar' : 'bg-white/5' }}">
                                            <p class="text-admin-text-primary font-medium">
                                                {{ $notification->data['title'] ?? 'Notification' }}
                                            </p>
                                            @if(!empty($notification->data['message']))
                                                <p class="mt-1 text-xs text-admin-text-secondary">
                                                    {{ $notification->data['message'] }}
                                                </p>
                                            @endif
                                            <p class="mt-1 text-[11px] text-admin-text-secondary/80">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    @empty
                                        <div class="px-4 py-6 text-center text-sm text-admin-text-secondary">
                                            No notifications yet.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            @php
                                $customer = auth('customer')->user();
                                $avatarUrl = $customer && $customer->avatar_path
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($customer->avatar_path, '/'))
                                    : null;
                            @endphp
                            <a
                                href="{{ route('customer.profile.edit') }}"
                                class="flex items-center text-admin-text-secondary hover:text-admin-text-primary focus:outline-none"
                                aria-label="{{ __('Profile') }}"
                            >
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center overflow-hidden">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $customer->full_name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-semibold text-admin-text-primary">
                                            {{ strtoupper(Str::substr($customer->first_name, 0, 1) . Str::substr($customer->last_name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                </header>

             <!-- Page Content -->
             <main class="{{ $isCampaign ? 'lg:flex-none' : 'flex-1' }} flex flex-col min-h-0 overflow-y-auto {{ $disableMainScroll ? 'lg:overflow-y-hidden' : '' }} overflow-x-auto lg:overflow-x-visible {{ trim($__env->yieldContent('content-padding-classes')) !== '' ? trim($__env->yieldContent('content-padding-classes')) : 'p-4 sm:p-6' }}" {!! trim($__env->yieldContent('page-container-attributes')) !!} @if($isCampaign) style="width: calc(100vw - 450px); max-width: calc(100vw - 450px);" @endif>
                 @php
                     $toastPosition = \App\Models\Setting::get('toast_position', 'top_right');
                    $allowedToastPositions = ['top_left', 'top_center', 'top_right', 'bottom_left', 'bottom_center', 'bottom_right'];
                    $toastPosition = is_string($toastPosition) ? trim($toastPosition) : 'top_right';
                    if (!in_array($toastPosition, $allowedToastPositions, true)) {
                        $toastPosition = 'top_right';
                    }

                    $toastRootClass = match ($toastPosition) {
                        'top_left' => 'fixed top-4 left-4 items-start',
                        'top_center' => 'fixed top-4 left-1/2 -translate-x-1/2 items-center',
                        'top_right' => 'fixed top-4 right-4 items-end',
                        'bottom_left' => 'fixed bottom-4 left-4 items-start',
                        'bottom_center' => 'fixed bottom-4 left-1/2 -translate-x-1/2 items-center',
                        'bottom_right' => 'fixed bottom-4 right-4 items-end',
                        default => 'fixed top-4 right-4 items-end',
                    };
                    $toastRootClass .= ' z-[9999] flex flex-col gap-3 w-full max-w-sm';

                    $toasts = [];

                    $success = session()->pull('success');
                    if (is_string($success) && trim($success) !== '') {
                        $toasts[] = ['type' => 'success', 'title' => __('You\'re all set'), 'message' => $success];
                    }

                    $info = session()->pull('info');
                    if (is_string($info) && trim($info) !== '') {
                        $toasts[] = ['type' => 'info', 'title' => __('Just so you know'), 'message' => $info];
                    }

                    $warning = session()->pull('warning');
                    if (is_string($warning) && trim($warning) !== '') {
                        $toasts[] = ['type' => 'warning', 'title' => __('Take a quick look'), 'message' => $warning];
                    }

                    $error = session()->pull('error');
                    if (is_string($error) && trim($error) !== '') {
                        $toasts[] = ['type' => 'error', 'title' => __('Something went wrong'), 'message' => $error];
                    }

                    if ($errors->any()) {
                        $toasts[] = ['type' => 'error', 'title' => __('Something went wrong'), 'message' => implode("\n", $errors->all())];
                    }
                @endphp

                <div data-toast-root data-toast-position="{{ $toastPosition }}" class="{{ $toastRootClass }}"></div>
                <script>
                    window.__mailpursePageToasts = @json($toasts);
                </script>

                @if(session()->has('impersonator_admin_id'))
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 shadow-sm dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-semibold">{{ __('Admin impersonation active') }}</div>
                                <div class="text-sm text-amber-800 dark:text-amber-200">{{ __('You are currently browsing as this customer.') }}</div>
                            </div>
                            <form method="POST" action="{{ route('customer.stop-impersonation') }}" class="shrink-0">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:bg-amber-500 dark:hover:bg-amber-400 dark:text-amber-950">
                                    {{ __('Return to Admin') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @php $hasCustomPageHeader = trim($__env->yieldContent('page-header')) !== ''; @endphp
                @if($hasCustomPageHeader)
                    @yield('page-header')
                @elseif($pageHeaderTitle !== '')
                    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            @if($hasCustomBreadcrumbs)
                                @yield('breadcrumbs')
                            @elseif(!request()->routeIs('customer.dashboard'))
                                <nav aria-label="Breadcrumb" class="mb-0">
                                    <ol class="flex flex-wrap items-center gap-1.5 text-[12px] text-admin-text-secondary">
                                        <li>
                                            <a href="{{ route('customer.dashboard') }}" class="font-medium transition hover:text-admin-text-primary">
                                                Dashboard
                                            </a>
                                        </li>
                                        <li aria-hidden="true" class="text-admin-text-secondary/60">/</li>
                                        <li class="font-medium text-admin-text-primary">{{ __($pageHeaderTitle) }}</li>
                                    </ol>
                                </nav>
                            @endif

                            <div class="flex flex-wrap items-center gap-3 min-w-0">
                                <h1 class="text-[22px] font-semibold tracking-tight text-admin-text-primary">{{ __($pageHeaderTitle) }}</h1>
                                @if($hasPageTitleMeta)
                                    @yield('page-title-meta')
                                @endif
                            </div>
                            @if($hasPageTitleDetails)
                                <div class="mt-1">
                                    @yield('page-title-details')
                                </div>
                            @endif
                        </div>

                        @if($hasPageActions)
                            <div class="flex w-full flex-col gap-3 lg:w-auto lg:items-end">
                                @yield('page-actions')
                            </div>
                        @endif
                    </div>
                @endif

                <div {!! trim($__env->yieldContent('content-wrapper-attributes')) !!}>
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        (function () {
            const MASK = '********';

            async function fetchSecretValue(url) {
                if (!url) {
                    return '';
                }

                const res = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) {
                    return '';
                }

                const json = await res.json();
                if (!json || json.success !== true) {
                    return '';
                }

                return typeof json.value === 'string' ? json.value : '';
            }

            function updateEyeIcon(svg, isVisible) {
                if (!svg) {
                    return;
                }

                if (isVisible) {
                    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
                } else {
                    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                }
            }

            document.addEventListener('click', function (e) {
                const btn = e.target && e.target.closest ? e.target.closest('[data-toggle-secret]') : null;
                if (!btn) {
                    return;
                }

                const wrapper = btn.closest('[data-secret-wrapper]');
                if (!wrapper) {
                    return;
                }

                const input = wrapper.querySelector('input');
                if (!input) {
                    return;
                }

                const togglingToText = input.type === 'password';
                const nextType = togglingToText ? 'text' : 'password';
                input.type = nextType;

                const changed = input.dataset.changed === '1';
                const secretUrl = input.getAttribute('data-secret-url') || '';

                if (togglingToText && !changed && input.value === MASK && secretUrl) {
                    fetchSecretValue(secretUrl)
                        .then(function (value) {
                            if (value) {
                                input.value = value;
                                input.dataset.revealed = '1';
                            }
                        })
                        .catch(function () {
                            // ignore
                        });
                }

                if (!togglingToText && input.dataset.revealed === '1' && !changed) {
                    input.value = MASK;
                    delete input.dataset.revealed;
                }

                const svg = btn.querySelector('svg');
                updateEyeIcon(svg, nextType === 'text');
            });

            document.addEventListener('DOMContentLoaded', function () {
                const inputs = document.querySelectorAll('input[data-secret-input]');
                inputs.forEach(function (input) {
                    input.dataset.initialValue = input.value;
                    input.addEventListener('input', function () {
                        input.dataset.changed = '1';
                        delete input.dataset.revealed;
                    });
                });

                const forms = document.querySelectorAll('form');
                forms.forEach(function (form) {
                    form.addEventListener('submit', function () {
                        const formInputs = form.querySelectorAll('input[data-secret-input]');
                        formInputs.forEach(function (input) {
                            const initial = input.dataset.initialValue || '';
                            const changed = input.dataset.changed === '1';
                            const revealed = input.dataset.revealed === '1';
                            if (!changed && ((input.value === MASK && initial === MASK) || revealed)) {
                                input.value = '';
                            }
                        });
                    });
                });
            });
        })();
    </script>

    <div
        data-confirm-modal-root
        x-data="{
            open: false,
            title: 'Are you sure?',
            message: '',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            variant: 'default',
            form: null,
            openFromEvent(e) {
                const d = (e && e.detail) ? e.detail : {};
                this.form = d.form || null;
                this.title = (typeof d.title === 'string' && d.title) ? d.title : 'Are you sure?';
                this.message = (typeof d.message === 'string') ? d.message : '';
                this.confirmText = (typeof d.confirmText === 'string' && d.confirmText) ? d.confirmText : 'Confirm';
                this.cancelText = (typeof d.cancelText === 'string' && d.cancelText) ? d.cancelText : 'Cancel';
                this.variant = (typeof d.variant === 'string' && d.variant) ? d.variant : 'default';
                this.open = true;
                this.$nextTick(() => {
                    try {
                        const btn = this.$refs.confirmBtn;
                        if (btn) btn.focus();
                    } catch (err) {
                        // ignore
                    }
                });
            },
            close() {
                this.open = false;
                this.form = null;
            },
            confirm() {
                const form = this.form;
                if (!form) {
                    this.close();
                    return;
                }
                form.dataset.mpSkipConfirm = '1';
                this.open = false;
                this.form = null;
                this.$nextTick(() => {
                    try {
                        form.submit();
                    } finally {
                        window.setTimeout(() => {
                            try {
                                delete form.dataset.mpSkipConfirm;
                            } catch (e) {
                                // ignore
                            }
                        }, 0);
                    }
                });
            }
        }"
        x-on:open-confirm-modal.window="openFromEvent($event)"
    >
        <div x-cloak x-show="open" class="fixed inset-0 z-[9999]">
            <div class="absolute inset-0 bg-black/40" @click="close()"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div
                    class="w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10"
                    @click.stop
                    @keydown.escape.window="close()"
                >
                    <div class="flex items-start justify-between px-6 pt-6">
                        <div class="pr-8">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="title"></h3>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-gray-100" @click="close()" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 pb-6 pt-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300" x-text="message"></p>

                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600" @click="close()" x-text="cancelText"></button>
                            <button
                                x-ref="confirmBtn"
                                type="button"
                                class="inline-flex justify-center rounded-xl px-5 py-2.5 text-sm font-medium text-white shadow-sm"
                                :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-primary-600 hover:bg-primary-700'"
                                @click="confirm()"
                                x-text="confirmText"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            function initSidebarTooltips() {
                var sidebar = document.querySelector('[data-sidebar="app"]');
                if (!sidebar) return;

                var tip = document.createElement('div');
                tip.id = 'sidebar-collapsed-tooltip';
                document.body.appendChild(tip);

                var arrow = document.createElement('div');
                arrow.id = 'sidebar-collapsed-tooltip-arrow';
                document.body.appendChild(arrow);

                function showTip(el, label) {
                    if (sidebar.getAttribute('data-collapsed') !== 'true') return;
                    var rect = el.getBoundingClientRect();
                    var sw = sidebar.getBoundingClientRect().right;
                    var midY = rect.top + rect.height / 2;
                    tip.textContent = label;
                    tip.style.left = (sw + 10) + 'px';
                    tip.style.top = midY + 'px';
                    tip.style.display = 'block';
                    arrow.style.left = (sw + 4) + 'px';
                    arrow.style.top = midY + 'px';
                    arrow.style.display = 'block';
                }

                function hideTip() {
                    tip.style.display = 'none';
                    arrow.style.display = 'none';
                }

                sidebar.querySelectorAll('nav a, nav button[type="button"]').forEach(function (el) {
                    var p = el.querySelector(':scope > p');
                    if (p && p.textContent.trim()) {
                        var label = p.textContent.trim();
                        el.addEventListener('mouseenter', function () { showTip(el, label); });
                        el.addEventListener('mouseleave', hideTip);
                        el.addEventListener('click', hideTip);
                    }
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebarTooltips);
            } else {
                initSidebarTooltips();
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
