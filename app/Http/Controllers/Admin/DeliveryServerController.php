<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BounceServer;
use App\Models\DeliveryServer;
use App\Models\Setting;
use App\Models\TrackingDomain;
use App\Services\DeliveryServerService;
use App\Services\UpdateServerService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class DeliveryServerController extends Controller
{
    private const EXTENDED_MAILBOX_TYPES = ['gmail', 'outlook'];

    private const EXTENDED_MAILBOX_DEFAULTS = [
        'gmail' => ['host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls'],
        'outlook' => ['host' => 'smtp.office365.com', 'port' => 587, 'encryption' => 'tls'],
    ];

    public function __construct(
        protected DeliveryServerService $deliveryServerService,
        protected UpdateServerService $updateServerService
    ) {}

    protected function isExtendedLicenseAvailable(): bool
    {
        $baseUrl = (string) config('services.update_server.base_url', Setting::get('update_api_base_url'));
        $licenseKey = Setting::get('update_license_key');
        $licenseKey = is_string($licenseKey) ? trim($licenseKey) : '';

        if ($baseUrl === '' || $licenseKey === '' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $licenseKey)) {
            return false;
        }

        $appUrl = (string) config('app.url');
        $parsed = parse_url($appUrl);
        $domain = is_array($parsed) && is_string($parsed['host'] ?? null) && trim((string) $parsed['host']) !== ''
            ? (string) $parsed['host']
            : $appUrl;

        $cacheKey = 'update_server:license_check:' . md5((string) $baseUrl) . ':' . md5((string) $licenseKey) . ':' . md5((string) $domain);
        $licenseCheck = Cache::get($cacheKey);

        if (!is_array($licenseCheck)) {
            $productSecret = (string) config('services.update_server.product_secret', Setting::get('update_product_secret'));
            $productName = (string) config('services.update_server.product_name', Setting::get('update_product_name'));

            if ($productSecret === '' || $productName === '') {
                return false;
            }

            $licenseCheck = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($licenseKey, $domain, $productSecret, $productName) {
                return $this->updateServerService->licenseCheck(
                    (string) $licenseKey,
                    $domain,
                    (string) $productSecret,
                    (string) $productName
                );
            });
        }

        if (!is_array($licenseCheck) || !($licenseCheck['valid'] ?? false)) {
            return false;
        }

        $payload = is_array($licenseCheck['data'] ?? null) ? $licenseCheck['data'] : [];
        $license = [];

        if (is_array($payload['data'] ?? null) && is_array($payload['data']['license'] ?? null)) {
            $license = $payload['data']['license'];
        } elseif (is_array($payload['license'] ?? null)) {
            $license = $payload['license'];
        }

        $typeCandidates = array_filter([
            is_string($license['license_type'] ?? null) ? $license['license_type'] : null,
            is_string($payload['license_type'] ?? null) ? $payload['license_type'] : null,
            is_string($license['type'] ?? null) ? $license['type'] : null,
            is_string($payload['type'] ?? null) ? $payload['type'] : null,
            is_string($license['name'] ?? null) ? $license['name'] : null,
            is_string($payload['name'] ?? null) ? $payload['name'] : null,
        ]);

        foreach ($typeCandidates as $candidate) {
            if (str_contains(strtolower(trim((string) $candidate)), 'extended')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display a listing of delivery servers.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type', 'status']);
        $filters['customer_id'] = null;
        $deliveryServers = $this->deliveryServerService->getPaginated($filters);
        $hasExtendedLicense = $this->isExtendedLicenseAvailable();

        $systemSmtpServer = null;
        if ($this->deliveryServerService->hasSystemSmtpConfigured()) {
            $systemSmtpServer = $this->deliveryServerService->getOrCreateSystemSmtpDeliveryServer();
        }

        return view('admin.delivery-servers.index', compact('deliveryServers', 'filters', 'systemSmtpServer', 'hasExtendedLicense'));
    }

    protected function authorizeSystemServer(DeliveryServer $deliveryServer): DeliveryServer
    {
        if ($deliveryServer->customer_id !== null) {
            abort(404);
        }

        return $deliveryServer;
    }

    public function revealSecret(Request $request, DeliveryServer $delivery_server)
    {
        $deliveryServer = $this->authorizeSystemServer($delivery_server);

        $field = (string) $request->query('field', '');
        $allowed = [
            'secret',
            'api_key',
            'token',
            'key',
            'send_mail_token',
        ];

        if (!in_array($field, $allowed, true)) {
            abort(404);
        }

        $value = data_get($deliveryServer->settings ?? [], $field, '');
        $value = is_string($value) ? trim($value) : '';

        return response()->json([
            'success' => true,
            'value' => $value,
        ]);
    }

    /**
     * Show the form for creating a new delivery server.
     */
    public function create(Request $request)
    {
        $flow = $request->query('flow');
        $flow = in_array($flow, ['smtp', 'api'], true) ? $flow : null;

        $trackingDomains = TrackingDomain::where('status', 'verified')->get();
        $bounceServers = BounceServer::where('active', true)->orderBy('name')->get();

        return view('admin.delivery-servers.create', compact('trackingDomains', 'bounceServers', 'flow'));
    }

    protected function normalizeExtendedMailboxInput(array &$validated, Request $request): void
    {
        $type = (string) ($validated['type'] ?? '');
        if (!in_array($type, self::EXTENDED_MAILBOX_TYPES, true)) {
            return;
        }

        $authMethod = (string) $request->input('settings.auth_method', 'app_password');
        if ($authMethod !== 'app_password') {
            throw ValidationException::withMessages([
                'settings.auth_method' => 'Only App Password is supported for Gmail and Outlook in this release.',
            ]);
        }

        $defaults = self::EXTENDED_MAILBOX_DEFAULTS[$type];
        if (!isset($validated['hostname']) || !is_string($validated['hostname']) || trim($validated['hostname']) === '') {
            $validated['hostname'] = $defaults['host'];
        }
        if (!isset($validated['port']) || !is_numeric($validated['port'])) {
            $validated['port'] = $defaults['port'];
        }
        if (!isset($validated['encryption']) || !is_string($validated['encryption']) || trim($validated['encryption']) === '') {
            $validated['encryption'] = $defaults['encryption'];
        }
        if (!isset($validated['from_email']) || !is_string($validated['from_email']) || trim($validated['from_email']) === '') {
            $validated['from_email'] = (string) ($validated['username'] ?? '');
        }
    }

    /**
     * Store a newly created delivery server.
     */
    public function store(Request $request)
    {
        $flow = $request->input('flow');
        $flow = in_array($flow, ['smtp', 'api'], true) ? $flow : null;
        $isApiFlow = $flow === 'api';

        $rules = [
            'flow' => ['nullable', 'in:smtp,api'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:smtp,sendmail,gmail,outlook,amazon-ses,mailgun,sendgrid,postmark,sparkpost,zeptomail,zeptomail-api'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'hostname' => ['required_if:type,zeptomail', 'nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['required_if:type,zeptomail,gmail,outlook', 'nullable', 'string', 'max:255'],
            'password' => ['required_if:type,zeptomail,gmail,outlook', 'nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:ssl,tls,none'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1'],
            'max_connection_messages' => ['nullable', 'integer', 'min:1'],
            'second_quota' => ['nullable', 'integer', 'min:0'],
            'minute_quota' => ['nullable', 'integer', 'min:0'],
            'hourly_quota' => ['nullable', 'integer', 'min:0'],
            'daily_quota' => ['nullable', 'integer', 'min:0'],
            'monthly_quota' => ['nullable', 'integer', 'min:0'],
            'pause_after_send' => ['nullable', 'integer', 'min:0'],
            'locked' => ['nullable', 'boolean'],
            'use_for' => ['nullable', 'boolean'],
            'use_for_email_to_list' => ['nullable', 'boolean'],
            'use_for_transactional' => ['nullable', 'boolean'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'bounce_server_id' => ['nullable', 'exists:bounce_servers,id'],
            'notes' => ['nullable', 'string'],
            // Mailgun-specific settings
            'settings.domain' => ['nullable', 'string', 'max:255'],
            'settings.secret' => ['nullable', 'string', 'max:255'],
            // Other provider API settings
            'settings.api_key' => ['nullable', 'string', 'max:255'],
            'settings.token' => ['nullable', 'string', 'max:255'],
            'settings.key' => ['nullable', 'string', 'max:255'],
            'settings.region' => ['nullable', 'string', 'max:255'],
            'settings.send_mail_token' => ['required_if:type,zeptomail-api', 'nullable', 'string', 'max:255'],
            'settings.mode' => ['nullable', 'in:raw,template'],
            'settings.template_key' => ['nullable', 'string', 'max:255'],
            'settings.template_alias' => ['nullable', 'string', 'max:255'],
            'settings.bounce_address' => ['nullable', 'email', 'max:255'],
            'settings.auth_method' => ['nullable', 'in:app_password,oauth'],
        ];

        if ($isApiFlow) {
            $rules['hostname'] = ['nullable', 'string', 'max:255'];
            $rules['username'] = ['nullable', 'string', 'max:255'];
            $rules['password'] = ['nullable', 'string', 'max:255'];
            $rules['encryption'] = ['nullable', 'in:ssl,tls,none'];
            $rules['settings.send_mail_token'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        if (in_array((string) ($validated['type'] ?? ''), self::EXTENDED_MAILBOX_TYPES, true) && !$this->isExtendedLicenseAvailable()) {
            throw ValidationException::withMessages([
                'type' => 'Extended license is required to use Gmail/Outlook mailbox providers.',
            ]);
        }

        $this->normalizeExtendedMailboxInput($validated, $request);

        $apiTypes = ['amazon-ses', 'mailgun', 'sendgrid', 'postmark', 'sparkpost', 'zeptomail-api'];
        if ($isApiFlow && !in_array($validated['type'], $apiTypes, true)) {
            throw ValidationException::withMessages([
                'type' => 'Invalid server type for API flow.',
            ]);
        }

        if ($isApiFlow && in_array($validated['type'], $apiTypes, true)) {
            $validated['tracking_domain_id'] = null;
            $validated['bounce_server_id'] = null;
        }

        $settings = [];

        if (in_array($validated['type'], self::EXTENDED_MAILBOX_TYPES, true)) {
            $settings['provider'] = $validated['type'];
            $settings['auth_method'] = 'app_password';
        }

        switch ($validated['type']) {
            case 'mailgun':
                if ($request->has('settings.domain')) {
                    $settings['domain'] = $request->input('settings.domain');
                }
                if ($request->has('settings.secret')) {
                    $secret = $request->input('settings.secret');
                    if (is_string($secret) && trim($secret) !== '' && trim($secret) !== '********') {
                        $settings['secret'] = $secret;
                    }
                }
                break;
            case 'sendgrid':
                if ($request->has('settings.api_key')) {
                    $apiKey = $request->input('settings.api_key');
                    if (is_string($apiKey) && trim($apiKey) !== '' && trim($apiKey) !== '********') {
                        $settings['api_key'] = $apiKey;
                    }
                }
                break;
            case 'postmark':
                if ($request->has('settings.token')) {
                    $token = $request->input('settings.token');
                    if (is_string($token) && trim($token) !== '' && trim($token) !== '********') {
                        $settings['token'] = $token;
                    }
                }
                break;
            case 'sparkpost':
                if ($request->has('settings.secret')) {
                    $secret = $request->input('settings.secret');
                    if (is_string($secret) && trim($secret) !== '' && trim($secret) !== '********') {
                        $settings['secret'] = $secret;
                    }
                }
                break;
            case 'amazon-ses':
                if ($request->has('settings.key')) {
                    $key = $request->input('settings.key');
                    if (is_string($key) && trim($key) !== '' && trim($key) !== '********') {
                        $settings['key'] = $key;
                    }
                }
                if ($request->has('settings.secret')) {
                    $secret = $request->input('settings.secret');
                    if (is_string($secret) && trim($secret) !== '' && trim($secret) !== '********') {
                        $settings['secret'] = $secret;
                    }
                }
                if ($request->has('settings.region')) {
                    $region = $request->input('settings.region');
                    if (is_string($region) && trim($region) !== '') {
                        $settings['region'] = $region;
                    }
                }
                break;

            case 'zeptomail-api':
                if ($request->has('settings.send_mail_token')) {
                    $token = $request->input('settings.send_mail_token');
                    if (is_string($token) && trim($token) !== '' && trim($token) !== '********') {
                        $settings['send_mail_token'] = $token;
                    }
                }
                if ($request->has('settings.mode')) {
                    $mode = $request->input('settings.mode');
                    if (is_string($mode) && in_array($mode, ['raw', 'template'], true)) {
                        $settings['mode'] = $mode;
                    }
                }
                if ($request->has('settings.template_key')) {
                    $templateKey = $request->input('settings.template_key');
                    if (is_string($templateKey) && trim($templateKey) !== '') {
                        $settings['template_key'] = $templateKey;
                    }
                }
                if ($request->has('settings.template_alias')) {
                    $templateAlias = $request->input('settings.template_alias');
                    if (is_string($templateAlias) && trim($templateAlias) !== '') {
                        $settings['template_alias'] = $templateAlias;
                    }
                }
                if ($request->has('settings.bounce_address')) {
                    $bounceAddress = $request->input('settings.bounce_address');
                    if (is_string($bounceAddress) && trim($bounceAddress) !== '') {
                        $settings['bounce_address'] = $bounceAddress;
                    }
                }
                break;
        }

        if ($isApiFlow && in_array($validated['type'], ['amazon-ses', 'mailgun', 'sendgrid', 'postmark', 'sparkpost', 'zeptomail-api'], true)) {
            $validated['hostname'] = null;
            $validated['port'] = null;
            $validated['username'] = null;
            $validated['password'] = null;
            $validated['encryption'] = null;
            $validated['tracking_domain_id'] = null;
            $validated['bounce_server_id'] = null;
        }
        $validated['settings'] = $settings;

        // Explicitly handle boolean checkboxes that may not be sent when unchecked
        $validated['locked'] = $request->has('locked') ? (bool) $request->input('locked') : false;
        $validated['use_for'] = $request->has('use_for') ? (bool) $request->input('use_for') : false;
        $validated['use_for_transactional'] = $request->has('use_for_transactional') ? (bool) $request->input('use_for_transactional') : false;
        $validated['use_for_email_to_list'] = $request->has('use_for_email_to_list') ? (bool) $request->input('use_for_email_to_list') : false;

        $deliveryServer = $this->deliveryServerService->create($validated);

        return redirect()
            ->route('admin.delivery-servers.show', $deliveryServer)
            ->with('success', 'Delivery server created successfully.');
    }

    /**
     * Display the specified delivery server.
     */
    public function show(DeliveryServer $deliveryServer)
    {
        $this->authorizeSystemServer($deliveryServer);
        $deliveryServer->load(['trackingDomain', 'bounceServer']);

        $clockSkewStatus = Cache::get($this->clockSkewCacheKey($deliveryServer));
        $clockSkewLastRestart = Cache::get($this->clockSkewCacheKey($deliveryServer) . ':last_restart');

        $deliveryLogs = $deliveryServer->deliveryLogs()
            ->latest()
            ->limit(20)
            ->get();

        $bounceLogs = $deliveryServer->bounce_server_id
            ? \App\Models\BounceLog::where('bounce_server_id', $deliveryServer->bounce_server_id)
                ->latest('bounced_at')
                ->limit(20)
                ->get()
            : collect();

        $failedRecipients = \App\Models\CampaignRecipient::query()
            ->whereIn('campaign_id', \App\Models\Campaign::where('delivery_server_id', $deliveryServer->id)->pluck('id'))
            ->whereIn('status', ['bounced', 'failed'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('admin.delivery-servers.show', compact(
            'deliveryServer', 'clockSkewStatus', 'clockSkewLastRestart',
            'deliveryLogs', 'bounceLogs', 'failedRecipients'
        ));
    }

    public function clockSkewCheck(Request $request, DeliveryServer $delivery_server)
    {
        $deliveryServer = $this->authorizeSystemServer($delivery_server);

        $url = (string) config('services.clock_skew_check.url', 'https://www.google.com');
        $thresholdSeconds = (int) config('services.clock_skew_check.threshold_seconds', 120);
        $timeoutSeconds = (int) config('services.clock_skew_check.timeout_seconds', 5);

        try {
            $response = Http::timeout($timeoutSeconds)
                ->withHeaders([
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                ])
                ->head($url);

            $dateHeader = $response->header('Date');
            if (!is_string($dateHeader) || trim($dateHeader) === '') {
                throw new \RuntimeException('No Date header returned by time-check endpoint.');
            }

            $remoteUtc = CarbonImmutable::parse($dateHeader, 'UTC');
            $localUtc = CarbonImmutable::now('UTC');
            $skewSeconds = abs($localUtc->diffInSeconds($remoteUtc, false));

            $status = [
                'checked_at' => CarbonImmutable::now()->toIso8601String(),
                'url' => $url,
                'threshold_seconds' => $thresholdSeconds,
                'timeout_seconds' => $timeoutSeconds,
                'skew_seconds' => $skewSeconds,
                'server_utc' => $localUtc->toIso8601String(),
                'remote_utc' => $remoteUtc->toIso8601String(),
                'ok' => $skewSeconds <= $thresholdSeconds,
            ];

            Cache::put($this->clockSkewCacheKey($deliveryServer), $status, now()->addHours(6));

            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with($status['ok'] ? 'success' : 'error', $status['ok']
                    ? 'Clock skew check OK.'
                    : 'Clock skew is too high. Sending may fail until server time is fixed.');
        } catch (\Throwable $e) {
            $status = [
                'checked_at' => CarbonImmutable::now()->toIso8601String(),
                'url' => $url,
                'threshold_seconds' => $thresholdSeconds,
                'timeout_seconds' => $timeoutSeconds,
                'ok' => false,
                'error' => $e->getMessage(),
            ];

            Cache::put($this->clockSkewCacheKey($deliveryServer), $status, now()->addHours(6));

            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('error', 'Clock skew check failed: ' . $e->getMessage());
        }
    }

    public function restartWorkers(Request $request, DeliveryServer $delivery_server)
    {
        $deliveryServer = $this->authorizeSystemServer($delivery_server);

        Artisan::call('queue:restart');

        Cache::put($this->clockSkewCacheKey($deliveryServer) . ':last_restart', [
            'restarted_at' => CarbonImmutable::now()->toIso8601String(),
        ], now()->addHours(6));

        return redirect()
            ->route('admin.delivery-servers.show', $deliveryServer)
            ->with('success', 'Queue workers restart signal sent.');
    }

    public function sendTestEmail(Request $request, DeliveryServer $delivery_server)
    {
        $deliveryServer = $this->authorizeSystemServer($delivery_server);

        $validated = $request->validate([
            'to_email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        try {
            $this->deliveryServerService->testConnection([
                'test_type' => 'server',
                'server_id' => $deliveryServer->id,
                'to_email' => $validated['to_email'],
                'subject' => $validated['subject'] ?? 'This is a Test Email',
                'message' => $validated['message'] ?? 'This is a Test Email. If you received this email, your delivery server is working correctly!',
            ]);

            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('success', 'Test email sent successfully!');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->withInput()
                ->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    private function clockSkewCacheKey(DeliveryServer $deliveryServer): string
    {
        return 'delivery_servers:' . $deliveryServer->id . ':clock_skew_status';
    }

    /**
     * Show the form for editing the specified delivery server.
     */
    public function edit(DeliveryServer $deliveryServer)
    {
        $this->authorizeSystemServer($deliveryServer);
        $trackingDomains = TrackingDomain::where('status', 'verified')->get();
        $bounceServers = BounceServer::where('active', true)->orderBy('name')->get();

        return view('admin.delivery-servers.edit', compact('deliveryServer', 'trackingDomains', 'bounceServers'));
    }

    /**
     * Update the specified delivery server.
     */
    public function update(Request $request, DeliveryServer $deliveryServer)
    {
        $this->authorizeSystemServer($deliveryServer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:smtp,sendmail,gmail,outlook,amazon-ses,mailgun,sendgrid,postmark,sparkpost,zeptomail,zeptomail-api'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'hostname' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:ssl,tls,none'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1'],
            'max_connection_messages' => ['nullable', 'integer', 'min:1'],
            'second_quota' => ['nullable', 'integer', 'min:0'],
            'minute_quota' => ['nullable', 'integer', 'min:0'],
            'hourly_quota' => ['nullable', 'integer', 'min:0'],
            'daily_quota' => ['nullable', 'integer', 'min:0'],
            'monthly_quota' => ['nullable', 'integer', 'min:0'],
            'pause_after_send' => ['nullable', 'integer', 'min:0'],
            'locked' => ['nullable', 'boolean'],
            'use_for' => ['nullable', 'boolean'],
            'use_for_email_to_list' => ['nullable', 'boolean'],
            'use_for_transactional' => ['nullable', 'boolean'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'bounce_server_id' => ['nullable', 'exists:bounce_servers,id'],
            'notes' => ['nullable', 'string'],
            // Mailgun-specific settings
            'settings.domain' => ['nullable', 'string', 'max:255'],
            'settings.secret' => ['nullable', 'string', 'max:255'],
            // Other provider API settings
            'settings.api_key' => ['nullable', 'string', 'max:255'],
            'settings.token' => ['nullable', 'string', 'max:255'],
            'settings.key' => ['nullable', 'string', 'max:255'],
            'settings.region' => ['nullable', 'string', 'max:255'],
            'settings.send_mail_token' => ['nullable', 'string', 'max:255'],
            'settings.mode' => ['nullable', 'in:raw,template'],
            'settings.template_key' => ['nullable', 'string', 'max:255'],
            'settings.template_alias' => ['nullable', 'string', 'max:255'],
            'settings.bounce_address' => ['nullable', 'email', 'max:255'],
            'settings.auth_method' => ['nullable', 'in:app_password,oauth'],
        ]);

        if (in_array((string) ($validated['type'] ?? ''), self::EXTENDED_MAILBOX_TYPES, true) && !$this->isExtendedLicenseAvailable()) {
            throw ValidationException::withMessages([
                'type' => 'Extended license is required to use Gmail/Outlook mailbox providers.',
            ]);
        }

        $this->normalizeExtendedMailboxInput($validated, $request);

        if (array_key_exists('password', $validated)) {
            $password = $validated['password'];
            if (!is_string($password) || trim($password) === '' || trim($password) === '********') {
                unset($validated['password']);
            }
        }

        // Build settings array for providers
        $settings = $deliveryServer->settings ?? [];

        if (in_array($validated['type'], self::EXTENDED_MAILBOX_TYPES, true)) {
            $settings['provider'] = $validated['type'];
            $settings['auth_method'] = 'app_password';
        }

        if ($validated['type'] === 'mailgun') {
            if ($request->has('settings.domain')) {
                $settings['domain'] = $request->input('settings.domain');
            }
            if ($request->has('settings.secret')) {
                $secret = $request->input('settings.secret');
                if (is_string($secret) && trim($secret) !== '' && trim($secret) !== '********') {
                    $settings['secret'] = $secret;
                }
            }
        }

        if ($validated['type'] === 'sendgrid') {
            if ($request->has('settings.api_key')) {
                $apiKey = $request->input('settings.api_key');
                if (is_string($apiKey) && trim($apiKey) !== '' && trim($apiKey) !== '********') {
                    $settings['api_key'] = $apiKey;
                }
            }
        }

        if ($validated['type'] === 'postmark') {
            if ($request->has('settings.token')) {
                $token = $request->input('settings.token');
                if (is_string($token) && trim($token) !== '' && trim($token) !== '********') {
                    $settings['token'] = $token;
                }
            }
        }

        if ($validated['type'] === 'sparkpost') {
            if ($request->has('settings.secret')) {
                $secret = $request->input('settings.secret');
                if (is_string($secret) && trim($secret) !== '' && trim($secret) !== '********') {
                    $settings['secret'] = $secret;
                }
            }
        }

        if ($validated['type'] === 'amazon-ses') {
            if ($request->has('settings.key')) {
                $key = $request->input('settings.key');
                if (is_string($key) && trim($key) !== '' && trim($key) !== '********') {
                    $settings['key'] = $key;
                }
            }
            if ($request->has('settings.secret')) {
                $secret = $request->input('settings.secret');
                if (is_string($secret) && trim($secret) !== '' && trim($secret) !== '********') {
                    $settings['secret'] = $secret;
                }
            }
            if ($request->has('settings.region')) {
                $region = $request->input('settings.region');
                if (is_string($region) && trim($region) !== '') {
                    $settings['region'] = $region;
                }
            }
        }

        if ($validated['type'] === 'zeptomail-api') {
            if ($request->has('settings.send_mail_token')) {
                $token = $request->input('settings.send_mail_token');
                if (is_string($token) && trim($token) !== '' && trim($token) !== '********') {
                    $settings['send_mail_token'] = $token;
                }
            }

            if ($request->has('settings.mode')) {
                $mode = $request->input('settings.mode');
                if (is_string($mode) && in_array($mode, ['raw', 'template'], true)) {
                    $settings['mode'] = $mode;
                }
            }

            if ($request->has('settings.template_key')) {
                $templateKey = $request->input('settings.template_key');
                if (is_string($templateKey) && trim($templateKey) !== '') {
                    $settings['template_key'] = $templateKey;
                }
            }

            if ($request->has('settings.template_alias')) {
                $templateAlias = $request->input('settings.template_alias');
                if (is_string($templateAlias) && trim($templateAlias) !== '') {
                    $settings['template_alias'] = $templateAlias;
                }
            }

            if ($request->has('settings.bounce_address')) {
                $bounceAddress = $request->input('settings.bounce_address');
                if (is_string($bounceAddress) && trim($bounceAddress) !== '') {
                    $settings['bounce_address'] = $bounceAddress;
                }
            }
        }

        $validated['settings'] = $settings;

        // Explicitly handle boolean checkboxes that may not be sent when unchecked
        $validated['locked'] = $request->has('locked') ? (bool) $request->input('locked') : false;
        $validated['use_for'] = $request->has('use_for') ? (bool) $request->input('use_for') : false;
        $validated['use_for_transactional'] = $request->has('use_for_transactional') ? (bool) $request->input('use_for_transactional') : false;
        $validated['use_for_email_to_list'] = $request->has('use_for_email_to_list') ? (bool) $request->input('use_for_email_to_list') : false;

        $this->deliveryServerService->update($deliveryServer, $validated);

        return redirect()
            ->route('admin.delivery-servers.show', $deliveryServer)
            ->with('success', 'Delivery server updated successfully.');
    }

    /**
     * Remove the specified delivery server.
     */
    public function destroy(DeliveryServer $deliveryServer)
    {
        $this->authorizeSystemServer($deliveryServer);
        $this->deliveryServerService->delete($deliveryServer);

        return redirect()
            ->route('admin.delivery-servers.index')
            ->with('success', 'Delivery server deleted successfully.');
    }

    public function makePrimary(DeliveryServer $deliveryServer)
    {
        $this->authorizeSystemServer($deliveryServer);
        $this->deliveryServerService->setPrimary($deliveryServer);

        return redirect()
            ->back()
            ->with('success', 'Primary delivery server updated successfully.');
    }

    /**
     * Show the SMTP/API test page.
     */
    public function showTest()
    {
        $this->deliveryServerService->getOrCreateSystemSmtpDeliveryServer();
        $deliveryServers = DeliveryServer::where('status', 'active')->whereNull('customer_id')->get();
        
        return view('admin.delivery-servers.test', compact('deliveryServers'));
    }

    /**
     * Test SMTP/API connection and send test email.
     */
    public function test(Request $request)
    {
        $validated = $request->validate([
            'test_type' => ['required', 'in:server,manual'],
            'server_id' => ['required_if:test_type,server', 'exists:delivery_servers,id'],
            'type' => ['required_if:test_type,manual', 'in:smtp,sendmail,amazon-ses,mailgun,mailjet,sendgrid,postmark,sparkpost,zeptomail'],
            'hostname' => ['required_if:test_type,manual', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:test_type,manual', 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:ssl,tls,none'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
            'api_hostname' => ['nullable', 'string', 'max:255'],
            'to_email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        try {
            @set_time_limit(120);

            \Log::info('Test email request received', [
                'test_type' => $validated['test_type'] ?? 'unknown',
                'to_email' => $validated['to_email'] ?? null,
            ]);

            $result = $this->deliveryServerService->testConnection($validated);

            \Log::info('Test email sent successfully', $result);

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!',
                'details' => $result,
            ]);
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            \Log::error('Symfony Mailer transport exception in test controller', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'SMTP Connection Error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'type' => 'connection_error',
            ], 422);
        } catch (\Swift_TransportException $e) {
            \Log::error('Swift Transport Exception in test controller', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'SMTP Connection Error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'type' => 'connection_error',
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in test controller', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation Error: Please check all required fields are filled correctly.',
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
                'type' => 'validation_error',
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Exception in test controller', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 422);
        }
    }

    /**
     * Verify delivery server using token.
     */
    public function verify(DeliveryServer $deliveryServer, string $token)
    {
        $this->authorizeSystemServer($deliveryServer);
        if (!in_array($deliveryServer->type, ['smtp', 'gmail', 'outlook'], true)) {
            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('info', 'Verification is only required for SMTP delivery servers.');
        }

        // Verify token matches
        if ($deliveryServer->verification_token !== $token) {
            return redirect()
                ->route('admin.delivery-servers.index')
                ->with('error', 'Invalid verification token.');
        }

        // Check if already verified
        if ($deliveryServer->isVerified()) {
            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('info', 'This delivery server is already verified.');
        }

        // Verify the server
        $verified = $this->deliveryServerService->verify($token);

        if (!$verified) {
            return redirect()
                ->route('admin.delivery-servers.index')
                ->with('error', 'Invalid or expired verification token.');
        }

        return redirect()
            ->route('admin.delivery-servers.show', $deliveryServer)
            ->with('success', 'Delivery server verified successfully!');
    }

    /**
     * Resend verification email.
     */
    public function resendVerification(DeliveryServer $deliveryServer)
    {
        $this->authorizeSystemServer($deliveryServer);
        if (!in_array($deliveryServer->type, ['smtp', 'gmail', 'outlook'], true)) {
            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('info', 'Verification is only required for SMTP delivery servers.');
        }

        // Check if username is a valid email address
        if (empty($deliveryServer->username) || !filter_var($deliveryServer->username, FILTER_VALIDATE_EMAIL)) {
            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('error', 'Cannot send verification email: SMTP account email (username) is not configured or invalid.');
        }

        if ($deliveryServer->isVerified()) {
            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('info', 'This delivery server is already verified.');
        }

        $sent = $this->deliveryServerService->sendVerificationEmail($deliveryServer);

        if (!$sent) {
            return redirect()
                ->route('admin.delivery-servers.show', $deliveryServer)
                ->with('error', 'Failed to send verification email. Please check your mail configuration and logs.');
        }

        return redirect()
            ->route('admin.delivery-servers.show', $deliveryServer)
            ->with('success', 'Verification email sent successfully! Check your inbox at ' . $deliveryServer->username);
    }
}

