<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Services\UpdateServerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use ZipArchive;

class AddonController extends Controller
{
    protected array $catalog = [
        [
            'slug'        => 'cold-email-outreach',
            'name'        => 'Cold Email Outreach',
            'author'      => 'MailPurse',
            'version'     => '1.0.0',
            'category'    => 'outreach',
            'description' => 'Run cold email outreach campaigns with sequences, A/B testing, reply detection, and scheduling.',
            'icon'        => 'outreach',
            'price'       => '$29',
            'purchase_url' => 'https://codecanyon.net/item/coldmail-cold-email-outreach-addons-for-mailpurse/62976476?s_rank=1'
        ],
        // [
        //     'slug'        => 'super-scrape',  
        //     'name'        => 'SuperScrape',
        //     'author'      => 'MailPurse',
        //     'version'     => '1.0.0',
        //     'category'    => 'lead-generation',
        //     'description' => 'Scrape Google Maps, Places, Reviews, News & Images for lead generation. Export to CSV or push directly to your email lists.',
        //     'icon'        => 'scraper',
        //     'price'       => '$29',
        //     'purchase_url' => '#'
        // ]
    ];

    public function index(): View
    {
        $installed = Addon::latest()->get()->keyBy('slug');
        $updateInfoBySlug = $this->getUpdateInfoBySlug($installed);

        $catalog = collect($this->catalog)->map(function ($item) use ($installed, $updateInfoBySlug) {
            $item['record'] = $installed->get($item['slug']);
            $item['status'] = $item['record']?->status ?? 'available';
            $item['update_info'] = $updateInfoBySlug[$item['slug']] ?? null;
            return $item;
        });

        $addons = $installed->map(function (Addon $addon) use ($updateInfoBySlug) {
            $addon->setAttribute('update_info', $updateInfoBySlug[$addon->slug] ?? null);

            return $addon;
        });

        return view('admin.addons.index', compact('addons', 'catalog'));
    }

