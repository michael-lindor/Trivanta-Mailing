<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\RunScraperJob;
use App\Models\Addon;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\ScraperJob;
use App\Models\ScraperLead;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ScraperController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (!Addon::isActive('super-scrape')) {
            return redirect()->route('customer.dashboard')
                ->with('error', __('The SuperScrape addon is not active.'));
        }

        $customerId = (int) auth('customer')->id();

        $totalLeads = ScraperLead::where('customer_id', $customerId)->count();

        $activeJobs = ScraperJob::where('customer_id', $customerId)
            ->whereIn('status', ['pending', 'running'])
            ->count();

        $topSource = ScraperLead::where('customer_id', $customerId)
            ->selectRaw('source_type, COUNT(*) as cnt')
            ->groupBy('source_type')
            ->orderByDesc('cnt')
            ->first();

        $topSourceLabel   = $topSource ? ucfirst($topSource->source_type) : 'Maps';
        $topSourcePercent = ($totalLeads > 0 && $topSource)
            ? (int) round(($topSource->cnt / $totalLeads) * 100)
            : 0;

        $recentJobs = ScraperJob::where('customer_id', $customerId)
            ->latest()
            ->limit(10)
            ->get();

        $creditsUsedThisMonth = ScraperJob::where('customer_id', $customerId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('credits_used');

        $monthlyCredits = $this->monthlyCreditsLimit();
        $creditsLeft    = max(0, $monthlyCredits - (int) $creditsUsedThisMonth);

        $serpApiConfigured  = (bool) trim((string) Setting::get('superscrape_serpapi_key', ''));
        $serperConfigured   = (bool) trim((string) Setting::get('superscrape_serper_key', ''));

        return view('customer.scraper.index', compact(
            'totalLeads',
            'activeJobs',
            'topSourceLabel',
            'topSourcePercent',
            'recentJobs',
            'creditsLeft',
            'monthlyCredits',
            'serpApiConfigured',
            'serperConfigured'
        ));
    }

    public function start(Request $request): RedirectResponse
    {
        if (!Addon::isActive('super-scrape')) {
            return redirect()->route('customer.dashboard')
                ->with('error', __('The SuperScrape addon is not active.'));
        }

        $validated = $request->validate([
            'type'           => ['required', 'in:maps,places,reviews,news,images'],
            'query'          => ['required', 'string', 'max:255'],
            'location'       => ['nullable', 'string', 'max:255'],
            'language'       => ['nullable', 'string', 'max:10'],
            'max_results'    => ['nullable', 'integer', 'min:1', 'max:500'],
            'extract_emails' => ['nullable', 'boolean'],
        ]);

        $type = $validated['type'];

        if (in_array($type, ['maps', 'places', 'reviews', 'images'], true)) {
            $serpApiKey = trim((string) Setting::get('superscrape_serpapi_key', ''));
            if ($serpApiKey === '') {
                return back()->with('error', __('SerpAPI key is not configured. Please contact the administrator.'));
            }
        }

        if ($type === 'news') {
            $serperKey = trim((string) Setting::get('superscrape_serper_key', ''));
            if ($serperKey === '') {
                return back()->with('error', __('Serper.dev key is not configured. Please contact the administrator.'));
            }
        }

        $customerId           = (int) auth('customer')->id();
        $creditsUsedThisMonth = (int) ScraperJob::where('customer_id', $customerId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('credits_used');

        $monthlyCredits = $this->monthlyCreditsLimit();
        $maxResults     = (int) ($validated['max_results'] ?? 20);
        $estimatedCost  = (int) ceil($maxResults / 10);

        if (($creditsUsedThisMonth + $estimatedCost) > $monthlyCredits) {
            return back()->with('error', __('Insufficient scraping credits for this month.'));
        }

        $job = ScraperJob::create([
            'customer_id'    => $customerId,
            'type'           => $type,
            'query'          => $validated['query'],
            'location'       => $validated['location'] ?? null,
            'language'       => $validated['language'] ?? 'en',
            'max_results'    => $maxResults,
            'extract_emails' => (bool) ($validated['extract_emails'] ?? false),
            'status'         => 'pending',
        ]);

        RunScraperJob::dispatch($job->id);

        return redirect()->route('customer.scraper.index')
            ->with('success', __('Scraping job started for ":query". Results will appear shortly.', ['query' => $job->query]));
    }

    public function jobs(Request $request): View|RedirectResponse
    {
        if (!Addon::isActive('super-scrape')) {
            return redirect()->route('customer.dashboard')
                ->with('error', __('The SuperScrape addon is not active.'));
        }

        $customerId = (int) auth('customer')->id();

        $query = ScraperJob::where('customer_id', $customerId)->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $jobs = $query->paginate(20)->withQueryString();

        return view('customer.scraper.jobs', compact('jobs'));
    }

    public function results(ScraperJob $job): View|RedirectResponse
    {
        if (!Addon::isActive('super-scrape')) {
            return redirect()->route('customer.dashboard')
                ->with('error', __('The SuperScrape addon is not active.'));
        }

        $customerId = (int) auth('customer')->id();

        if ($job->customer_id !== $customerId) {
            abort(403);
        }

        $leads = ScraperLead::where('job_id', $job->id)
            ->paginate(50);

        $emailLists = EmailList::where('customer_id', $customerId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('customer.scraper.results', compact('job', 'leads', 'emailLists'));
    }

    public function diagnosis(ScraperJob $job): View|RedirectResponse
    {
        if (!Addon::isActive('super-scrape')) {
            return redirect()->route('customer.dashboard')
                ->with('error', __('The SuperScrape addon is not active.'));
        }

        $customerId = (int) auth('customer')->id();

        if ($job->customer_id !== $customerId) {
            abort(403);
        }

        return view('customer.scraper.diagnosis', compact('job'));
    }

    public function exportCsv(ScraperJob $job): StreamedResponse|RedirectResponse
    {
        $customerId = (int) auth('customer')->id();

        if ($job->customer_id !== $customerId) {
            abort(403);
        }

        $leads = ScraperLead::where('job_id', $job->id)->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="scraper-leads-' . $job->id . '.csv"',
        ];

        $callback = function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Website', 'Address', 'Rating', 'Reviews', 'Category', 'Source']);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->name ?? '',
                    $lead->email ?? '',
                    $lead->phone ?? '',
                    $lead->website ?? '',
                    $lead->address ?? '',
                    $lead->rating ?? '',
                    $lead->reviews_count ?? '',
                    $lead->category ?? '',
                    $lead->source_type,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function pushToList(Request $request, ScraperJob $job): RedirectResponse
    {
        $customerId = (int) auth('customer')->id();

        if ($job->customer_id !== $customerId) {
            abort(403);
        }

        $validated = $request->validate([
            'email_list_id' => ['required', 'integer', 'exists:email_lists,id'],
        ]);

        $list = EmailList::where('id', $validated['email_list_id'])
            ->where('customer_id', $customerId)
            ->firstOrFail();

        $leads = ScraperLead::where('job_id', $job->id)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $pushed = 0;
        foreach ($leads as $lead) {
            $existing = ListSubscriber::where('list_id', $list->id)
                ->where('email', $lead->email)
                ->exists();

            if ($existing) {
                continue;
            }

            ListSubscriber::create([
                'list_id'    => $list->id,
                'email'      => $lead->email,
                'first_name' => $lead->name ? explode(' ', trim($lead->name))[0] : null,
                'last_name'  => $lead->name ? (explode(' ', trim($lead->name))[1] ?? null) : null,
                'status'     => 'subscribed',
                'source'     => 'scraper',
                'ip_address' => request()->ip(),
            ]);

            $pushed++;
        }

        return redirect()->route('customer.scraper.results', $job)
            ->with('success', __(':count leads pushed to ":list" successfully.', [
                'count' => $pushed,
                'list'  => $list->name,
            ]));
    }

    public function deleteJob(ScraperJob $job): RedirectResponse
    {
        $customerId = (int) auth('customer')->id();

        if ($job->customer_id !== $customerId) {
            abort(403);
        }

        ScraperLead::where('job_id', $job->id)->delete();
        $job->delete();

        return redirect()->route('customer.scraper.jobs')
            ->with('success', __('Scraping job deleted.'));
    }

    public function status(ScraperJob $job): JsonResponse
    {
        $customerId = (int) auth('customer')->id();

        if ($job->customer_id !== $customerId) {
            abort(403);
        }

        return response()->json([
            'status'        => $job->status,
            'records_count' => $job->records_count,
            'credits_used'  => $job->credits_used,
            'completed_at'  => $job->completed_at?->toISOString(),
        ]);
    }

    public function settings(): View|RedirectResponse
    {
        if (!Addon::isActive('super-scrape')) {
            return redirect()->route('customer.dashboard')
                ->with('error', __('The SuperScrape addon is not active.'));
        }

        $serpApiConfigured = (bool) trim((string) Setting::get('superscrape_serpapi_key', ''));
        $serperConfigured  = (bool) trim((string) Setting::get('superscrape_serper_key', ''));

        return view('customer.scraper.settings', compact('serpApiConfigured', 'serperConfigured'));
    }

    private function monthlyCreditsLimit(): int
    {
        try {
            $limit = (int) Setting::get('superscrape_monthly_credits', 500);
        } catch (\Throwable $e) {
            $limit = 500;
        }

        return max(1, $limit);
    }
}
