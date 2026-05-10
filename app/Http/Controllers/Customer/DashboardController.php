<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CampaignTracking;
use App\Models\ListSubscriber;
use App\Services\Billing\UsageService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(private readonly UsageService $usageService)
    {
    }

    /**
     * Display the customer dashboard.
     */
    public function index()
    {
        $customer = auth()->guard('customer')->user();

        $customerTimezone = $customer->timezone ?: config('app.timezone');
        $customerLocalTime = now()->setTimezone($customerTimezone);

        $cacheKey = 'customer_dashboard_v2:' . (int) $customer->id;

        $cached = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($customer) {
            $listIds = $customer->emailLists()->pluck('id');

            // --- Basic counts ---
            $emailListsCount = $listIds->count();
            $subscribersCount = $listIds->isEmpty()
                ? 0
                : ListSubscriber::whereIn('list_id', $listIds)->count();
            $campaignsCount = $customer->campaigns()->count();

            // --- Subscriber breakdown by status ---
            $subscriberBreakdown = $listIds->isEmpty() ? [] :
                ListSubscriber::whereIn('list_id', $listIds)
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

            $subscribedCount   = (int) ($subscriberBreakdown['confirmed']    ?? 0);
            $unconfirmedCount  = (int) ($subscriberBreakdown['pending']       ?? 0);
            $blacklistedCount  = (int) ($subscriberBreakdown['blacklisted']   ?? 0);

            // --- Campaign aggregate stats ---
            $allCampaigns = $customer->campaigns();
            $totalSent        = (int) $allCampaigns->sum('sent_count');
            $totalOpened      = (int) $allCampaigns->sum('opened_count');
            $totalClicked     = (int) $allCampaigns->sum('clicked_count');
            $totalBounced     = (int) $allCampaigns->sum('bounced_count');
            $totalUnsubscribed= (int) $allCampaigns->sum('unsubscribed_count');
            $totalDelivered   = max(0, $totalSent - $totalBounced);

            $completedCampaignsQuery = $customer->campaigns()->where('status', 'completed');
            $avgOpenRate      = round((float) ($completedCampaignsQuery->avg('open_rate')  ?? 0), 1);
            $avgClickRate     = round((float) ($completedCampaignsQuery->avg('click_rate') ?? 0), 1);
            $clickToOpenRate  = $totalOpened > 0
                ? round(($totalClicked / $totalOpened) * 100, 1)
                : 0.0;
            $unsubscribeRate  = $totalSent > 0
                ? round(($totalUnsubscribed / $totalSent) * 100, 2)
                : 0.0;
            $bounceRate       = $totalSent > 0
                ? round(($totalBounced / $totalSent) * 100, 2)
                : 0.0;

            // Reputation score: start at 100, penalise bounce & unsubscribe rates
            $senderScore = max(0, min(100, (int) round(100 - ($bounceRate * 5) - ($unsubscribeRate * 10))));

            // --- Active campaigns ---
            $activeCampaignsCount = $customer->campaigns()->where('status', 'running')->count();

            // --- Top campaigns by open rate ---
            $topCampaigns = $customer->campaigns()
                ->where('status', 'completed')
                ->where('open_rate', '>', 0)
                ->orderByDesc('open_rate')
                ->limit(5)
                ->get(['id', 'name', 'open_rate', 'sent_count', 'started_at']);

            // --- Campaign performance table ---
            $performanceCampaigns = $customer->campaigns()
                ->whereIn('status', ['completed', 'running'])
                ->orderByDesc('started_at')
                ->limit(5)
                ->get(['id', 'name', 'status', 'sent_count', 'open_rate', 'click_rate', 'started_at']);

            // --- Engagement over time (last 7 days) ---
            $campaignIds = $customer->campaigns()->pluck('id');
            $engagementRaw = $campaignIds->isEmpty() ? collect() :
                CampaignTracking::whereIn('campaign_id', $campaignIds)
                    ->where('event_at', '>=', now()->subDays(6)->startOfDay())
                    ->whereIn('event_type', ['opened', 'clicked'])
                    ->selectRaw("DATE(event_at) as date, event_type, COUNT(*) as count")
                    ->groupBy('date', 'event_type')
                    ->get();

            $engagementDates  = [];
            $engagementOpens  = [];
            $engagementClicks = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $engagementDates[]  = now()->subDays($i)->format('M j');
                $engagementOpens[]  = (int) $engagementRaw->where('date', $date)->where('event_type', 'opened')->sum('count');
                $engagementClicks[] = (int) $engagementRaw->where('date', $date)->where('event_type', 'clicked')->sum('count');
            }

            return compact(
                'emailListsCount', 'subscribersCount', 'campaignsCount',
                'activeCampaignsCount',
                'subscribedCount', 'unconfirmedCount', 'blacklistedCount',
                'totalSent', 'totalDelivered', 'totalBounced',
                'avgOpenRate', 'avgClickRate', 'clickToOpenRate',
                'unsubscribeRate', 'bounceRate', 'senderScore',
                'topCampaigns', 'performanceCampaigns',
                'engagementDates', 'engagementOpens', 'engagementClicks'
            );
        });

        $usageData  = $this->usageService->getUsage($customer);
        $quotaUsed  = (int) ($usageData['emails_sent_this_month'] ?? 0);
        $quotaLimit = $customer->groupLimit('sending_quota.monthly_quota')
            ?? ((int) ($customer->quota ?? 0) ?: null);

        $hour      = (int) $customerLocalTime->format('H');
        $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $firstName = $customer->first_name;

        $listLimit       = $customer->groupLimit('lists.limits.max_lists');
        $subscriberLimit = $customer->groupLimit('lists.limits.max_subscribers');
        $campaignLimit   = $customer->groupLimit('campaigns.limits.max_campaigns');

        return view('customer.dashboard.index', array_merge($cached, [
            'customerTimezone'  => $customerTimezone,
            'customerLocalTime' => $customerLocalTime,
            'quotaUsed'         => $quotaUsed,
            'quotaLimit'        => $quotaLimit,
            'listLimit'         => $listLimit,
            'subscriberLimit'   => $subscriberLimit,
            'campaignLimit'     => $campaignLimit,
            'greeting'          => $greeting,
            'firstName'         => $firstName,
        ]));
    }
}