    public function remoteInstall(Request $request, UpdateServerService $updateServerService): JsonResponse
    {
        $slug       = (string) $request->input('slug', '');
        $licenseKey = trim((string) $request->input('license_key', ''));

        $catalogItem = collect($this->catalog)->firstWhere('slug', $slug);
        if (!$catalogItem) {
            return response()->json(['error' => __('Addon not found in catalog.')], 404);
        }

        if (Addon::where('slug', $slug)->exists()) {
            return response()->json(['error' => __('Addon ":name" is already installed.', ['name' => $catalogItem['name']])], 409);
        }

        if ($licenseKey === '') {
            return response()->json(['error' => __('Purchase code is required.')], 422);
        }

        $addonConfig = config('services.update_server.addons.' . $slug);
        if (!is_array($addonConfig)) {
            return response()->json(['error' => __('Addon server configuration not found for ":slug".', ['slug' => $slug])], 500);
        }

        ['baseUrl' => $baseUrl, 'productId' => $productId, 'productSecret' => $productSecret, 'productName' => $productName, 'domain' => $domain] = $this->resolveAddonServerConfig($slug);

        // Always activate the license for this addon (ensures server registers domain)
        $licenseActivate = $updateServerService->licenseActivateAddon($licenseKey, $domain, $productId, $productSecret, $productName);
        if (!($licenseActivate['valid'] ?? false)) {
            $message = is_string($licenseActivate['message'] ?? null) && trim((string) $licenseActivate['message']) !== ''
                ? (string) $licenseActivate['message']
                : __('Purchase code is not valid.');

            $raw = $licenseActivate['raw'] ?? null;
            if ($raw !== null) {
                $rawStr = is_string($raw) ? trim($raw) : (is_array($raw) ? trim((string) json_encode($raw)) : '');
                if ($rawStr !== '' && $rawStr !== $message) {
                    $message .= ' — ' . $rawStr;
                }
            }

            return response()->json(['error' => $message], 422);
        }

        // Debug: log activation response to help diagnose 403 on download
        Log::debug('Addon license activate response', [
            'slug'    => $slug,
            'domain'  => $domain,
            'valid'   => $licenseActivate['valid'] ?? null,
            'message' => $licenseActivate['message'] ?? null,
            'status'  => $licenseActivate['status'] ?? null,
            'data'    => $licenseActivate['data'] ?? null,
        ]);

        // Brief pause to let the server persist the activation before we request the download
        usleep(500000);

        $installResult = $this->downloadAndExtractAddonPackage(
            $updateServerService,
            $slug,
            $licenseKey,
            $baseUrl,
            $domain,
            $productId,
            $productSecret,
            $productName
        );

        if (!($installResult['success'] ?? false)) {
            return response()->json(['error' => $installResult['message'] ?? __('Installation failed.')], (int) ($installResult['status'] ?? 422));
        }

        $manifest = $installResult['manifest'];

        Addon::create([
            'slug'         => $slug,
            'name'         => $manifest['name'],
            'author'       => $manifest['author'] ?? $catalogItem['author'],
            'version'      => $manifest['version'] ?? $catalogItem['version'],
            'category'     => $manifest['category'] ?? $catalogItem['category'],
            'description'  => $manifest['description'] ?? $catalogItem['description'],
            'status'       => 'active',
            'license_key'  => $licenseKey,
            'installed_at' => now(),
            'activated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __(':name v:version installed and activated successfully.', [
                'name'    => $manifest['name'],
                'version' => $manifest['version'] ?? $catalogItem['version'],
            ]),
        ]);
    }

    public function installUpdate(Addon $addon, UpdateServerService $updateServerService): RedirectResponse
    {
        Log::info('Addon update started', ['addon_id' => $addon->id, 'slug' => $addon->slug, 'name' => $addon->name]);

        if (!is_string($addon->license_key) || trim($addon->license_key) === '') {
            Log::warning('Addon update failed: no license key', ['addon_id' => $addon->id, 'slug' => $addon->slug]);
            return back()->with('error', __('Purchase code is required before installing updates for :name.', ['name' => $addon->name]));
        }

        try {
            ['baseUrl' => $baseUrl, 'productId' => $productId, 'productSecret' => $productSecret, 'productName' => $productName, 'domain' => $domain] = $this->resolveAddonServerConfig($addon->slug);
        } catch (\Throwable $e) {
            Log::error('Addon update failed: could not resolve server config', ['addon_id' => $addon->id, 'slug' => $addon->slug, 'error' => $e->getMessage()]);
            return back()->with('error', __('Addon server configuration not found for :slug.', ['slug' => $addon->slug]));
        }

        $updateInfo = $this->getAddonUpdateInfo($addon, $updateServerService, $baseUrl, $productId);
        if (!($updateInfo['update_available'] ?? false)) {
            Log::info('Addon update: no update available', ['addon_id' => $addon->id, 'slug' => $addon->slug, 'update_info' => $updateInfo]);
            return back()->with('error', __('No update available for :name.', ['name' => $addon->name]));
        }

        $licenseKey = trim((string) $addon->license_key);

        Log::info('Addon update: activating license', ['addon_id' => $addon->id, 'slug' => $addon->slug]);
        $licenseActivate = $updateServerService->licenseActivateAddon($licenseKey, $domain, $productId, $productSecret, $productName);
        if (!($licenseActivate['valid'] ?? false)) {
            $message = is_string($licenseActivate['message'] ?? null) && trim((string) $licenseActivate['message']) !== ''
                ? (string) $licenseActivate['message']
                : __('Purchase code is not valid.');

            Log::error('Addon update failed: license activation failed', ['addon_id' => $addon->id, 'slug' => $addon->slug, 'message' => $message, 'response' => $licenseActivate]);
            return back()->with('error', $message);
        }

        usleep(500000);

        Log::info('Addon update: downloading package', ['addon_id' => $addon->id, 'slug' => $addon->slug]);
        $installResult = $this->downloadAndExtractAddonPackage(
            $updateServerService,
            $addon->slug,
            $licenseKey,
            $baseUrl,
            $domain,
            $productId,
            $productSecret,
            $productName
        );

        if (!($installResult['success'] ?? false)) {
            Log::error('Addon update failed: download/extract failed', ['addon_id' => $addon->id, 'slug' => $addon->slug, 'message' => $installResult['message'] ?? null]);
            return back()->with('error', $installResult['message'] ?? __('Failed to install addon update.'));
        }

        $manifest = $installResult['manifest'];
        $extra = array_diff_key($manifest, array_flip([
            'slug', 'name', 'author', 'version', 'category', 'description',
        ]));

        Log::info('Addon update: updating database', ['addon_id' => $addon->id, 'slug' => $addon->slug, 'new_version' => $manifest['version'] ?? null]);
        $addon->update([
            'name'         => $manifest['name'] ?? $addon->name,
            'author'       => $manifest['author'] ?? $addon->author,
            'version'      => $manifest['version'] ?? $addon->version,
            'category'     => $manifest['category'] ?? $addon->category,
            'description'  => $manifest['description'] ?? $addon->description,
            'meta'         => !empty($extra) ? $extra : $addon->meta,
            'installed_at' => now(),
            'activated_at' => $addon->status === 'active' ? now() : $addon->activated_at,
        ]);

        Log::info('Addon update completed successfully', ['addon_id' => $addon->id, 'slug' => $addon->slug, 'new_version' => $manifest['version'] ?? null]);
        return back()->with('success', __(':name updated to v:version successfully.', [
            'name' => $addon->name,
            'version' => $manifest['version'] ?? $addon->version,
        ]));
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'addon_zip' => 'required|file|mimes:zip|max:102400',
        ], [
            'addon_zip.required' => __('Please select a zip file to upload.'),
            'addon_zip.mimes'    => __('The file must be a valid .zip archive.'),
            'addon_zip.max'      => __('The zip file must not exceed 100MB.'),
        ]);

        $zip     = new ZipArchive();
        $tmpPath = $request->file('addon_zip')->getPathname();

        if ($zip->open($tmpPath) !== true) {
            return back()->with('error', __('Could not open zip file. Please verify it is a valid archive.'));
        }

        $manifestContent = null;
        $rootDir         = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === 'addon.json') {
                $manifestContent = $zip->getFromIndex($i);
                $rootDir         = (dirname($name) === '.') ? '' : explode('/', $name)[0];
                break;
            }
        }

        if ($manifestContent === null) {
            $zip->close();
            return back()->with('error', __('Invalid addon package: addon.json manifest not found inside the zip.'));
        }

        $manifest = json_decode($manifestContent, true);

        if (!is_array($manifest) || empty($manifest['slug']) || empty($manifest['name'])) {
            $zip->close();
            return back()->with('error', __('Invalid addon.json: required fields "slug" and "name" are missing.'));
        }

        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower((string) $manifest['slug']));

        if ($slug === '') {
            $zip->close();
            return back()->with('error', __('Invalid addon slug in addon.json.'));
        }

        if (Addon::where('slug', $slug)->exists()) {
            $zip->close();
            return back()->with('error', __('Addon ":name" is already installed. Uninstall it first before re-uploading.', ['name' => $manifest['name']]));
        }

        $addonPath = storage_path("app/addons/{$slug}");

        if (!is_dir($addonPath)) {
            mkdir($addonPath, 0755, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            $relative = ($rootDir !== '' && str_starts_with($name, $rootDir . '/'))
                ? substr($name, strlen($rootDir) + 1)
                : $name;

            if ($relative === '' || str_contains($relative, '..')) {
                continue;
            }

            $target = $addonPath . '/' . $relative;

            if (str_ends_with($name, '/')) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
                continue;
            }

            $dir = dirname($target);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($target, $zip->getFromIndex($i));
        }

        $zip->close();

        $extra = array_diff_key($manifest, array_flip([
            'slug', 'name', 'author', 'version', 'category', 'description',
        ]));

        Addon::create([
            'slug'         => $slug,
            'name'         => $manifest['name'],
            'author'       => $manifest['author'] ?? 'Unknown',
            'version'      => $manifest['version'] ?? '1.0.0',
            'category'     => $manifest['category'] ?? 'general',
            'description'  => $manifest['description'] ?? '',
            'status'       => 'installed',
            'meta'         => !empty($extra) ? $extra : null,
            'installed_at' => now(),
        ]);

        return back()->with('success', __(':name v:version installed successfully. Enter your purchase code to activate it.', [
            'name'    => $manifest['name'],
            'version' => $manifest['version'] ?? '1.0.0',
        ]));
    }

    public function activate(Request $request, Addon $addon): RedirectResponse
    {
        $licenseKey = trim((string) $request->input('license_key', ''));

        if ($licenseKey === '') {
            return back()->with('error', __('Purchase code is required to activate the addon.'));
        }

        $addon->update([
            'status'       => 'active',
            'license_key'  => $licenseKey,
            'activated_at' => now(),
        ]);

        return back()->with('success', __(':name has been activated successfully.', ['name' => $addon->name]));
    }

    public function deactivate(Addon $addon): RedirectResponse
    {
        $addon->update([
            'status'       => 'installed',
            'activated_at' => null,
        ]);

        return back()->with('success', __(':name has been deactivated.', ['name' => $addon->name]));
    }

    public function uninstall(Addon $addon): RedirectResponse
    {
        $name = $addon->name;
        $slug = $addon->slug;

        $addonPath = storage_path("app/addons/{$slug}");
        if (is_dir($addonPath)) {
            $this->deleteDirectory($addonPath);
        }

        $addon->delete();

        return back()->with('success', __(':name has been uninstalled and its files removed.', ['name' => $name]));
    }

    private function resolveAddonServerConfig(string $slug): array
    {
        $addonConfig = config('services.update_server.addons.' . $slug);

        if (!is_array($addonConfig)) {
            abort(500, __('Addon server configuration not found for ":slug".', ['slug' => $slug]));
        }

        $baseUrl       = (string) config('services.update_server.base_url');
        $productId     = (int) ($addonConfig['product_id'] ?? 0);
        $productSecret = (string) ($addonConfig['product_secret'] ?? '');
        $productName   = (string) ($addonConfig['product_name'] ?? $slug);

        $appUrl = (string) config('app.url');
        $parsed = parse_url($appUrl);
        $domain = is_array($parsed) && is_string($parsed['host'] ?? null)
            ? (string) $parsed['host']
            : $appUrl;

        return compact('baseUrl', 'productId', 'productSecret', 'productName', 'domain');
    }

    private function getUpdateInfoBySlug($installed): array
    {
        $service = app(UpdateServerService::class);
        $baseUrl = (string) config('services.update_server.base_url');
        $data = [];

        foreach ($installed as $slug => $addon) {
            $addonConfig = config('services.update_server.addons.' . $slug);
            if (!is_array($addonConfig)) {
                continue;
            }

            $productId = (int) ($addonConfig['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $data[$slug] = $this->getAddonUpdateInfo($addon, $service, $baseUrl, $productId);
        }

        return $data;
    }

    private function getAddonUpdateInfo(Addon $addon, UpdateServerService $updateServerService, string $baseUrl, int $productId): ?array
    {
        $productResult = $updateServerService->getProduct($productId, $baseUrl);
        $changelogResult = $updateServerService->getChangelogs($productId, $baseUrl);

        Log::debug('Addon update: raw API responses', [
            'addon_id' => $addon->id,
            'slug' => $addon->slug,
            'product_id' => $productId,
            'product_result' => $productResult,
            'changelog_result' => $changelogResult,
        ]);

        $latestVersion = $this->extractLatestVersion($productResult, $changelogResult);

        if (!is_string($latestVersion) || trim($latestVersion) === '') {
            Log::debug('Addon update: could not determine latest version', [
                'addon_id' => $addon->id,
                'slug' => $addon->slug,
                'product_result' => $productResult,
                'changelog_result' => $changelogResult,
            ]);
            return null;
        }

        $installedVersion = is_string($addon->version) ? trim($addon->version) : '';
        if ($installedVersion === '') {
            return null;
        }

        $updateAvailable = version_compare($latestVersion, $installedVersion, '>');

        Log::debug('Addon update check result', [
            'addon_id' => $addon->id,
            'slug' => $addon->slug,
            'installed_version' => $installedVersion,
            'latest_version' => $latestVersion,
            'update_available' => $updateAvailable,
        ]);

        return [
            'installed_version' => $installedVersion,
            'latest_version' => $latestVersion,
            'update_available' => $updateAvailable,
        ];
    }

    private function extractLatestVersion(?array $productResult, ?array $changelogResult): ?string
    {
        $latest = null;

        $payload = is_array($productResult) ? ($productResult['data'] ?? null) : null;
        if (is_array($payload)) {
            $productData = null;
            if (is_array($payload['data'] ?? null)) {
                $productData = $payload['data'];
            } elseif (is_array($payload['product'] ?? null)) {
                $productData = $payload['product'];
            } else {
                $productData = $payload;
            }

            if (is_array($productData)) {
                $latest = $productData['latest_version'] ?? ($productData['latestVersion'] ?? ($productData['latest'] ?? null));
                if (!is_string($latest) || trim((string) $latest) === '') {
                    $latest = $productData['version'] ?? null;
                }
            }
        }

        if (is_string($latest) && trim((string) $latest) !== '') {
            return trim((string) $latest);
        }

        $changelogData = is_array($changelogResult) ? ($changelogResult['data'] ?? null) : null;
        if (!is_array($changelogData)) {
            return null;
        }

        $versionMap = null;
        if (is_array($changelogData['releases'] ?? null)) {
            $versionMap = $changelogData['releases'];
        } elseif (is_array($changelogData['changelog'] ?? null)) {
            $versionMap = $changelogData['changelog'];
        }

        if (!is_array($versionMap) || empty($versionMap)) {
            return null;
        }

        $versions = array_keys($versionMap);
        usort($versions, function ($a, $b) {
            return version_compare((string) $b, (string) $a);
        });

        $top = $versions[0] ?? null;
        return is_string($top) && trim($top) !== '' ? trim($top) : null;
    }

    private function downloadAndExtractAddonPackage(
        UpdateServerService $updateServerService,
        string $slug,
        string $licenseKey,
        string $baseUrl,
        string $domain,
        int $productId,
        string $productSecret,
        string $productName
    ): array {
        $downloadResult = $updateServerService->requestAddonDownloadUrl($baseUrl, $licenseKey, $domain, $productId, $productSecret, $productName);
        if (!($downloadResult['success'] ?? false)) {
            return [
                'success' => false,
                'message' => $this->formatDownloadError($downloadResult),
                'status' => 502,
            ];
        }

        $downloadUrl = is_string($downloadResult['download_url'] ?? null) ? trim((string) $downloadResult['download_url']) : '';
        if ($downloadUrl === '') {
            return [
                'success' => false,
                'message' => __('Download URL not available.'),
                'status' => 502,
            ];
        }

        try {
            $response = Http::timeout(120)->get($downloadUrl);
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => __('Download failed (HTTP :status).', ['status' => $response->status()]),
                    'status' => 502,
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => __('Download error: ') . $e->getMessage(),
                'status' => 502,
            ];
        }

        $tmpPath = sys_get_temp_dir() . '/' . $slug . '_' . time() . '.zip';
        file_put_contents($tmpPath, $response->body());

        $zip = new ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            @unlink($tmpPath);

            return [
                'success' => false,
                'message' => __('Downloaded file is not a valid zip archive.'),
                'status' => 422,
            ];
        }

        $manifestContent = null;
        $rootDir = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === 'addon.json') {
                $manifestContent = $zip->getFromIndex($i);
                $rootDir = (dirname($name) === '.') ? '' : explode('/', $name)[0];
                break;
            }
        }

        if ($manifestContent === null) {
            $zip->close();
            @unlink($tmpPath);

            return [
                'success' => false,
                'message' => __('Invalid addon package: addon.json not found.'),
                'status' => 422,
            ];
        }

        $manifest = json_decode($manifestContent, true);
        if (!is_array($manifest) || empty($manifest['slug']) || empty($manifest['name'])) {
            $zip->close();
            @unlink($tmpPath);

            return [
                'success' => false,
                'message' => __('Invalid addon.json manifest.'),
                'status' => 422,
            ];
        }

        $addonPath = storage_path("app/addons/{$slug}");
        if (is_dir($addonPath)) {
            $this->deleteDirectory($addonPath);
        }

        mkdir($addonPath, 0755, true);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $relative = ($rootDir !== '' && str_starts_with($name, $rootDir . '/'))
                ? substr($name, strlen($rootDir) + 1)
                : $name;

            if ($relative === '' || str_contains($relative, '..')) {
                continue;
            }

            $target = $addonPath . '/' . $relative;

            if (str_ends_with($name, '/')) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }

                continue;
            }

            $dir = dirname($target);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($target, $zip->getFromIndex($i));
        }

        $zip->close();
        @unlink($tmpPath);

        return [
            'success' => true,
            'manifest' => $manifest,
        ];
    }

    private function formatDownloadError(array $downloadResult): string
    {
        $msg = is_string($downloadResult['message'] ?? null) ? trim((string) $downloadResult['message']) : '';
        if ($msg === '') {
            $msg = __('Failed to get download URL from server.');
        }

        if (is_numeric($downloadResult['status'] ?? null)) {
            $msg .= ' (HTTP ' . (int) $downloadResult['status'] . ')';
        }

        $raw = $downloadResult['raw'] ?? null;
        if ($raw !== null) {
            $rawStr = is_string($raw) ? trim($raw) : (is_array($raw) ? trim((string) json_encode($raw)) : '');
            if ($rawStr !== '') {
                $msg .= ': ' . $rawStr;
            }
        }

        return $msg;
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
