<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailList;
use Illuminate\Http\Request;

class ListAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:lists.permissions.can_access_lists');
    }

    public function index(Request $request, EmailList $list)
    {
        $customer = $request->user('customer');
        if ((int) $list->customer_id !== (int) $customer->id) {
            abort(404);
        }

        $period = (string) $request->input('period', '30');
        $days = in_array((int) $period, [7, 30, 90], true) ? (int) $period : 30;

        $periodStart = now()->subDays($days)->startOfDay();
        $prevStart   = now()->subDays($days * 2)->startOfDay();

        // ── Stat: Total Audience ──────────────────────────────────────────
        $confirmedCount  = (int) ($list->confirmed_subscribers_count ?? 0);
        $newSubsCurrent  = $list->subscribers()->where('subscribed_at', '>=', $periodStart)->count();
        $newSubsPrev     = $list->subscribers()->whereBetween('subscribed_at', [$prevStart, $periodStart])->count();
        $audienceDelta   = $this->computeDelta($newSubsCurrent, $newSubsPrev);

        // ── Stat: Open / Click Rates ──────────────────────────────────────
        [$currentOpen, $currentClick] = $this->periodRates((int) $list->id, (int) $customer->id, $periodStart, now());
        [$prevOpen, $prevClick]       = $this->periodRates((int) $list->id, (int) $customer->id, $prevStart, $periodStart);
        $openRateDelta  = round($currentOpen - $prevOpen, 1);
        $clickRateDelta = round($currentClick - $prevClick, 1);

        // ── Stat: Unsubscribes ────────────────────────────────────────────
        $unsubsCurrent = $list->subscribers()->where('status', 'unsubscribed')->where('updated_at', '>=', $periodStart)->count();
        $unsubsPrev    = $list->subscribers()->where('status', 'unsubscribed')->whereBetween('updated_at', [$prevStart, $periodStart])->count();
        $unsubsDelta   = $this->computeDelta($unsubsCurrent, $unsubsPrev);

        // ── Audience growth (last 6 weeks) ────────────────────────────────
        $weeklyGrowth = [];
        for ($w = 5; $w >= 0; $w--) {
            $wStart = now()->startOfWeek()->subWeeks($w);
            $wEnd   = (clone $wStart)->endOfWeek();
            $weeklyGrowth[] = [
                'label' => 'Week ' . (6 - $w),
                'value' => $list->subscribers()->whereBetween('subscribed_at', [$wStart, $wEnd])->count(),
            ];
        }

        // ── Engagement trends (last 5 weeks) ─────────────────────────────
        $weeklyEngagement = [];
        for ($w = 4; $w >= 0; $w--) {
            $wStart = now()->startOfWeek()->subWeeks($w);
            $wEnd   = (clone $wStart)->endOfWeek();
            $wCampaigns = Campaign::where('customer_id', $customer->id)
                ->where('list_id', $list->id)
                ->whereBetween('created_at', [$wStart, $wEnd])
                ->where('sent_count', '>', 0)
                ->get(['sent_count', 'opened_count', 'clicked_count']);
            $ws = $wCampaigns->sum('sent_count');
            $weeklyEngagement[] = [
                'label'  => 'Week ' . (5 - $w),
                'opens'  => $ws > 0 ? round($wCampaigns->sum('opened_count') / $ws * 100, 1) : 0,
                'clicks' => $ws > 0 ? round($wCampaigns->sum('clicked_count') / $ws * 100, 1) : 0,
            ];
        }

        // ── Top segments ──────────────────────────────────────────────────
        $topSegments = $list->segments()->orderByDesc('subscribers_count')->limit(4)->get();

        return view('customer.lists.analytics.index', compact(
            'list', 'period', 'days',
            'confirmedCount', 'audienceDelta',
            'currentOpen', 'openRateDelta',
            'currentClick', 'clickRateDelta',
            'unsubsCurrent', 'unsubsDelta',
            'weeklyGrowth', 'weeklyEngagement',
            'topSegments'
        ));
    }

    private function periodRates(int $listId, int $customerId, $from, $to): array
    {
        $campaigns = Campaign::where('customer_id', $customerId)
            ->where('list_id', $listId)
            ->whereBetween('created_at', [$from, $to])
            ->where('sent_count', '>', 0)
            ->get(['sent_count', 'opened_count', 'clicked_count']);

        $sent    = $campaigns->sum('sent_count');
        $opened  = $campaigns->sum('opened_count');
        $clicked = $campaigns->sum('clicked_count');

        return [
            $sent > 0 ? round($opened / $sent * 100, 1) : 0.0,
            $sent > 0 ? round($clicked / $sent * 100, 1) : 0.0,
        ];
    }

    private function computeDelta(int $current, int $prev): float
    {
        if ($prev === 0) return $current > 0 ? 100.0 : 0.0;
        return round(($current - $prev) / $prev * 100, 1);
    }
}
