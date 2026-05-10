<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BounceServer;
use App\Models\DeliveryServer;
use App\Models\ReplyServer;
use App\Models\Setting;
use App\Models\TrackingDomain;
use App\Services\DeliveryServerService;
use App\Services\UpdateServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class DeliveryServerController extends Controller
{
    private const EXTENDED_MAILBOX_TYPES = ['gmail', 'outlook'];

    public function __construct(
        protected DeliveryServerService $deliveryServerService,
        protected UpdateServerService $updateServerService
    ) {
        $this->middleware('customer.access:servers.permissions.can_access_delivery_servers')->only([
            'index',
            'show',
        ]);
        $this->middleware('customer.access:servers.permissions.can_create_delivery_servers')->only([
            'create',
            'store',
        ]);
        $this->middleware('customer.access:servers.permissions.can_edit_delivery_servers')->only([
            'edit',
            'update',
            'sendTestEmail',
            'verify',
            'resendVerification',
        ]);
        $this->middleware('customer.access:servers.permissions.can_delete_delivery_servers')->only([
            'destroy',
        ]);

        $this->middleware('demo.prevent')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    protected function authorizeManage(DeliveryServer $deliveryServer): DeliveryServer
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $deliveryServer->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $deliveryServer;
    }

    protected function authorizeView(DeliveryServer $deliveryServer): DeliveryServer
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ((int) $deliveryServer->customer_id === (int) $customer->id) {
            return $deliveryServer;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);

        if (!$mustAddOwn && $canUseSystem && $deliveryServer->customer_id === null && $deliveryServer->status === 'active') {
            return $deliveryServer;
        }

        abort(404);
    }

    protected function canUseExtendedMailboxProvidersForCustomer($customer): bool
    {
        if (!$customer) {
            return false;
        }

        $groupAllows = (bool) $customer->groupSetting('servers.permissions.can_use_extended_mailbox_providers', false);
        if (!$groupAllows) {
            return false;
        }

        return $this->isExtendedLicenseAvailable();
    }

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
            $licenseCheck = $this->fetchAndCacheLicenseCheck($cacheKey, $licenseKey, $domain);
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

    protected function fetchAndCacheLicenseCheck(string $cacheKey, string $licenseKey, string $domain): ?array
    {
        $productSecret = (string) config('services.update_server.product_secret', Setting::get('update_product_secret'));
        $productName = (string) config('services.update_server.product_name', Setting::get('update_product_name'));

        if ($productSecret === '' || $productName === '') {
            return null;
        }

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($licenseKey, $domain, $productSecret, $productName) {
            return $this->updateServerService->licenseCheck(
                (string) $licenseKey,
                $domain,
                (string) $productSecret,
                (string) $productName
            );
        });
    }

    public function revealSecret(Request $request, DeliveryServer $delivery_server)
    {
        $deliveryServer = $this->authorizeView($delivery_server);

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

    protected function ensureCanSelectTrackingDomain(?int $trackingDomainId): void
    {
        if ($trackingDomainId === null) {
            return;
        }

        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        $canSelect = (bool) $customer->groupSetting('domains.tracking_domains.select_for_servers', false);
        if (!$canSelect) {
            throw ValidationException::withMessages([
                'tracking_domain_id' => 'Tracking domain selection is not allowed for your account.',
            ]);
        }

        $trackingDomain = TrackingDomain::find($trackingDomainId);
        if (!$trackingDomain || $trackingDomain->status !== 'verified') {
            throw ValidationException::withMessages([
                'tracking_domain_id' => 'Selected tracking domain is invalid.',
            ]);
        }

        if ((int) $trackingDomain->customer_id === (int) $customer->id) {
            return;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);

        if (!$mustAddOwn && $canUseSystem && $trackingDomain->customer_id === null) {
            return;
        }

        throw ValidationException::withMessages([
            'tracking_domain_id' => 'Selected tracking domain is not available for your account.',
        ]);
    }

    protected function ensureCanSelectBounceServer(?int $bounceServerId): void
    {
        if ($bounceServerId === null) {
            return;
        }

        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        $bounceServer = BounceServer::find($bounceServerId);
        if (!$bounceServer || !$bounceServer->isActive()) {
            throw ValidationException::withMessages([
                'bounce_server_id' => 'Selected bounce server is invalid.',
            ]);
        }

        if ((int) $bounceServer->customer_id === (int) $customer->id) {
            return;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);

        if (!$mustAddOwn && $canUseSystem && $bounceServer->customer_id === null) {
            return;
        }

        throw ValidationException::withMessages([
            'bounce_server_id' => 'Selected bounce server is not available for your account.',
        ]);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $filters = $request->only(['search', 'type', 'status']);

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $canUseExtendedMailboxProviders = $this->canUseExtendedMailboxProvidersForCustomer($customer);

        $deliveryServers = DeliveryServer::query()
            ->when($mustAddOwn, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhere(function ($sys) {
                            $sys->whereNull('customer_id')->where('status', 'active');
                        });
                    }
                });
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('hostname', 'like', "%{$search}%")
                        ->orWhere('from_email', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('customer.delivery-servers.index', compact('deliveryServers', 'filters', 'canUseExtendedMailboxProviders'));
    }

    public function create(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('servers.limits.max_delivery_servers', $customer->deliveryServers()->count(), 'Delivery server limit reached.');
        $canUseExtendedMailboxProviders = $this->canUseExtendedMailboxProvidersForCustomer($customer);

        $flow = $request->query('flow');
        $flow = in_array($flow, ['smtp', 'api'], true) ? $flow : null;
        
        $type = $request->query('type');
        $allowedTypes = ['smtp', 'sendmail', 'amazon-ses', 'mailgun', 'sendgrid', 'postmark', 'sparkpost', 'zeptomail', 'zeptomail-api', 'gmail', 'outlook'];
        $type = in_array($type, $allowedTypes, true) ? $type : null;

        $canSelectTracking = (bool) $customer->groupSetting('domains.tracking_domains.select_for_servers', false);
        $mustAddTracking = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);
        $mustAddBounce = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $trackingDomains = TrackingDomain::query()
            ->where('status', 'verified')
            ->when($canSelectTracking, function ($q) use ($customer, $mustAddTracking, $canUseSystem) {
                $q->when($mustAddTracking, function ($sub) use ($customer) {
                    $sub->where('customer_id', $customer->id);
                }, function ($sub) use ($customer, $canUseSystem) {
                    $sub->where(function ($inner) use ($customer, $canUseSystem) {
                        $inner->where('customer_id', $customer->id);
                        if ($canUseSystem) {
                            $inner->orWhereNull('customer_id');
                        }
                    });
                });
            }, function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->orderBy('domain')
            ->get();

        $bounceServers = BounceServer::query()
            ->where('active', true)
            ->when($mustAddBounce, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    $sub->orWhereNull('customer_id');
                });
            })
            ->orderBy('name')
            ->get();

        if (!$canUseExtendedMailboxProviders && in_array((string) $type, self::EXTENDED_MAILBOX_TYPES, true)) {
            $type = 'smtp';
        }

        return view('customer.delivery-servers.create', compact('trackingDomains', 'bounceServers', 'flow', 'type', 'canUseExtendedMailboxProviders'));
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('servers.limits.max_delivery_servers', $customer->deliveryServers()->count(), 'Delivery server limit reached.');
        $canUseExtendedMailboxProviders = $this->canUseExtendedMailboxProvidersForCustomer($customer);

        $flow = $request->input('flow');
        $flow = in_array($flow, ['smtp', 'api'], true) ? $flow : null;
        $isApiFlow = $flow === 'api';

        $rules = [
            'flow' => ['nullable', 'in:smtp,api'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:smtp,sendmail,amazon-ses,mailgun,sendgrid,postmark,sparkpost,zeptomail,zeptomail-api,gmail,outlook'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'hostname' => ['required_if:type,smtp,gmail,outlook', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:type,smtp,gmail,outlook', 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['required_if:type,smtp,gmail,outlook', 'nullable', 'string', 'max:255'],
            'password' => ['required_if:type,smtp,gmail,outlook', 'nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:ssl,tls,none'],
            'from_email' => ['required_if:type,amazon-ses', 'nullable', 'email', 'max:255'],
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
            'settings.domain' => ['required_if:type,mailgun', 'nullable', 'string', 'max:255'],
            'settings.secret' => ['required_if:type,mailgun,sparkpost,amazon-ses', 'nullable', 'string', 'max:255'],
            'settings.api_key' => ['required_if:type,sendgrid', 'nullable', 'string', 'max:255'],
            'settings.token' => ['required_if:type,postmark', 'nullable', 'string', 'max:255'],
            'settings.key' => ['required_if:type,amazon-ses', 'nullable', 'string', 'max:255'],
            'settings.region' => ['nullable', 'string', 'max:255'],
            'settings.send_mail_token' => ['required_if:type,zeptomail-api', 'nullable', 'string', 'max:255'],
            'settings.mode' => ['nullable', 'in:raw,template'],
            'settings.template_key' => ['nullable', 'string', 'max:255'],
            'settings.template_alias' => ['nullable', 'string', 'max:255'],
            'settings.bounce_address' => ['nullable', 'email', 'max:255'],
        ];

        if ($isApiFlow) {
            $rules['hostname'] = ['nullable', 'string', 'max:255'];
            $rules['username'] = ['nullable', 'string', 'max:255'];
            $rules['password'] = ['nullable', 'string', 'max:255'];
            $rules['encryption'] = ['nullable', 'in:ssl,tls,none'];
            $rules['settings.send_mail_token'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        if (in_array((string) ($validated['type'] ?? ''), self::EXTENDED_MAILBOX_TYPES, true) && !$canUseExtendedMailboxProviders) {
            throw ValidationException::withMessages([
                'type' => 'This is an Extended License feature. Please upgrade to use Gmail/Outlook mailbox providers.',
            ]);
        }

        $apiTypes = ['amazon-ses', 'mailgun', 'sendgrid', 'postmark', 'sparkpost', 'zeptomail-api'];
        if ($isApiFlow && !in_array($validated['type'], $apiTypes, true)) {
            throw ValidationException::withMessages([
                'type' => 'Invalid server type for API flow.',
            ]);
        }

        $validated['locked'] = $request->boolean('locked');
        $validated['use_for'] = $request->boolean('use_for');
        $validated['use_for_email_to_list'] = $request->boolean('use_for_email_to_list');
        $validated['use_for_transactional'] = $request->boolean('use_for_transactional');

        if ($isApiFlow && in_array($validated['type'], $apiTypes, true)) {
            $validated['tracking_domain_id'] = null;
            $validated['bounce_server_id'] = null;
        }

        $this->ensureCanSelectTrackingDomain($validated['tracking_domain_id'] ?? null);
        $this->ensureCanSelectBounceServer($validated['bounce_server_id'] ?? null);

        $settings = [];

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
        $validated['customer_id'] = $customer->id;

        $deliveryServer = $this->deliveryServerService->create($validated);

        $this->syncAdditionalServerTypes($request, $customer->id, $deliveryServer);

        return redirect()
            ->route('customer.delivery-servers.show', $deliveryServer)
            ->with('success', 'Delivery server created successfully.');
    }

    public function show(DeliveryServer $delivery_server)
    {
        $this->authorizeView($delivery_server);
        $delivery_server->load(['trackingDomain', 'bounceServer']);

        $deliveryLogs = $delivery_server->deliveryLogs()
            ->latest()
            ->limit(20)
            ->get();

        $bounceLogs = $delivery_server->bounce_server_id
            ? \App\Models\BounceLog::where('bounce_server_id', $delivery_server->bounce_server_id)
                ->latest('bounced_at')
                ->limit(20)
                ->get()
            : collect();

        $failedRecipients = \App\Models\CampaignRecipient::query()
            ->whereIn('campaign_id', \App\Models\Campaign::where('delivery_server_id', $delivery_server->id)->pluck('id'))
            ->whereIn('status', ['bounced', 'failed'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('customer.delivery-servers.show', [
            'deliveryServer' => $delivery_server,
            'deliveryLogs' => $deliveryLogs,
            'bounceLogs' => $bounceLogs,
            'failedRecipients' => $failedRecipients,
        ]);
    }

    public function sendTestEmail(Request $request, DeliveryServer $delivery_server)
    {
        $this->authorizeView($delivery_server);

        $validated = $request->validate([
            'to_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $this->deliveryServerService->testConnection([
                'test_type' => 'server',
                'server_id' => $delivery_server->id,
                'to_email' => $validated['to_email'],
                'subject' => 'This is a Test Email',
                'message' => 'This is a Test Email. If you received this email, your delivery server is working correctly!',
            ]);

            return back()->with('success', 'Test email sent successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function edit(DeliveryServer $delivery_server)
    {
        $this->authorizeManage($delivery_server);
        $customer = auth('customer')->user();
        $canUseExtendedMailboxProviders = $this->canUseExtendedMailboxProvidersForCustomer($customer);

        $canSelectTracking = (bool) $customer->groupSetting('domains.tracking_domains.select_for_servers', false);
        $mustAddTracking = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);
        $mustAddBounce = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $trackingDomains = TrackingDomain::query()
            ->where('status', 'verified')
            ->when($canSelectTracking, function ($q) use ($customer, $mustAddTracking, $canUseSystem) {
                $q->when($mustAddTracking, function ($sub) use ($customer) {
                    $sub->where('customer_id', $customer->id);
                }, function ($sub) use ($customer, $canUseSystem) {
                    $sub->where(function ($inner) use ($customer, $canUseSystem) {
                        $inner->where('customer_id', $customer->id);
                        if ($canUseSystem) {
                            $inner->orWhereNull('customer_id');
                        }
                    });
                });
            }, function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->orderBy('domain')
            ->get();

        $bounceServers = BounceServer::query()
            ->where('active', true)
            ->when($mustAddBounce, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('name')
            ->get();

        return view('customer.delivery-servers.edit', [
            'deliveryServer' => $delivery_server,
            'trackingDomains' => $trackingDomains,
            'bounceServers' => $bounceServers,
            'canUseExtendedMailboxProviders' => $canUseExtendedMailboxProviders,
        ]);
    }

    public function update(Request $request, DeliveryServer $delivery_server)
    {
        $this->authorizeManage($delivery_server);
        $customer = auth('customer')->user();
        $canUseExtendedMailboxProviders = $this->canUseExtendedMailboxProvidersForCustomer($customer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:smtp,sendmail,amazon-ses,mailgun,sendgrid,postmark,sparkpost,zeptomail,zeptomail-api,gmail,outlook'],
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
            'settings.domain' => ['nullable', 'string', 'max:255'],
            'settings.secret' => ['nullable', 'string', 'max:255'],
            'settings.api_key' => ['nullable', 'string', 'max:255'],
            'settings.token' => ['nullable', 'string', 'max:255'],
            'settings.key' => ['nullable', 'string', 'max:255'],
            'settings.region' => ['nullable', 'string', 'max:255'],
            'settings.send_mail_token' => ['nullable', 'string', 'max:255'],
            'settings.mode' => ['nullable', 'in:raw,template'],
            'settings.template_key' => ['nullable', 'string', 'max:255'],
            'settings.template_alias' => ['nullable', 'string', 'max:255'],
            'settings.bounce_address' => ['nullable', 'email', 'max:255'],
        ]);

        if (in_array((string) ($validated['type'] ?? ''), self::EXTENDED_MAILBOX_TYPES, true) && !$canUseExtendedMailboxProviders) {
            throw ValidationException::withMessages([
                'type' => 'This is an Extended License feature. Please upgrade to use Gmail/Outlook mailbox providers.',
            ]);
        }

        if (array_key_exists('password', $validated)) {
            $password = $validated['password'];
            if (!is_string($password) || trim($password) === '' || trim($password) === '********') {
                unset($validated['password']);
            }
        }

        $validated['locked'] = $request->boolean('locked');
        $validated['use_for'] = $request->boolean('use_for');
        $validated['use_for_email_to_list'] = $request->boolean('use_for_email_to_list');
        $validated['use_for_transactional'] = $request->boolean('use_for_transactional');

        $this->ensureCanSelectTrackingDomain($validated['tracking_domain_id'] ?? null);
        $this->ensureCanSelectBounceServer($validated['bounce_server_id'] ?? null);

        $settings = $delivery_server->settings ?? [];

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

        $this->deliveryServerService->update($delivery_server, $validated);
        $delivery_server->refresh();
        $this->syncAdditionalServerTypes($request, $customer->id, $delivery_server);

        return redirect()
            ->route('customer.delivery-servers.show', $delivery_server)
            ->with('success', 'Delivery server updated successfully.');
    }

    public function destroy(DeliveryServer $delivery_server)
    {
        $this->authorizeManage($delivery_server);
        $this->deliveryServerService->delete($delivery_server);

        return redirect()
            ->route('customer.delivery-servers.index')
            ->with('success', 'Delivery server deleted successfully.');
    }

    public function verify(DeliveryServer $delivery_server, string $token)
    {
        $this->authorizeManage($delivery_server);

        if ($delivery_server->type !== 'smtp') {
            return redirect()
                ->route('customer.delivery-servers.show', $delivery_server)
                ->with('info', 'Verification is only required for SMTP delivery servers.');
        }

        if ($delivery_server->verification_token !== $token) {
            return redirect()
                ->route('customer.delivery-servers.index')
                ->with('error', 'Invalid verification token.');
        }

        if ($delivery_server->isVerified()) {
            return redirect()
                ->route('customer.delivery-servers.show', $delivery_server)
                ->with('info', 'This delivery server is already verified.');
        }

        $verified = $this->deliveryServerService->verify($token);

        if (!$verified) {
            return redirect()
                ->route('customer.delivery-servers.index')
                ->with('error', 'Invalid or expired verification token.');
        }

        return redirect()
            ->route('customer.delivery-servers.show', $delivery_server)
            ->with('success', 'Delivery server verified successfully!');
    }

    public function resendVerification(DeliveryServer $delivery_server)
    {
        $this->authorizeManage($delivery_server);

        if ($delivery_server->type !== 'smtp') {
            return redirect()
                ->route('customer.delivery-servers.show', $delivery_server)
                ->with('info', 'Verification is only required for SMTP delivery servers.');
        }

        if (empty($delivery_server->username) || !filter_var($delivery_server->username, FILTER_VALIDATE_EMAIL)) {
            return redirect()
                ->route('customer.delivery-servers.show', $delivery_server)
                ->with('error', 'Cannot send verification email: SMTP account email (username) is not configured or invalid.');
        }

        if ($delivery_server->isVerified()) {
            return redirect()
                ->route('customer.delivery-servers.show', $delivery_server)
                ->with('info', 'This delivery server is already verified.');
        }

        $sent = $this->deliveryServerService->sendVerificationEmail($delivery_server);

        if (!$sent) {
            return redirect()
                ->route('customer.delivery-servers.show', $delivery_server)
                ->with('error', 'Failed to send verification email. Please check your mail configuration and logs.');
        }

        return redirect()
            ->route('customer.delivery-servers.show', $delivery_server)
            ->with('success', 'Verification email sent successfully! Check your inbox at ' . $delivery_server->username);
    }

    protected function syncAdditionalServerTypes(Request $request, int $customerId, DeliveryServer $deliveryServer): void
    {
        if (blank($deliveryServer->hostname) || blank($deliveryServer->username)) {
            return;
        }

        if ($request->boolean('use_as_reply_server')) {
            ReplyServer::query()->updateOrCreate(
                [
                    'customer_id' => $customerId,
                    'hostname' => (string) ($deliveryServer->hostname ?? ''),
                    'port' => (int) ($deliveryServer->port ?? 993),
                    'username' => (string) ($deliveryServer->username ?? ''),
                ],
                [
                    'name' => $deliveryServer->name,
                    'reply_domain' => $this->extractReplyDomain($deliveryServer->from_email ?? $deliveryServer->username),
                    'protocol' => 'imap',
                    'encryption' => in_array($deliveryServer->encryption, ['ssl', 'tls', 'none'], true) ? $deliveryServer->encryption : 'ssl',
                    'password' => (string) ($deliveryServer->password ?? ''),
                    'mailbox' => 'INBOX',
                    'active' => $deliveryServer->status === 'active',
                    'delete_after_processing' => false,
                    'max_emails_per_batch' => 100,
                    'notes' => $deliveryServer->notes,
                ]
            );
        }

        if ($request->boolean('use_as_bounce_server')) {
            BounceServer::query()->updateOrCreate(
                [
                    'customer_id' => $customerId,
                    'hostname' => (string) ($deliveryServer->hostname ?? ''),
                    'port' => (int) ($deliveryServer->port ?? 993),
                    'username' => (string) ($deliveryServer->username ?? ''),
                ],
                [
                    'name' => $deliveryServer->name,
                    'protocol' => 'imap',
                    'encryption' => in_array($deliveryServer->encryption, ['ssl', 'tls', 'none'], true) ? $deliveryServer->encryption : 'ssl',
                    'password' => (string) ($deliveryServer->password ?? ''),
                    'mailbox' => 'INBOX',
                    'active' => $deliveryServer->status === 'active',
                    'delete_after_processing' => false,
                    'max_emails_per_batch' => 100,
                    'notes' => $deliveryServer->notes,
                ]
            );
        }
    }

    protected function extractReplyDomain(?string $email): ?string
    {
        $email = trim((string) $email);

        if ($email === '' || !str_contains($email, '@')) {
            return null;
        }

        return ltrim((string) strrchr($email, '@'), '@') ?: null;
    }
}
