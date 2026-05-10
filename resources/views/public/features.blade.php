@extends('layouts.public')

@section('title', 'Features')
@section('pageId', 'features-page')

@section('content')
@php
    $getText = function (string $key, string $default): string {
        try {
            $val = \App\Models\Setting::get($key, $default);
        } catch (\Throwable $e) {
            $val = $default;
        }
        $val = is_string($val) ? $val : $default;
        $val = trim($val);
        return $val !== '' ? $val : $default;
    };

    $heroTitle    = $getText('features_hero_title', 'Powerful Features for Email Marketing');
    $heroSubtitle = $getText('features_hero_subtitle', 'Everything you need to create, send, and track successful email campaigns.');

    $sections = [];
    for ($i = 1; $i <= 4; $i++) {
        $sections[$i] = [
            'title'       => $getText('features_section_' . $i . '_title', ''),
            'description' => $getText('features_section_' . $i . '_description', ''),
            'dt'  => [1 => $getText('features_section_'.$i.'_dt_1',''), 2 => $getText('features_section_'.$i.'_dt_2',''), 3 => $getText('features_section_'.$i.'_dt_3','')],
            'dd'  => [1 => $getText('features_section_'.$i.'_dd_1',''), 2 => $getText('features_section_'.$i.'_dd_2',''), 3 => $getText('features_section_'.$i.'_dd_3','')],
            'bullets' => [1 => $getText('features_section_'.$i.'_bullet_1',''), 2 => $getText('features_section_'.$i.'_bullet_2',''), 3 => $getText('features_section_'.$i.'_bullet_3','')],
        ];
    }

    $sections[1]['title']       = $sections[1]['title']       ?: 'Email List Management';
    $sections[1]['description'] = $sections[1]['description'] ?: 'Organize and manage your subscribers with powerful list management tools. Keep your lists clean, segmented, and engaged.';
    $sections[1]['dt'][1]       = $sections[1]['dt'][1]       ?: 'Subscriber Management';
    $sections[1]['dd'][1]       = $sections[1]['dd'][1]       ?: 'Import, export, and manage subscribers with ease. Support for custom fields, tags, and segmentation.';
    $sections[1]['dt'][2]       = $sections[1]['dt'][2]       ?: 'Double Opt-in';
    $sections[1]['dd'][2]       = $sections[1]['dd'][2]       ?: 'Ensure list quality with double opt-in confirmation. Automatically verify email addresses and reduce bounces.';
    $sections[1]['dt'][3]       = $sections[1]['dt'][3]       ?: 'List Segmentation';
    $sections[1]['dd'][3]       = $sections[1]['dd'][3]       ?: 'Segment your audience based on behavior, preferences, or custom fields for targeted campaigns.';
    $sections[1]['bullets'][1]  = $sections[1]['bullets'][1]  ?: 'Unlimited lists';
    $sections[1]['bullets'][2]  = $sections[1]['bullets'][2]  ?: 'Custom fields & tags';
    $sections[1]['bullets'][3]  = $sections[1]['bullets'][3]  ?: 'Import/Export CSV';

    $sections[2]['title']       = $sections[2]['title']       ?: 'Email Campaigns';
    $sections[2]['description'] = $sections[2]['description'] ?: 'Create beautiful, responsive email campaigns that engage your audience and drive results.';
    $sections[2]['dt'][1]       = $sections[2]['dt'][1]       ?: 'Drag & Drop Editor';
    $sections[2]['dd'][1]       = $sections[2]['dd'][1]       ?: 'Build professional emails with our intuitive drag-and-drop editor. No coding required.';
    $sections[2]['dt'][2]       = $sections[2]['dt'][2]       ?: 'Responsive Templates';
    $sections[2]['dd'][2]       = $sections[2]['dd'][2]       ?: 'Choose from beautiful, mobile-responsive templates or create your own custom designs.';
    $sections[2]['dt'][3]       = $sections[2]['dt'][3]       ?: 'Scheduling & Automation';
    $sections[2]['dd'][3]       = $sections[2]['dd'][3]       ?: 'Schedule campaigns for the perfect time or set up automated sequences based on triggers.';
    $sections[2]['bullets'][1]  = $sections[2]['bullets'][1]  ?: 'Unlimited campaigns';
    $sections[2]['bullets'][2]  = $sections[2]['bullets'][2]  ?: 'A/B testing';
    $sections[2]['bullets'][3]  = $sections[2]['bullets'][3]  ?: 'Real-time tracking';

    $sections[3]['title']       = $sections[3]['title']       ?: 'Auto Responders';
    $sections[3]['description'] = $sections[3]['description'] ?: 'Automate your email marketing with triggered campaigns that engage subscribers at the right time.';
    $sections[3]['dt'][1]       = $sections[3]['dt'][1]       ?: 'Welcome Series';
    $sections[3]['dd'][1]       = $sections[3]['dd'][1]       ?: 'Automatically send welcome emails to new subscribers with customizable sequences.';
    $sections[3]['dt'][2]       = $sections[3]['dt'][2]       ?: 'Triggered Campaigns';
    $sections[3]['dd'][2]       = $sections[3]['dd'][2]       ?: 'Set up campaigns that trigger based on subscriber actions, dates, or field changes.';
    $sections[3]['dt'][3]       = $sections[3]['dt'][3]       ?: 'Drip Campaigns';
    $sections[3]['dd'][3]       = $sections[3]['dd'][3]       ?: 'Create multi-email sequences that nurture leads and guide them through your funnel.';
    $sections[3]['bullets'][1]  = $sections[3]['bullets'][1]  ?: 'Multiple triggers';
    $sections[3]['bullets'][2]  = $sections[3]['bullets'][2]  ?: 'Delay scheduling';
    $sections[3]['bullets'][3]  = $sections[3]['bullets'][3]  ?: 'Unlimited sequences';

    $sections[4]['title']       = $sections[4]['title']       ?: 'Analytics & Reporting';
    $sections[4]['description'] = $sections[4]['description'] ?: 'Track your campaign performance with detailed analytics and insights.';
    $sections[4]['dt'][1]       = $sections[4]['dt'][1]       ?: 'Real-time Tracking';
    $sections[4]['dd'][1]       = $sections[4]['dd'][1]       ?: 'Monitor opens, clicks, bounces, and unsubscribes in real-time as your campaigns send.';
    $sections[4]['dt'][2]       = $sections[4]['dt'][2]       ?: 'Detailed Reports';
    $sections[4]['dd'][2]       = $sections[4]['dd'][2]       ?: 'Get comprehensive reports on campaign performance, subscriber engagement, and ROI.';
    $sections[4]['dt'][3]       = $sections[4]['dt'][3]       ?: 'Export Data';
    $sections[4]['dd'][3]       = $sections[4]['dd'][3]       ?: 'Export your analytics data to CSV or PDF for further analysis and reporting.';
    $sections[4]['bullets'][1]  = $sections[4]['bullets'][1]  ?: 'Open & click rates';
    $sections[4]['bullets'][2]  = $sections[4]['bullets'][2]  ?: 'Bounce tracking';
    $sections[4]['bullets'][3]  = $sections[4]['bullets'][3]  ?: 'Subscriber insights';
