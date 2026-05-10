<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailList;
use App\Models\ListSegment;
use App\Models\ListSubscriber;
use App\Services\EmailListService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EmailListController extends Controller
{
    public function __construct(
        protected EmailListService $emailListService
    ) {
        $this->middleware('customer.access:lists.permissions.can_access_lists')->only(['index', 'show']);
        $this->middleware('customer.access:lists.permissions.can_create_lists')->only(['create', 'store']);
        $this->middleware('customer.access:lists.permissions.can_edit_lists')->only(['edit', 'update', 'storeTag', 'updateTag', 'destroyTag']);
        $this->middleware('customer.access:lists.permissions.can_delete_lists')->only(['destroy']);
    }

    protected function authorizeOwnership(EmailList $list): EmailList
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $list->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $list;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $viewTab = (string) $request->input('view', 'all');
        if ((string) $request->input('tab') === 'tags') {
            return redirect()->route('customer.tags.index');
        }
        $search  = trim((string) $request->input('search', ''));
        $sortBy  = (string) $request->input('sort', 'updated_at_desc');
        $perPage = 15;
        $customer = auth('customer')->user();

        // ── Audience stats (always computed) ───────────────────────────────
        $audienceRow = EmailList::query()
            ->where('customer_id', $customer->id)
            ->selectRaw('COUNT(*) as lists_count')
            ->selectRaw('COALESCE(SUM(subscribers_count), 0) as total_contacts')
            ->selectRaw('COALESCE(SUM(confirmed_subscribers_count), 0) as active_subscribers')
            ->selectRaw('COALESCE(SUM(unsubscribed_count), 0) as unsubscribed_count')
            ->first();

        $campaignRow = Campaign::query()
            ->where('customer_id', $customer->id)
            ->whereNotNull('list_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN sent_count > bounced_count THEN sent_count - bounced_count ELSE 0 END), 0) as delivered_count')
            ->selectRaw('COALESCE(SUM(opened_count), 0) as opened_count')
            ->first();

        $deliveredTotal = (int) ($campaignRow->delivered_count ?? 0);
        $avgOpenRate    = $deliveredTotal > 0
            ? round((int) ($campaignRow->opened_count ?? 0) / $deliveredTotal * 100, 1)
            : 0.0;

        $listsTotal    = (int) ($audienceRow->lists_count ?? 0);
        $segmentsTotal = ListSegment::query()
            ->whereHas('emailList', fn ($q) => $q->where('customer_id', $customer->id))
            ->count();

        // ── Growth deltas (last 30 days vs previous 30 days) ──────────────────
        $listIds = EmailList::query()->where('customer_id', $customer->id)->pluck('id')->all();

        $delta = static function (int $curr, int $prev): ?float {
            if ($prev === 0) return null;
            return round(($curr - $prev) / $prev * 100, 1);
        };

        $currSubs = ListSubscriber::whereIn('list_id', $listIds)
            ->where('subscribed_at', '>=', now()->subDays(30))->count();
        $prevSubs = ListSubscriber::whereIn('list_id', $listIds)
            ->whereBetween('subscribed_at', [now()->subDays(60), now()->subDays(30)])->count();

        $currConfirmed = ListSubscriber::whereIn('list_id', $listIds)
            ->where('status', 'confirmed')->where('subscribed_at', '>=', now()->subDays(30))->count();
        $prevConfirmed = ListSubscriber::whereIn('list_id', $listIds)
            ->where('status', 'confirmed')
            ->whereBetween('subscribed_at', [now()->subDays(60), now()->subDays(30)])->count();

        $currUnsub = ListSubscriber::whereIn('list_id', $listIds)
            ->where('status', 'unsubscribed')->where('unsubscribed_at', '>=', now()->subDays(30))->count();
        $prevUnsub = ListSubscriber::whereIn('list_id', $listIds)
            ->where('status', 'unsubscribed')
            ->whereBetween('unsubscribed_at', [now()->subDays(60), now()->subDays(30)])->count();

        $audienceStats = [
            'total_contacts'         => (int) ($audienceRow->total_contacts ?? 0),
            'total_contacts_delta'   => $delta($currSubs, $prevSubs),
            'active_subscribers'     => (int) ($audienceRow->active_subscribers ?? 0),
            'active_subscribers_delta' => $delta($currConfirmed, $prevConfirmed),
            'avg_open_rate'          => $avgOpenRate,
            'avg_open_rate_delta'    => null,
            'unsubscribed'           => (int) ($audienceRow->unsubscribed_count ?? 0),
            'unsubscribed_delta'     => $delta($currUnsub, $prevUnsub),
            'lists_count'            => $listsTotal,
            'segments_count'         => $segmentsTotal,
            'all_count'              => $listsTotal + $segmentsTotal,
        ];

        // ── Sort config ─────────────────────────────────────────────────────
        [$sortCol, $sortDir] = match ($sortBy) {
            'name_asc'  => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'subs_desc' => ['subscribers_count', 'desc'],
            default     => ['updated_at', 'desc'],
        };

        // ── Build paginated table items ─────────────────────────────────────
        if ($viewTab === 'lists') {
            $listsQ = EmailList::query()
                ->where('customer_id', $customer->id)
                ->when($search, fn ($q) => $q->where(fn ($q2) =>
                    $q2->where('display_name', 'like', "%{$search}%")
                       ->orWhere('name', 'like', "%{$search}%")
                ))
                ->orderBy($sortCol === 'name' ? 'display_name' : $sortCol, $sortDir);

            $paginatedLists = $listsQ->paginate($perPage)->withQueryString();
            $perfMap = $this->listPerformanceMap((int) $customer->id, $paginatedLists->pluck('id')->all());
            $this->appendPerformanceToLists($paginatedLists, $perfMap);
            $paginator = $paginatedLists->setCollection(
                $paginatedLists->getCollection()->map(fn ($l) => $this->wrapListItem($l))
            );

        } elseif ($viewTab === 'segments') {
            $segsQ = ListSegment::query()
                ->whereHas('emailList', fn ($q) => $q->where('customer_id', $customer->id))
                ->with('emailList')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy($sortCol, $sortDir);

            $paginatedSegs = $segsQ->paginate($perPage)->withQueryString();
            $paginator = $paginatedSegs->setCollection(
                $paginatedSegs->getCollection()->map(fn ($s) => $this->wrapSegmentItem($s))
            );

        } else {
            // all: load + merge + manual paginate
            $allLists = EmailList::query()
                ->where('customer_id', $customer->id)
                ->when($search, fn ($q) => $q->where(fn ($q2) =>
                    $q2->where('display_name', 'like', "%{$search}%")
                       ->orWhere('name', 'like', "%{$search}%")
                ))
                ->get();

            $perfMap = $this->listPerformanceMap((int) $customer->id, $allLists->pluck('id')->all());
            $allLists->each(function ($l) use ($perfMap) {
                $m = $perfMap[(int) $l->id] ?? ['open_rate' => 0.0, 'click_rate' => 0.0];
                $l->setAttribute('open_rate', $m['open_rate']);
                $l->setAttribute('click_rate', $m['click_rate']);
            });

            $allSegs = ListSegment::query()
                ->whereHas('emailList', fn ($q) => $q->where('customer_id', $customer->id))
                ->with('emailList')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->get();

            $merged = collect()
                ->merge($allLists->map(fn ($l) => $this->wrapListItem($l)))
                ->merge($allSegs->map(fn ($s) => $this->wrapSegmentItem($s)));

            $merged = $sortDir === 'asc'
                ? $merged->sortBy($sortCol === 'name' ? 'name' : 'sort_key')->values()
                : $merged->sortByDesc($sortCol === 'name' ? 'name' : 'sort_key')->values();

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $paginator   = new LengthAwarePaginator(
                $merged->slice(($currentPage - 1) * $perPage, $perPage)->values(),
                $merged->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('customer.lists.index', compact('audienceStats', 'viewTab', 'search', 'sortBy', 'paginator'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer.lists.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('lists.limits.max_lists', $customer->emailLists()->count(), 'Email list limit reached.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'opt_in' => ['nullable', 'in:single,double'],
            'opt_out' => ['nullable', 'in:single,double'],
            'double_opt_in' => ['nullable', 'boolean'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'welcome_email_enabled' => ['nullable', 'boolean'],
            'welcome_email_subject' => ['nullable', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'string'],
            'unsubscribe_email_enabled' => ['nullable', 'boolean'],
            'unsubscribe_email_subject' => ['nullable', 'string', 'max:255'],
            'unsubscribe_email_content' => ['nullable', 'string'],
            'tags' => ['nullable'],
        ]);

        $validated['tags'] = $this->normalizeTags($request->input('tags'));
        $validated = $this->syncOptInFields($validated);

        $emailList = $this->emailListService->create($customer, $validated);

        return redirect()
            ->route('customer.lists.show', $emailList)
            ->with('success', 'Email list created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailList $list)
    {
        $this->authorizeOwnership($list);

        $performance = $this->listPerformanceMap((int) $list->customer_id, [(int) $list->id]);
        $listPerformance = $performance[(int) $list->id] ?? [
            'campaigns_count' => 0,
            'sent_count' => 0,
            'delivered_count' => 0,
            'opened_count' => 0,
            'clicked_count' => 0,
            'open_rate' => 0.0,
            'click_rate' => 0.0,
        ];

        $recentCampaigns = Campaign::query()
            ->where('customer_id', (int) $list->customer_id)
            ->where('list_id', (int) $list->id)
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'name', 'status', 'sent_count', 'opened_count', 'clicked_count', 'bounced_count', 'created_at']);

        $recentAdditions = $list->subscribers()
            ->latest('subscribed_at')
            ->limit(5)
            ->get(['id', 'email', 'first_name', 'last_name', 'source', 'subscribed_at']);

        $growthPeriods = $this->buildGrowthData($list);

        $segmentsCount = $list->segments()->count();

        [$healthScore, $healthLabel] = $this->computeHealthScore($list, $listPerformance);

        $subscriberDelta = $this->computeSubscriberDelta($list);

        return view('customer.lists.show', compact(
            'list', 'listPerformance', 'recentCampaigns',
            'recentAdditions', 'growthPeriods', 'segmentsCount',
            'healthScore', 'healthLabel', 'subscriberDelta'
        ));
    }

    private function buildGrowthData(EmailList $list): array
    {
        $periods = [7, 30, 90];
        $result = [];

        foreach ($periods as $days) {
            $rows = $list->subscribers()
                ->selectRaw('DATE(subscribed_at) as date, COUNT(*) as cnt')
                ->where('subscribed_at', '>=', now()->subDays($days - 1)->startOfDay())
                ->groupByRaw('DATE(subscribed_at)')
                ->orderBy('date')
                ->pluck('cnt', 'date')
                ->toArray();

            $labels = [];
            $values = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $labels[] = $days <= 7
                    ? now()->subDays($i)->format('D')
                    : now()->subDays($i)->format('M d');
                $values[] = (int) ($rows[$date] ?? 0);
            }

            $result[$days] = ['labels' => $labels, 'values' => $values];
        }

        return $result;
    }

    private function computeHealthScore(EmailList $list, array $performance): array
    {
        $total = max(1, (int) $list->subscribers_count);
        $confirmedRate = min(100, ((int) $list->confirmed_subscribers_count / $total) * 100);
        $bounceRate    = ((int) $list->bounced_count / $total) * 100;
        $openRate      = min(100, (float) ($performance['open_rate'] ?? 0));
        $clickRate     = min(100, (float) ($performance['click_rate'] ?? 0));

        $score = (int) round(
            ($confirmedRate * 0.30) +
            (min($openRate, 60) / 60 * 100 * 0.30) +
            (min($clickRate, 20) / 20 * 100 * 0.20) +
            (max(0.0, 100.0 - $bounceRate * 5) * 0.20)
        );
        $score = max(0, min(100, $score));

        $label = match (true) {
            $score >= 90 => 'Excellent',
            $score >= 70 => 'Good',
            $score >= 50 => 'Fair',
            default      => 'Poor',
        };

        return [$score, $label];
    }

    private function computeSubscriberDelta(EmailList $list): float
    {
        $current  = $list->subscribers()->where('subscribed_at', '>=', now()->subDays(30))->count();
        $previous = $list->subscribers()
            ->where('subscribed_at', '>=', now()->subDays(60))
            ->where('subscribed_at', '<', now()->subDays(30))
            ->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    private function listPerformanceMap(int $customerId, array $listIds): array
    {
        $listIds = array_values(array_filter(array_map('intval', $listIds), static fn (int $id) => $id > 0));
        if ($listIds === []) {
            return [];
        }

        $rows = Campaign::query()
            ->where('customer_id', $customerId)
            ->whereIn('list_id', $listIds)
            ->groupBy('list_id')
            ->selectRaw('list_id')
            ->selectRaw('COUNT(*) as campaigns_count')
            ->selectRaw('COALESCE(SUM(sent_count), 0) as sent_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN sent_count > bounced_count THEN sent_count - bounced_count ELSE 0 END), 0) as delivered_count')
            ->selectRaw('COALESCE(SUM(opened_count), 0) as opened_count')
            ->selectRaw('COALESCE(SUM(clicked_count), 0) as clicked_count')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $listId = (int) ($row->list_id ?? 0);
            if ($listId <= 0) {
                continue;
            }

            $delivered = (int) ($row->delivered_count ?? 0);
            $opened = (int) ($row->opened_count ?? 0);
            $clicked = (int) ($row->clicked_count ?? 0);

            $map[$listId] = [
                'campaigns_count' => (int) ($row->campaigns_count ?? 0),
                'sent_count' => (int) ($row->sent_count ?? 0),
                'delivered_count' => $delivered,
                'opened_count' => $opened,
                'clicked_count' => $clicked,
                'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0.0,
                'click_rate' => $delivered > 0 ? round(($clicked / $delivered) * 100, 2) : 0.0,
            ];
        }

        return $map;
    }

    private function appendPerformanceToLists(LengthAwarePaginator $emailLists, array $performanceMap): void
    {
        $collection = $emailLists->getCollection();

        if (!$collection instanceof Collection) {
            return;
        }

        $collection->transform(function (EmailList $list) use ($performanceMap) {
            $metrics = $performanceMap[(int) $list->id] ?? [
                'campaigns_count' => 0,
                'sent_count' => 0,
                'delivered_count' => 0,
                'opened_count' => 0,
                'clicked_count' => 0,
                'open_rate' => 0.0,
                'click_rate' => 0.0,
            ];

            $list->setAttribute('campaigns_count', $metrics['campaigns_count']);
            $list->setAttribute('sent_count', $metrics['sent_count']);
            $list->setAttribute('delivered_count', $metrics['delivered_count']);
            $list->setAttribute('opened_count', $metrics['opened_count']);
            $list->setAttribute('clicked_count', $metrics['clicked_count']);
            $list->setAttribute('open_rate', $metrics['open_rate']);
            $list->setAttribute('click_rate', $metrics['click_rate']);

            return $list;
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailList $list)
    {
        $this->authorizeOwnership($list);
        return view('customer.lists.edit', compact('list'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'opt_in' => ['nullable', 'in:single,double'],
            'opt_out' => ['nullable', 'in:single,double'],
            'double_opt_in' => ['nullable', 'boolean'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'welcome_email_enabled' => ['nullable', 'boolean'],
            'welcome_email_subject' => ['nullable', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'string'],
            'unsubscribe_email_enabled' => ['nullable', 'boolean'],
            'unsubscribe_email_subject' => ['nullable', 'string', 'max:255'],
            'unsubscribe_email_content' => ['nullable', 'string'],
            'tags' => ['nullable'],
        ]);

        $validated['tags'] = $this->normalizeTags($request->input('tags'));
        $validated = $this->syncOptInFields($validated);

        $this->emailListService->update($list, $validated);

        return redirect()
            ->route('customer.lists.show', $list)
            ->with('success', 'Email list updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailList $list)
    {
        $this->authorizeOwnership($list);
        $this->emailListService->delete($list);

        return redirect()
            ->route('customer.lists.index')
            ->with('success', 'Email list deleted successfully.');
    }

    public function storeTag(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);

        $validated = $request->validate([
            'tag' => ['required', 'string', 'max:100'],
        ]);

        $existingTags = is_array($list->tags ?? null) ? $list->tags : [];
        $newTag = trim((string) $validated['tag']);

        if ($newTag !== '' && !in_array($newTag, $existingTags, true)) {
            $existingTags[] = $newTag;
        }

        $list->update([
            'tags' => $this->normalizeTags($existingTags),
        ]);

        return redirect()
            ->route('customer.tags.index')
            ->with('success', 'Tag added successfully.');
    }

    public function updateTag(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);

        $validated = $request->validate([
            'old_tag' => ['required', 'string', 'max:100'],
            'new_tag' => ['required', 'string', 'max:100'],
        ]);

        $tags = is_array($list->tags ?? null) ? $list->tags : [];
        $oldTag = trim((string) $validated['old_tag']);
        $newTag = trim((string) $validated['new_tag']);

        foreach ($tags as $index => $tag) {
            if ((string) $tag === $oldTag) {
                $tags[$index] = $newTag;
            }
        }

        $list->update([
            'tags' => $this->normalizeTags($tags),
        ]);

        return redirect()
            ->route('customer.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroyTag(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);

        $validated = $request->validate([
            'tag' => ['required', 'string', 'max:100'],
        ]);

        $tagToDelete = trim((string) $validated['tag']);
        $tags = is_array($list->tags ?? null) ? $list->tags : [];
        $tags = array_values(array_filter($tags, static fn ($tag) => (string) $tag !== $tagToDelete));

        $list->update([
            'tags' => $this->normalizeTags($tags),
        ]);

        return redirect()
            ->route('customer.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }

    private function normalizeTags(mixed $tagsInput): array
    {
        $tags = [];

        if (is_string($tagsInput)) {
            $tags = preg_split('/[,\n]+/', $tagsInput) ?: [];
        } elseif (is_array($tagsInput)) {
            $tags = $tagsInput;
        }

        $normalized = array_values(array_unique(array_filter(array_map(
            static fn ($tag) => trim((string) $tag),
            $tags
        ), static fn (string $tag) => $tag !== '')));

        return array_slice($normalized, 0, 50);
    }

    private function syncOptInFields(array $data): array
    {
        if (array_key_exists('opt_in', $data)) {
            $data['double_opt_in'] = $data['opt_in'] === 'double';
        } elseif (array_key_exists('double_opt_in', $data)) {
            $data['opt_in'] = (bool) $data['double_opt_in'] ? 'double' : 'single';
        }

        return $data;
    }

    private function wrapListItem(EmailList $l): array
    {
        return [
            'type'       => 'list',
            'id'         => $l->id,
            'name'       => $l->display_name ?? $l->name,
            'subtitle'   => 'ID: ' . ($l->name ?? ''),
            'subscribers'=> (int) ($l->confirmed_subscribers_count ?? 0),
            'open_rate'  => (float) ($l->open_rate ?? 0),
            'click_rate' => (float) ($l->click_rate ?? 0),
            'updated_at' => $l->updated_at,
            'sort_key'   => $l->updated_at?->timestamp ?? 0,
            'url'        => route('customer.lists.show', $l),
        ];
    }

    private function wrapSegmentItem(ListSegment $s): array
    {
        $rules     = is_array($s->rules) ? $s->rules : [];
        $condCount = is_array($rules['conditions'] ?? null) ? count($rules['conditions']) : 0;

        return [
            'type'       => 'segment',
            'id'         => $s->id,
            'name'       => $s->name,
            'subtitle'   => 'Conditions: ' . $condCount . ' ' . ($condCount === 1 ? 'Rule' : 'Rules'),
            'subscribers'=> (int) ($s->subscribers_count ?? 0),
            'open_rate'  => 0.0,
            'click_rate' => 0.0,
            'updated_at' => $s->updated_at,
            'sort_key'   => $s->updated_at?->timestamp ?? 0,
            'url'        => $s->emailList ? route('customer.lists.segments.index', $s->emailList) : '#',
        ];
    }
}
