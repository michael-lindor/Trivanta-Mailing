<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\WordPressPluginBrandingService;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct(
        private readonly WordPressPluginBrandingService $wordpressPluginBrandingService
    ) {
    }

    private function saveIntegrationApiKey(Request $request, string $settingKey, string $tab)
    {
        $validated = $request->validate([
            'settings.api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = data_get($validated, 'settings.api_key');
        $apiKey = is_string($apiKey) ? trim($apiKey) : '';

        if ($apiKey !== '' && $apiKey !== '********') {
            Setting::set($settingKey, $apiKey, 'integrations', 'string');
        }

        return redirect()
            ->route('admin.integrations.index', ['tab' => $tab])
            ->with('success', __('Integration settings updated successfully.'));
    }

    private function revealIntegrationApiKey(string $settingKey)
    {
        $value = Setting::get($settingKey);
        $value = is_string($value) ? trim($value) : '';

        return response()->json([
            'success' => true,
            'value' => $value,
        ]);
    }

    public function index(Request $request)
    {
        $tab = (string) $request->query('tab', 'google');
        if (!in_array($tab, ['google', 'wordpress'], true)) {
            $tab = 'google';
        }

        $googleSocialiteAvailable = class_exists('Laravel\\Socialite\\Facades\\Socialite');

        $clientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');

        $dbClientId = Setting::get('google_client_id');
        $dbClientSecret = Setting::get('google_client_secret');

        if (is_string($dbClientId)) {
            $dbClientId = trim($dbClientId);
        }
        if (is_string($dbClientSecret)) {
            $dbClientSecret = trim($dbClientSecret);
        }

        $googleClientId = (is_string($dbClientId) && $dbClientId !== '') ? $dbClientId : $clientId;
        $googleClientSecret = (is_string($dbClientSecret) && $dbClientSecret !== '') ? $dbClientSecret : $clientSecret;

        $googleOAuthConfigured = (bool) ($googleSocialiteAvailable && $googleClientId && $googleClientSecret);

        $googleRedirectSheets = route('customer.integrations.google.callback', ['service' => 'sheets']);
        $googleRedirectDrive = route('customer.integrations.google.callback', ['service' => 'drive']);
        $wordpressApiKey = Setting::get('integration_wordpress_api_key');
        $wordpressApiKey = is_string($wordpressApiKey) && trim($wordpressApiKey) !== '' ? '********' : '';
        $wordpressBranding = $this->wordpressPluginBrandingService->settings();
        $wordpressCopy = $this->wordpressPluginBrandingService->visibleCopy();

        return view('admin.integrations.index', compact(
            'tab',
            'googleSocialiteAvailable',
            'googleOAuthConfigured',
            'googleClientId',
            'googleClientSecret',
            'googleRedirectSheets',
            'googleRedirectDrive',
            'wordpressApiKey',
            'wordpressBranding',
            'wordpressCopy'
        ));
    }

    public function storeGoogle(Request $request)
    {
        return $this->saveIntegrationApiKey($request, 'integration_google_api_key', 'google');
    }

    public function revealGoogleSecret(Request $request)
    {
        $field = (string) $request->query('field', '');
        if ($field !== 'api_key') {
            abort(404);
        }

        return $this->revealIntegrationApiKey('integration_google_api_key');
    }

    public function storeWordpress(Request $request)
    {
        $validated = $request->validate([
            'settings.api_key' => ['nullable', 'string', 'max:255'],
            'wordpress_plugin.white_label_enabled' => ['nullable', 'boolean'],
            'wordpress_plugin.plugin_name' => ['nullable', 'string', 'max:255'],
            'wordpress_plugin.plugin_slug' => ['nullable', 'string', 'max:255'],
            'wordpress_plugin.plugin_author' => ['nullable', 'string', 'max:255'],
            'wordpress_plugin.plugin_description' => ['nullable', 'string', 'max:500'],
            'wordpress_plugin.plugin_menu_label' => ['nullable', 'string', 'max:255'],
            'wordpress_plugin.plugin_settings_title' => ['nullable', 'string', 'max:255'],
            'wordpress_plugin.app_label' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = data_get($validated, 'settings.api_key');
        $apiKey = is_string($apiKey) ? trim($apiKey) : '';

        if ($apiKey !== '' && $apiKey !== '********') {
            Setting::set('integration_wordpress_api_key', $apiKey, 'integrations', 'string');
        }

        $this->wordpressPluginBrandingService->save([
            'white_label_enabled' => (bool) data_get($validated, 'wordpress_plugin.white_label_enabled', false),
            'plugin_name' => data_get($validated, 'wordpress_plugin.plugin_name'),
            'plugin_slug' => data_get($validated, 'wordpress_plugin.plugin_slug'),
            'plugin_author' => data_get($validated, 'wordpress_plugin.plugin_author'),
            'plugin_description' => data_get($validated, 'wordpress_plugin.plugin_description'),
            'plugin_menu_label' => data_get($validated, 'wordpress_plugin.plugin_menu_label'),
            'plugin_settings_title' => data_get($validated, 'wordpress_plugin.plugin_settings_title'),
            'app_label' => data_get($validated, 'wordpress_plugin.app_label'),
        ]);

        return redirect()
            ->route('admin.integrations.index', ['tab' => 'wordpress'])
            ->with('success', __('Integration settings updated successfully.'));
    }

    public function revealWordpressSecret(Request $request)
    {
        $field = (string) $request->query('field', '');
        if ($field !== 'api_key') {
            abort(404);
        }

        return $this->revealIntegrationApiKey('integration_wordpress_api_key');
    }

    public function downloadWordpressPlugin(Request $request)
    {
        $package = $this->wordpressPluginBrandingService->packagePlugin();

        return response()->download($package['path'], $package['download_name'])->deleteFileAfterSend(true);
    }
}