@endphp

<style>
/* ── Features Page ───────────────────────────────────────── */
.fp-hero        { background: #030712; }
.fp-hero-grid   { background-image: linear-gradient(to right,rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(to bottom,rgba(255,255,255,.04) 1px,transparent 1px); background-size: 3.5rem 3.5rem; }
.fp-grid-bg     { background-image: linear-gradient(to right,rgba(148,163,184,.08) 1px,transparent 1px),linear-gradient(to bottom,rgba(148,163,184,.08) 1px,transparent 1px); background-size: 3.5rem 3.5rem; }
.dark .fp-grid-bg { background-image: linear-gradient(to right,rgba(148,163,184,.05) 1px,transparent 1px),linear-gradient(to bottom,rgba(148,163,184,.05) 1px,transparent 1px); }

/* ambient orb keyframes */
@keyframes fpAmb1 { 0%,100%{transform:translate(0,0) scale(1)} 30%{transform:translate(50px,-40px) scale(1.08)} 65%{transform:translate(-30px,25px) scale(0.94)} }
@keyframes fpAmb2 { 0%,100%{transform:translate(0,0) scale(1)} 35%{transform:translate(-50px,30px) scale(0.92)} 70%{transform:translate(35px,-20px) scale(1.06)} }
@keyframes fpAmb3 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(25px,-50px)} }
@keyframes fpAmb4 { 0%,100%{transform:translate(0,0) scale(1)} 40%{transform:translate(-20px,40px) scale(1.1)} 80%{transform:translate(15px,-15px) scale(0.95)} }
/* aurora band */
@keyframes fpAurora { 0%,100%{opacity:.35;transform:scaleX(1)} 50%{opacity:.7;transform:scaleX(1.15)} }
/* scan sweep */
@keyframes fpScan { from{transform:translateY(-100%)} to{transform:translateY(250%)} }
/* floating particles */
@keyframes fpP1 { 0%,100%{transform:translateY(0);opacity:.4} 50%{transform:translateY(-22px);opacity:.8} }
@keyframes fpP2 { 0%,100%{transform:translateY(0);opacity:.3} 50%{transform:translateY(-16px);opacity:.6} }
@keyframes fpP3 { 0%,100%{transform:translateY(0);opacity:.5} 50%{transform:translateY(-28px);opacity:.9} }

