<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GoogleIntegration;
use App\Services\WordPressPluginBrandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class IntegrationController extends Controller
{
    public function __construct(
        private readonly WordPressPluginBrandingService $wordpressPluginBrandingService
    ) {
        $this->middleware('customer.access:servers.permissions.can_access_delivery_servers')->only(['index']);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $canAccessGoogle = $customer ? (bool) $customer->groupAllows('integrations.permissions.can_access_google') : false;

        $allowedTabs = ['wordpress'];
        if ($canAccessGoogle) {
            $allowedTabs[] = 'google';
        }

        $tab = (string) $request->query('tab', $canAccessGoogle ? 'google' : 'wordpress');
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = $canAccessGoogle ? 'google' : 'wordpress';
        }

        $googleIntegrations = collect();
        if ($customer && $canAccessGoogle && Schema::hasTable('google_integrations')) {
            $googleIntegrations = GoogleIntegration::query()
                ->where('customer_id', $customer->id)
                ->whereIn('service', ['sheets', 'drive'])
                ->get()
                ->keyBy('service');
        }

        $wordpressCopy = $this->wordpressPluginBrandingService->visibleCopy();

        return view('customer.integrations.index', compact('tab', 'canAccessGoogle', 'googleIntegrations', 'wordpressCopy'));
    }

    public function downloadWordpressPlugin(Request $request)
    {
        $package = $this->wordpressPluginBrandingService->packagePlugin();

        return response()->download($package['path'], $package['download_name'])->deleteFileAfterSend(true);
    }
}
