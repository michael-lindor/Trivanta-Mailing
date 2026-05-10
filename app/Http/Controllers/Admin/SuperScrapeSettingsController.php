<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperScrapeSettingsController extends Controller
{
    public function edit(): View
    {
        $serpApiKey     = Setting::get('superscrape_serpapi_key', '');
        $serperKey      = Setting::get('superscrape_serper_key', '');
        $monthlyCredits = (int) Setting::get('superscrape_monthly_credits', 500);

        return view('admin.addons.super-scrape-settings', compact('serpApiKey', 'serperKey', 'monthlyCredits'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'superscrape_serpapi_key'     => ['nullable', 'string', 'max:500'],
            'superscrape_serper_key'      => ['nullable', 'string', 'max:500'],
            'superscrape_monthly_credits' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        Setting::set('superscrape_serpapi_key', trim((string) ($validated['superscrape_serpapi_key'] ?? '')), 'addon', 'string');
        Setting::set('superscrape_serper_key', trim((string) ($validated['superscrape_serper_key'] ?? '')), 'addon', 'string');
        Setting::set('superscrape_monthly_credits', (string) (int) $validated['superscrape_monthly_credits'], 'addon', 'string');

        return redirect()->route('admin.super-scrape.settings')
            ->with('success', __('SuperScrape settings saved.'));
    }
}