/* hero dark pill + text */
.fp-hero .fp-pill     { background: rgba(var(--brand-rgb),.18); color: var(--brand-color); border-color: rgba(var(--brand-rgb),.35); }
.fp-hero-tag          { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); backdrop-filter: blur(10px); color: #d1d5db; }
.fp-pill        { background: rgba(var(--brand-rgb),.1); color: var(--brand-color); border: 1px solid rgba(var(--brand-rgb),.25); }
.dark .fp-pill  { background: rgba(var(--brand-rgb),.15); }
.fp-card        { transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease; }
.fp-card:hover  { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(var(--brand-rgb),.12); border-color: rgba(var(--brand-rgb),.4); }
.fp-icon-wrap   { background: linear-gradient(135deg, rgba(var(--brand-rgb),.15) 0%, rgba(var(--brand-rgb),.05) 100%); }
.fp-check       { color: var(--brand-color); }
.fp-stat-num    { background: linear-gradient(135deg, var(--brand-color), rgba(var(--brand-rgb),.6)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

/* mock visuals */
.fp-mock        { border-radius: 1rem; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,.12); }
.dark .fp-mock  { box-shadow: 0 25px 60px rgba(0,0,0,.4); }
.fp-bar         { border-radius: 6px; animation: fpBarGrow 1.4s cubic-bezier(.22,1,.36,1) both; transform-origin: bottom; }
@keyframes fpBarGrow { from { transform: scaleY(0); opacity: 0; } to { transform: scaleY(1); opacity: 1; } }
.fp-flow-dot    { width: 10px; height: 10px; border-radius: 50%; background: var(--brand-color); flex-shrink: 0; box-shadow: 0 0 0 4px rgba(var(--brand-rgb),.2); }
.fp-flow-line   { width: 2px; flex: 1; background: linear-gradient(to bottom, rgba(var(--brand-rgb),.5), rgba(var(--brand-rgb),.1)); }

/* section label */
.fp-eyebrow     { font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--brand-color); }

