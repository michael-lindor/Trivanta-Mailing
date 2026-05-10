<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerGroupStoreRequest;
use App\Http\Requests\Admin\CustomerGroupUpdateRequest;
use App\Models\CustomerGroup;
use App\Models\DeliveryServer;
use App\Models\Setting;
use App\Services\CustomerGroupService;
use App\Services\UpdateServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerGroupController extends Controller
{
    public function __construct(
        protected CustomerGroupService $customerGroupService,
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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $customerGroups = $this->customerGroupService->getPaginated($filters);

        return view('admin.customer-groups.index', compact('customerGroups', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $defaultSettings = $this->customerGroupService->getDefaultSettings();
        $hasExtendedLicense = $this->isExtendedLicenseAvailable();
        $allGroups = CustomerGroup::orderBy('name')->get();
        $deliveryServers = DeliveryServer::query()
            ->whereNull('customer_id')
            ->orderBy('name')
            ->get();
        $allocatedDeliveryServerIds = [];
        
        return view('admin.customer-groups.create_v2', compact('defaultSettings', 'hasExtendedLicense', 'allGroups', 'deliveryServers', 'allocatedDeliveryServerIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerGroupStoreRequest $request)
    {
        $customerGroup = $this->customerGroupService->create($request->validated());

        return redirect()
            ->route('admin.customer-groups.index')
            ->with('success', 'Customer group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerGroup $customerGroup)
    {
        $customerGroup->load('customers');
        $settings = $this->customerGroupService->getEffectiveSettings($customerGroup);
        
        return view('admin.customer-groups.show', compact('customerGroup', 'settings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerGroup $customerGroup)
    {
        $settings = $this->customerGroupService->getEffectiveSettings($customerGroup);
        $hasExtendedLicense = $this->isExtendedLicenseAvailable();
        $allGroups = CustomerGroup::where('id', '!=', $customerGroup->id)->orderBy('name')->get();
        $deliveryServers = DeliveryServer::query()
            ->whereNull('customer_id')
            ->orderBy('name')
            ->get();
        $allocatedDeliveryServerIds = $customerGroup->allocatedDeliveryServers()
            ->pluck('delivery_servers.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        
        return view('admin.customer-groups.edit', compact('customerGroup', 'settings', 'hasExtendedLicense', 'allGroups', 'deliveryServers', 'allocatedDeliveryServerIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerGroupUpdateRequest $request, CustomerGroup $customerGroup)
    {
        $this->customerGroupService->update($customerGroup, $request->validated());

        return redirect()
            ->route('admin.customer-groups.index')
            ->with('success', 'Customer group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerGroup $customerGroup)
    {
        try {
            $this->customerGroupService->delete($customerGroup);

            return redirect()
                ->route('admin.customer-groups.index')
                ->with('success', 'Customer group deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.customer-groups.index')
                ->with('error', $e->getMessage());
        }
    }
}