/* deep-dive alt bg */
.fp-alt-bg      { background: linear-gradient(to bottom, #f8fafc 0%, #fff 100%); }
.dark .fp-alt-bg { background: linear-gradient(to bottom, #0f172a 0%, #111827 100%); }
</style>

<!-- ════════════════════════════════════════════════════════
     HERO  (dark + ambient)
════════════════════════════════════════════════════════ -->
<section class="fp-hero relative overflow-hidden py-28 lg:py-36">

    <!-- dark grid overlay -->
    <div class="fp-hero-grid absolute inset-0 pointer-events-none"></div>

    <!-- vignette edges -->
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 100% 100% at 50% 50%, transparent 40%, #030712 100%)"></div>

    <!-- ambient orb 1 — brand primary, top-left -->
    <div class="pointer-events-none absolute" style="top:-120px;left:10%;width:580px;height:580px;border-radius:50%;background:rgba(var(--brand-rgb),.22);filter:blur(80px);animation:fpAmb1 9s ease-in-out infinite"></div>

    <!-- ambient orb 2 — violet, bottom-right -->
    <div class="pointer-events-none absolute" style="bottom:-100px;right:8%;width:500px;height:500px;border-radius:50%;background:rgba(139,92,246,.18);filter:blur(90px);animation:fpAmb2 11s ease-in-out infinite"></div>

    <!-- ambient orb 3 — brand secondary, top-right -->
    <div class="pointer-events-none absolute" style="top:5%;right:-80px;width:340px;height:340px;border-radius:50%;background:rgba(var(--brand-rgb),.14);filter:blur(70px);animation:fpAmb3 7s ease-in-out infinite"></div>

    <!-- ambient orb 4 — indigo, bottom-left -->
    <div class="pointer-events-none absolute" style="bottom:10%;left:-60px;width:300px;height:300px;border-radius:50%;background:rgba(99,102,241,.13);filter:blur(65px);animation:fpAmb4 10s ease-in-out infinite"></div>

    <!-- aurora top band -->
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px" style="background:linear-gradient(90deg,transparent 0%,rgba(var(--brand-rgb),.7) 50%,transparent 100%);animation:fpAurora 4s ease-in-out infinite"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-20 blur-xl" style="background:linear-gradient(to bottom,rgba(var(--brand-rgb),.12),transparent);animation:fpAurora 4s ease-in-out infinite"></div>

    <!-- aurora bottom fade -->
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-32" style="background:linear-gradient(to top,#030712,transparent)"></div>

    <!-- scan sweep -->
    <div class="pointer-events-none absolute inset-x-0 h-56 blur-3xl" style="background:linear-gradient(to bottom,transparent,rgba(var(--brand-rgb),.07),transparent);animation:fpScan 10s linear infinite"></div>

    <!-- floating particles -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        @php
            $particles = [
                ['top'=>'18%','left'=>'12%','s'=>'5px','c'=>'var(--brand-color)','a'=>'fpP1','d'=>'3.2s'],
                ['top'=>'35%','left'=>'28%','s'=>'3px','c'=>'rgba(139,92,246,1)','a'=>'fpP2','d'=>'4.5s'],
                ['top'=>'12%','left'=>'55%','s'=>'4px','c'=>'var(--brand-color)','a'=>'fpP3','d'=>'2.8s'],
                ['top'=>'60%','left'=>'70%','s'=>'3px','c'=>'rgba(99,102,241,1)','a'=>'fpP1','d'=>'5.1s'],
                ['top'=>'25%','left'=>'80%','s'=>'5px','c'=>'var(--brand-color)','a'=>'fpP2','d'=>'3.7s'],
                ['top'=>'70%','left'=>'18%','s'=>'4px','c'=>'rgba(139,92,246,1)','a'=>'fpP3','d'=>'4.2s'],
                ['top'=>'48%','left'=>'44%','s'=>'3px','c'=>'var(--brand-color)','a'=>'fpP1','d'=>'6s'],
                ['top'=>'80%','left'=>'62%','s'=>'4px','c'=>'rgba(99,102,241,1)','a'=>'fpP2','d'=>'3.5s'],
            ];
        @endphp
        @foreach($particles as $pt)
        <span class="absolute rounded-full" style="top:{{ $pt['top'] }};left:{{ $pt['left'] }};width:{{ $pt['s'] }};height:{{ $pt['s'] }};background:{{ $pt['c'] }};opacity:.5;animation:{{ $pt['a'] }} {{ $pt['d'] }} ease-in-out infinite"></span>
        @endforeach
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- eyebrow badge -->
        <div class="feat-badge gsap-fade-up inline-flex items-center gap-2 rounded-full fp-pill px-4 py-1.5 text-sm font-medium mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Everything in one platform
        </div>

        <!-- headline -->
        <h1 class="feat-headline gsap-fade-up text-4xl sm:text-5xl lg:text-[3.5rem] font-bold tracking-tight text-white leading-[1.1]">
            {{ $heroTitle }}
        </h1>

        <!-- subtitle -->
        <p class="feat-sub gsap-fade-up mt-6 text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
            {{ $heroSubtitle }}
        </p>

        <!-- quick-feature pills -->
        <div class="feat-pills gsap-fade-up mt-10 flex flex-wrap justify-center gap-3">
            @php
                $quickFeatures = [
                    ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Email Campaigns'],
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'List Management'],
                    ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'label' => 'Auto Responders'],
                    ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Analytics'],
                    ['icon' => 'M5 12h14M12 5l7 7-7 7', 'label' => 'API Access'],
                    ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Deliverability'],
                ];
            @endphp
            @foreach($quickFeatures as $qf)
            <span class="fp-hero-tag inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium">
                <svg class="w-4 h-4 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $qf['icon'] }}"/></svg>
                {{ $qf['label'] }}
            </span>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     STATS STRIP
════════════════════════════════════════════════════════ -->
<section class="fp-stats-section border-y border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="fp-stats-grid grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @php
                $stats = [
                    ['num' => '∞',     'label' => 'Subscribers'],
                    ['num' => '∞',     'label' => 'Campaigns'],
                    ['num' => '10+',   'label' => 'SMTP Providers'],
                    ['num' => '100%',  'label' => 'Self-Hosted'],
                ];
            @endphp
            @foreach($stats as $stat)
            <div class="fp-stat gsap-fade-up">
                <div class="text-4xl font-bold fp-stat-num">{{ $stat['num'] }}</div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     12-FEATURE GRID
════════════════════════════════════════════════════════ -->
<section class="fp-grid-section py-24 bg-gray-50 dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- header -->
        <div class="fp-grid-header text-center max-w-2xl mx-auto mb-16">
            <p class="fp-eyebrow gsap-fade-up mb-3">Full Platform</p>
            <h2 class="fp-grid-title gsap-fade-up text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Built for every stage of email marketing</h2>
            <p class="fp-grid-sub gsap-fade-up mt-4 text-lg text-gray-500 dark:text-gray-400">From list building to deliverability — every tool you need is already included.</p>
        </div>

        @php
            $featureCards = [
                ['color' => 'blue',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'title' => 'List Management',    'desc' => 'Unlimited lists, subscribers, custom fields, tags and CSV import/export.'],
                ['color' => 'violet', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'title' => 'Email Campaigns',    'desc' => 'Drag-and-drop editor, responsive templates, scheduling and A/B testing.'],
                ['color' => 'emerald','icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Auto Responders',   'desc' => 'Welcome sequences, drip campaigns and trigger-based automation flows.'],
                ['color' => 'amber',  'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'title' => 'Analytics & Reports',  'desc' => 'Real-time open/click tracking, bounce monitoring and exportable reports.'],
                ['color' => 'rose',   'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Delivery Servers',   'desc' => 'Connect Amazon SES, Mailgun, SendGrid, Postmark or any SMTP server.'],
                ['color' => 'sky',    'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'title' => 'Email Warmup',        'desc' => 'Gradually build sender reputation with automated IP and domain warmup plans.'],
                ['color' => 'purple', 'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'title' => 'Page Builder',        'desc' => 'Drag-and-drop landing page and subscribe form builder with custom branding.'],
                ['color' => 'teal',   'icon' => 'M5 12h14M12 5l7 7-7 7', 'title' => 'REST API',             'desc' => 'Full REST API with OpenAPI documentation for seamless third-party integrations.'],
                ['color' => 'orange', 'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'title' => 'DKIM / SPF Auth',    'desc' => 'One-click domain authentication setup with guided SPF, DKIM and DMARC config.'],
                ['color' => 'indigo', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'title' => 'Multi-Tenant SaaS',   'desc' => 'Run your own email marketing SaaS with customer plans, billing and white-label.'],
                ['color' => 'pink',   'icon' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2', 'title' => 'Subscriber Forms',    'desc' => 'Embed sign-up forms anywhere with custom fields, redirects and GDPR consent.'],
                ['color' => 'lime',   'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'title' => 'Bounce Handling',     'desc' => 'Automatic hard/soft bounce detection, suppression lists and IMAP/webhook support.'],
            ];
            $colorMap = [
                'blue'   => ['bg'=>'bg-blue-500/10   dark:bg-blue-500/20',   'ic'=>'text-blue-500'],
                'violet' => ['bg'=>'bg-violet-500/10 dark:bg-violet-500/20', 'ic'=>'text-violet-500'],
                'emerald'=> ['bg'=>'bg-emerald-500/10 dark:bg-emerald-500/20','ic'=>'text-emerald-500'],
                'amber'  => ['bg'=>'bg-amber-500/10  dark:bg-amber-500/20',  'ic'=>'text-amber-500'],
                'rose'   => ['bg'=>'bg-rose-500/10   dark:bg-rose-500/20',   'ic'=>'text-rose-500'],
                'sky'    => ['bg'=>'bg-sky-500/10    dark:bg-sky-500/20',    'ic'=>'text-sky-500'],
                'purple' => ['bg'=>'bg-purple-500/10 dark:bg-purple-500/20', 'ic'=>'text-purple-500'],
                'teal'   => ['bg'=>'bg-teal-500/10   dark:bg-teal-500/20',   'ic'=>'text-teal-500'],
                'orange' => ['bg'=>'bg-orange-500/10 dark:bg-orange-500/20', 'ic'=>'text-orange-500'],
                'indigo' => ['bg'=>'bg-indigo-500/10 dark:bg-indigo-500/20', 'ic'=>'text-indigo-500'],
                'pink'   => ['bg'=>'bg-pink-500/10   dark:bg-pink-500/20',   'ic'=>'text-pink-500'],
                'lime'   => ['bg'=>'bg-lime-500/10   dark:bg-lime-500/20',   'ic'=>'text-lime-500'],
            ];
        @endphp

        <div class="fp-cards-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($featureCards as $fc)
            @php $cm = $colorMap[$fc['color']] ?? $colorMap['blue']; @endphp
            <div class="fp-card gsap-fade-up bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 flex flex-col gap-4">
                <div class="fp-icon-wrap w-11 h-11 rounded-xl {{ $cm['bg'] }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $cm['ic'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $fc['icon'] }}"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ $fc['title'] }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $fc['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     DEEP DIVE 1 — LIST MANAGEMENT  (content left, mock right)
════════════════════════════════════════════════════════ -->
<section class="fp-deep1 py-24 lg:py-32 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <!-- content -->
            <div class="fp-d1-content gsap-slide-left">
                <p class="fp-eyebrow mb-3">01 — {{ $sections[1]['title'] }}</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white leading-tight">{{ $sections[1]['title'] }}</h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 leading-relaxed">{{ $sections[1]['description'] }}</p>

                <div class="mt-8 space-y-5">
                    @for($j = 1; $j <= 3; $j++)
                    <div class="flex gap-4">
                        <div class="fp-icon-wrap w-10 h-10 rounded-xl bg-primary-500/10 dark:bg-primary-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $sections[1]['dt'][$j] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $sections[1]['dd'][$j] }}</div>
                        </div>
                    </div>
                    @endfor
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    @for($j = 1; $j <= 3; $j++)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $sections[1]['bullets'][$j] }}
                    </span>
                    @endfor
                </div>
            </div>

            <!-- mock: subscriber list table -->
            <div class="fp-d1-mock gsap-slide-right">
                <div class="fp-mock bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <!-- chrome bar -->
                    <div class="flex items-center gap-1.5 px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        <span class="ml-3 flex-1 h-5 rounded bg-gray-200 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 flex items-center px-2">app / lists / newsletter</span>
                    </div>
                    <!-- table header -->
                    <div class="px-4 py-3 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Newsletter List</span>
                        <span class="rounded-full fp-pill text-xs px-2.5 py-1 font-semibold">12,480 subscribers</span>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                <th class="px-4 py-2 text-left font-medium">Email</th>
                                <th class="px-4 py-2 text-left font-medium">Status</th>
                                <th class="px-4 py-2 text-left font-medium">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @php
                                $mockRows = [
                                    ['email' => 'james@example.com',   'status' => 'Active',       'date' => 'Mar 28',  'dot' => 'bg-emerald-500'],
                                    ['email' => 'sarah@acme.io',       'status' => 'Active',       'date' => 'Mar 25',  'dot' => 'bg-emerald-500'],
                                    ['email' => 'mike@startup.co',     'status' => 'Unsubscribed', 'date' => 'Mar 20',  'dot' => 'bg-gray-400'],
                                    ['email' => 'lisa@company.com',    'status' => 'Active',       'date' => 'Mar 18',  'dot' => 'bg-emerald-500'],
                                    ['email' => 'tom@business.net',    'status' => 'Bounced',      'date' => 'Mar 15',  'dot' => 'bg-red-400'],
                                ];
                            @endphp
                            @foreach($mockRows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-2.5 text-gray-800 dark:text-gray-200 font-medium">{{ $row['email'] }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center gap-1.5 text-xs">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $row['dot'] }}"></span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ $row['status'] }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-500 text-xs">{{ $row['date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- footer bar -->
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs text-gray-500 dark:text-gray-500 bg-gray-50 dark:bg-gray-900">
                        <span>Page 1 of 512</span>
                        <div class="flex gap-1">
                            <span class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700">‹ Prev</span>
                            <span class="px-2 py-1 rounded text-white" style="background:var(--brand-color)">1</span>
                            <span class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700">Next ›</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     DEEP DIVE 2 — CAMPAIGNS  (mock left, content right)
════════════════════════════════════════════════════════ -->
<section class="fp-deep2 fp-alt-bg py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <!-- mock: email editor preview -->
            <div class="fp-d2-mock gsap-slide-left order-2 lg:order-1">
                <div class="fp-mock bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-1.5 px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">Campaign Editor</span>
                        <span class="ml-auto text-xs font-medium text-emerald-600 dark:text-emerald-400">● Live Preview</span>
                    </div>
                    <div class="flex min-h-[320px]">
                        <!-- sidebar blocks -->
                        <div class="w-28 border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 space-y-2 flex-shrink-0">
                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold mb-3">Blocks</p>
                            @php
                                $blocks = ['Text','Image','Button','Divider','Spacer','Columns'];
                            @endphp
                            @foreach($blocks as $bl)
                            <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400 cursor-pointer hover:border-primary-400 transition-colors text-center">{{ $bl }}</div>
                            @endforeach
                        </div>
                        <!-- canvas -->
                        <div class="flex-1 p-4 space-y-3">
                            <!-- header block -->
                            <div class="rounded-lg border-2 border-dashed border-primary-300 dark:border-primary-800 p-3 text-center">
                                <div class="h-3 w-16 rounded mx-auto mb-2" style="background:var(--brand-color); opacity:.8"></div>
                                <div class="h-2 w-32 rounded mx-auto bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <!-- body block -->
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 space-y-1.5">
                                <div class="h-2 w-full rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-2 w-5/6 rounded bg-gray-200 dark:bg-gray-700"></div>
                                <div class="h-2 w-4/6 rounded bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <!-- image block -->
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 h-20 flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <!-- CTA button block -->
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex justify-center">
                                <span class="rounded-full text-white text-xs font-semibold px-5 py-2" style="background:var(--brand-color)">Read More →</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- content -->
            <div class="fp-d2-content gsap-slide-right order-1 lg:order-2">
                <p class="fp-eyebrow mb-3">02 — {{ $sections[2]['title'] }}</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white leading-tight">{{ $sections[2]['title'] }}</h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 leading-relaxed">{{ $sections[2]['description'] }}</p>

                <div class="mt-8 space-y-5">
                    @for($j = 1; $j <= 3; $j++)
                    <div class="flex gap-4">
                        <div class="fp-icon-wrap w-10 h-10 rounded-xl bg-primary-500/10 dark:bg-primary-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $sections[2]['dt'][$j] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $sections[2]['dd'][$j] }}</div>
                        </div>
                    </div>
                    @endfor
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    @for($j = 1; $j <= 3; $j++)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $sections[2]['bullets'][$j] }}
                    </span>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     DEEP DIVE 3 — AUTO RESPONDERS  (content left, mock right)
════════════════════════════════════════════════════════ -->
<section class="fp-deep3 py-24 lg:py-32 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <!-- content -->
            <div class="fp-d3-content gsap-slide-left">
                <p class="fp-eyebrow mb-3">03 — {{ $sections[3]['title'] }}</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white leading-tight">{{ $sections[3]['title'] }}</h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 leading-relaxed">{{ $sections[3]['description'] }}</p>

                <div class="mt-8 space-y-5">
                    @for($j = 1; $j <= 3; $j++)
                    <div class="flex gap-4">
                        <div class="fp-icon-wrap w-10 h-10 rounded-xl bg-primary-500/10 dark:bg-primary-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $sections[3]['dt'][$j] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $sections[3]['dd'][$j] }}</div>
                        </div>
                    </div>
                    @endfor
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    @for($j = 1; $j <= 3; $j++)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $sections[3]['bullets'][$j] }}
                    </span>
                    @endfor
                </div>
            </div>

            <!-- mock: automation flow -->
            <div class="fp-d3-mock gsap-slide-right">
                <div class="fp-mock bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Welcome Sequence</span>
                        <span class="rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs px-2.5 py-1 font-semibold">● Active</span>
                    </div>
                    @php
                        $flowSteps = [
                            ['label' => 'Subscriber joins list',    'sublabel' => 'Trigger',            'delay' => '',          'color' => 'bg-primary-500'],
                            ['label' => 'Send Welcome Email',       'sublabel' => 'Immediately',        'delay' => 'Day 0',     'color' => 'bg-violet-500'],
                            ['label' => 'Wait',                     'sublabel' => '2 days',             'delay' => '',          'color' => 'bg-gray-400'],
                            ['label' => 'Send Getting Started',     'sublabel' => 'Tip email #1',       'delay' => 'Day 2',     'color' => 'bg-violet-500'],
                            ['label' => 'Wait',                     'sublabel' => '3 days',             'delay' => '',          'color' => 'bg-gray-400'],
                            ['label' => 'Send Case Study',          'sublabel' => 'Social proof email', 'delay' => 'Day 5',     'color' => 'bg-violet-500'],
                        ];
                    @endphp
                    <div class="space-y-0">
                        @foreach($flowSteps as $idx => $step)
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="fp-flow-dot {{ $step['color'] }} mt-1"></div>
                                @if(!$loop->last)
                                <div class="fp-flow-line my-1" style="min-height:2.5rem"></div>
                                @endif
                            </div>
                            <div class="pb-4 flex-1 {{ $loop->last ? '' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $step['label'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $step['sublabel'] }}</div>
                                    </div>
                                    @if($step['delay'])
                                    <span class="text-xs rounded-full fp-pill px-2.5 py-1 font-semibold flex-shrink-0">{{ $step['delay'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     DEEP DIVE 4 — ANALYTICS  (mock left, content right)
════════════════════════════════════════════════════════ -->
<section class="fp-deep4 fp-alt-bg py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <!-- mock: analytics dashboard -->
            <div class="fp-d4-mock gsap-slide-left order-2 lg:order-1">
                <div class="fp-mock bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-1.5 px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">Campaign Analytics — Spring Sale 2025</span>
                    </div>
                    <!-- stat tiles -->
                    <div class="grid grid-cols-4 gap-3 p-4 border-b border-gray-100 dark:border-gray-700">
                        @php
                            $tiles = [['v'=>'68.4%','l'=>'Open Rate','up'=>true],['v'=>'12.1%','l'=>'Click Rate','up'=>true],['v'=>'0.8%','l'=>'Bounce','up'=>false],['v'=>'0.3%','l'=>'Unsub','up'=>false]];
                        @endphp
                        @foreach($tiles as $tile)
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-3 text-center">
                            <div class="text-base font-bold {{ $tile['up'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">{{ $tile['v'] }}</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $tile['l'] }}</div>
                        </div>
                        @endforeach
                    </div>
                    <!-- bar chart -->
                    <div class="p-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 font-medium">Opens over time (last 7 days)</p>
                        <div class="flex items-end gap-2 h-28">
                            @php $bars = [55,72,48,90,83,67,95]; @endphp
                            @foreach($bars as $bidx => $bval)
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div class="fp-bar w-full rounded-md" style="height:{{ $bval }}%; background: linear-gradient(to top, var(--brand-color), rgba(var(--brand-rgb),.5)); animation-delay: {{ $bidx * 0.1 }}s;"></div>
                                <span class="text-[9px] text-gray-400">{{ ['M','T','W','T','F','S','S'][$bidx] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- content -->
            <div class="fp-d4-content gsap-slide-right order-1 lg:order-2">
                <p class="fp-eyebrow mb-3">04 — {{ $sections[4]['title'] }}</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white leading-tight">{{ $sections[4]['title'] }}</h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 leading-relaxed">{{ $sections[4]['description'] }}</p>

                <div class="mt-8 space-y-5">
                    @for($j = 1; $j <= 3; $j++)
                    <div class="flex gap-4">
                        <div class="fp-icon-wrap w-10 h-10 rounded-xl bg-primary-500/10 dark:bg-primary-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $sections[4]['dt'][$j] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $sections[4]['dd'][$j] }}</div>
                        </div>
                    </div>
                    @endfor
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    @for($j = 1; $j <= 3; $j++)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 px-3.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 fp-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $sections[4]['bullets'][$j] }}
                    </span>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     INFRASTRUCTURE & DELIVERABILITY SECTION
════════════════════════════════════════════════════════ -->
<section class="fp-infra-section py-24 lg:py-32 bg-gray-950 dark:bg-gray-950 text-white relative overflow-hidden">
    <!-- bg grid -->
    <div class="fp-grid-bg absolute inset-0 pointer-events-none opacity-40"></div>
    <!-- orb -->
    <div class="infra-orb pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full blur-3xl" style="background:rgba(var(--brand-rgb),.08)"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <p class="fp-eyebrow gsap-fade-up mb-3" style="color:rgba(var(--brand-rgb),.9)">Infrastructure</p>
            <h2 class="fp-infra-title gsap-fade-up text-3xl sm:text-4xl font-bold text-white">Enterprise-grade deliverability</h2>
            <p class="fp-infra-sub gsap-fade-up mt-4 text-lg text-gray-400">Send with confidence using multi-server support, IP warmup, authentication and bounce management.</p>
        </div>

        <div class="fp-infra-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $infraItems = [
                    ['icon' => 'M5 12h14M12 5l7 7-7 7', 'title' => 'Multi-Server Routing', 'desc' => 'Connect multiple SMTP providers and route sends by priority, pool, or campaign type.'],
                    ['icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064', 'title' => 'IP & Domain Warmup', 'desc' => 'Gradually ramp sending volume on new IPs and domains to build a strong sender reputation.'],
                    ['icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'title' => 'SPF / DKIM / DMARC', 'desc' => 'Step-by-step domain authentication guide with record verification and health indicators.'],
                    ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'title' => 'Bounce Processing', 'desc' => 'Automatic bounce parsing via IMAP/webhook. Soft bounces retry, hard bounces auto-suppress.'],
                ];
            @endphp
            @foreach($infraItems as $item)
            <div class="fp-infra-card gsap-fade-up group p-6 rounded-2xl border border-gray-800 bg-gray-900/60 backdrop-blur-sm hover:border-primary-700 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-5" style="background:rgba(var(--brand-rgb),.15)">
                    <svg class="w-5 h-5" style="color:var(--brand-color)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                </div>
                <h3 class="font-semibold text-white mb-2">{{ $item['title'] }}</h3>
                <p class="text-sm text-gray-400 leading-relaxed">{{ $item['desc'] }}</p>
            </div>
            @endforeach
        </div>

        <!-- provider logos -->
        <div class="fp-providers-row gsap-fade-up mt-16 flex flex-wrap items-center justify-center gap-x-12 gap-y-6">
            <span class="text-sm text-gray-500 mr-2">Works with:</span>
            @php
                $providers = ['Amazon SES','Mailgun','SendGrid','Postmark','SparkPost','Any SMTP'];
            @endphp
            @foreach($providers as $prov)
            <span class="text-gray-300 font-semibold text-sm">{{ $prov }}</span>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════
     CTA
════════════════════════════════════════════════════════ -->
<section class="fp-cta-section py-24 bg-white dark:bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="fp-cta-badge gsap-fade-up inline-flex items-center gap-2 rounded-full fp-pill px-4 py-1.5 text-sm font-medium mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            Ready to get started?
        </div>
        <h2 class="fp-cta-title gsap-fade-up text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
            Start sending smarter emails today
        </h2>
        <p class="fp-cta-sub gsap-fade-up mt-5 text-xl text-gray-500 dark:text-gray-400">
            Self-hosted, fully featured, and ready in minutes.
        </p>
        <div class="fp-cta-btns gsap-fade-up mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full text-white px-8 py-4 text-base font-semibold shadow-lg hover:shadow-xl transition-all hover:opacity-90" style="background:var(--brand-color)">
                Get Started Free
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('pricing') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-8 py-4 text-base font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                View Pricing
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    var teardown = function () {
        if (window.ScrollTrigger) {
            try { ScrollTrigger.getAll().forEach(function(t){ t.kill(); }); } catch(e){}
        }
    };

    var init = function () {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

        gsap.registerPlugin(ScrollTrigger);

        /* ── Orbs handled by CSS keyframes, skip GSAP ── */

        /* ── Hero ──────────────────────────────────── */
        var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.to('.feat-badge',    { opacity: 1, y: 0, duration: 0.7 })
          .to('.feat-headline', { opacity: 1, y: 0, duration: 0.7 }, '-=0.4')
          .to('.feat-sub',      { opacity: 1, y: 0, duration: 0.6 }, '-=0.4')
          .to('.feat-pills',    { opacity: 1, y: 0, duration: 0.6 }, '-=0.3');

        /* ── Stats strip ───────────────────────────── */
        gsap.to('.fp-stat', {
            scrollTrigger: { trigger: '.fp-stats-section', start: 'top 80%' },
            opacity: 1, y: 0, duration: 0.6, stagger: 0.12
        });

        /* ── Feature cards grid ────────────────────── */
        gsap.to('.fp-grid-header .gsap-fade-up', {
            scrollTrigger: { trigger: '.fp-grid-section', start: 'top 80%' },
            opacity: 1, y: 0, duration: 0.6, stagger: 0.1
        });
        gsap.to('.fp-cards-grid .fp-card', {
            scrollTrigger: { trigger: '.fp-cards-grid', start: 'top 80%' },
            opacity: 1, y: 0, duration: 0.5, stagger: 0.06, ease: 'power2.out'
        });

        /* ── Deep dive sections ────────────────────── */
        ['.fp-d1-content', '.fp-d2-mock', '.fp-d3-content', '.fp-d4-mock'].forEach(function(sel) {
            gsap.to(sel, {
                scrollTrigger: { trigger: sel, start: 'top 80%' },
                opacity: 1, x: 0, duration: 0.8, ease: 'power3.out'
            });
        });
        ['.fp-d1-mock', '.fp-d2-content', '.fp-d3-mock', '.fp-d4-content'].forEach(function(sel) {
            gsap.to(sel, {
                scrollTrigger: { trigger: sel, start: 'top 80%' },
                opacity: 1, x: 0, duration: 0.8, ease: 'power3.out'
            });
        });

        /* ── Infrastructure section ────────────────── */
        gsap.to('.fp-infra-title, .fp-infra-sub', {
            scrollTrigger: { trigger: '.fp-infra-section', start: 'top 80%' },
            opacity: 1, y: 0, duration: 0.6, stagger: 0.15
        });
        gsap.to('.fp-infra-card', {
            scrollTrigger: { trigger: '.fp-infra-grid', start: 'top 80%' },
            opacity: 1, y: 0, duration: 0.55, stagger: 0.1, ease: 'power2.out'
        });
        gsap.to('.fp-providers-row', {
            scrollTrigger: { trigger: '.fp-providers-row', start: 'top 85%' },
            opacity: 1, y: 0, duration: 0.6
        });

        /* ── CTA ───────────────────────────────────── */
        gsap.to('.fp-cta-badge, .fp-cta-title, .fp-cta-sub, .fp-cta-btns', {
            scrollTrigger: { trigger: '.fp-cta-section', start: 'top 80%' },
            opacity: 1, y: 0, duration: 0.6, stagger: 0.15
        });

        try { ScrollTrigger.refresh(true); } catch (e) {}
    };

    if (window.Turbo) {
        document.addEventListener('turbo:before-cache', teardown);
        document.addEventListener('turbo:load', init);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush
