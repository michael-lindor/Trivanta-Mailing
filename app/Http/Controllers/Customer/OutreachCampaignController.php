<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\DeliveryServer;
use App\Models\OutreachCampaign;
use App\Models\OutreachLead;
use App\Models\OutreachSequence;
use App\Models\Template;
use App\Models\TrackingDomain;
use App\Services\TemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OutreachCampaignController extends Controller
{
    public function __construct(
        protected TemplateService $templateService
    ) {
    }

    private function guardAddon(): void
    {
        if (!Addon::isActive('cold-email-outreach')) {
            abort(403, 'Cold Email Outreach addon is not active.');
        }
    }

    private function ownedCampaign(int $id): OutreachCampaign
    {
        return OutreachCampaign::where('customer_id', auth('customer')->id())
            ->findOrFail($id);
    }

    public function show(Request $request, int $campaign): View
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);
        $tab      = $request->query('tab', 'analytics');

        $statusInsights = $this->buildStatusInsights($campaign);

        $data = compact('campaign', 'tab', 'statusInsights');

        if ($tab === 'analytics') {
            $leadStats = OutreachLead::where('campaign_id', $campaign->id)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $data['totalSent']   = $leadStats->except(['pending'])->sum();
            $data['delivered']   = $leadStats->except(['pending', 'bounced'])->sum();
            $data['opens']       = $leadStats->only(['opened', 'clicked', 'replied'])->sum();
            $data['clicks']      = $leadStats->only(['clicked'])->sum();
            $data['replies']     = $leadStats->only(['replied'])->sum();
            $data['bounces']     = $leadStats->only(['bounced'])->sum();
            $data['leadStats']   = $leadStats;
        }

        if ($tab === 'leads') {
            $search  = (string) $request->query('search', '');
            $status  = (string) $request->query('status', '');

            $query = OutreachLead::where('campaign_id', $campaign->id)
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($s) use ($search) {
                        $s->where('email', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('company', 'like', "%{$search}%");
                    });
                })
                ->when($status !== '', fn ($q) => $q->where('status', $status))
                ->latest();

            $data['leads']        = $query->paginate(20)->withQueryString();
            $data['searchQuery']  = $search;
            $data['statusFilter'] = $status;
        }

        if ($tab === 'sequences') {
            $data['sequences'] = OutreachSequence::where('campaign_id', $campaign->id)
                ->orderBy('sort_order')
                ->get();
            $customer = auth('customer')->user();
            $data['templates'] = Template::query()
                ->whereIn('type', ['email', 'campaign'])
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'customer_id', 'is_system', 'is_public'])
                ->filter(fn (Template $template) => $customer && $this->templateService->canAccessTemplate($template, $customer))
                ->values();
        }

        if ($tab === 'options') {
            $data['deliveryServers'] = DeliveryServer::where('customer_id', auth('customer')->id())
                ->where('status', 'active')
                ->get();
            $data['trackingDomains'] = TrackingDomain::where('customer_id', auth('customer')->id())
                ->where('status', 'verified')
                ->get();
        }

        return view('customer.outreach.show', $data);
    }

    private function buildStatusInsights(OutreachCampaign $campaign): array
    {
        $issues = [];

        if ($campaign->leads_count < 1) {
            $issues[] = __('No leads have been added to this campaign yet.');
        }

        if (!OutreachSequence::where('campaign_id', $campaign->id)->exists()) {
            $issues[] = __('No sequence steps have been configured yet.');
        }

        if (count($campaign->getSetting('sender_account_ids', [])) < 1) {
            $issues[] = __('No sender account has been selected in campaign options.');
        }

        $logs = $campaign->statusLogs()->map(function ($log) {
            $status = (string) ($log['status'] ?? 'info');

            return [
                'status' => $status,
                'badge' => match ($status) {
                    'failed' => 'text-red-700 bg-red-50 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
                    'paused' => 'text-amber-700 bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
                    'active' => 'text-green-700 bg-green-50 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
                    default => 'text-gray-700 bg-gray-50 border-gray-200 dark:bg-white/5 dark:text-admin-text-primary dark:border-admin-border',
                },
                'title' => Str::headline($status),
                'message' => (string) ($log['message'] ?? ''),
                'created_at' => $log['created_at'] ?? null,
            ];
        })->all();

        if (empty($logs) && in_array($campaign->status, ['paused', 'failed'], true)) {
            $logs[] = [
                'status' => $campaign->status,
                'badge' => $campaign->status === 'failed'
                    ? 'text-red-700 bg-red-50 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800'
                    : 'text-amber-700 bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
                'title' => Str::headline($campaign->status),
                'message' => $campaign->status === 'failed'
                    ? __('This campaign entered a failed state. Review the setup below and retry after fixing the issue.')
                    : __('This campaign is currently paused.'),
                'created_at' => optional($campaign->updated_at)?->toDateTimeString(),
            ];
        }

        return [
            'issues' => $issues,
            'logs' => $logs,
            'has_attention_items' => !empty($issues) || !empty($logs),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $this->guardAddon();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $campaign = OutreachCampaign::create([
            'customer_id' => auth('customer')->id(),
            'name'        => $validated['name'],
            'status'      => 'draft',
            'settings'    => (new OutreachCampaign)->getDefaultSettings(),
        ]);

        return redirect()->route('customer.outreach.campaigns.show', $campaign)
            ->with('success', __('Campaign created successfully.'));
    }

    public function duplicate(int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        $duplicate = DB::transaction(function () use ($campaign) {
            $copy = OutreachCampaign::create([
                'customer_id' => auth('customer')->id(),
                'name' => $campaign->name . ' ' . __('(Copy)'),
                'status' => 'draft',
                'leads_count' => 0,
                'settings' => $campaign->settings ?? $campaign->getDefaultSettings(),
            ]);

            foreach ($campaign->sequences()->get() as $sequence) {
                OutreachSequence::create([
                    'campaign_id' => $copy->id,
                    'sort_order' => $sequence->sort_order,
                    'delay_days' => $sequence->delay_days,
                    'delay_type' => $sequence->delay_type,
                    'subject_a' => $sequence->subject_a,
                    'body_a' => $sequence->body_a,
                    'subject_b' => $sequence->subject_b,
                    'body_b' => $sequence->body_b,
                    'variant_split' => $sequence->variant_split,
                    'has_variant_b' => $sequence->has_variant_b,
                ]);
            }

            return $copy;
        });

        return redirect()->route('customer.outreach.campaigns.show', $duplicate)
            ->with('success', __('Campaign duplicated successfully.'));
    }

    public function updateLeads(Request $request, int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        $request->validate([
            'leads'               => 'required|string',
            'leads_import_mode'   => 'in:append,replace',
        ]);

        $mode  = $request->input('leads_import_mode', 'append');
        $raw   = (string) $request->input('leads', '');
        $lines = array_filter(array_map('trim', explode("\n", $raw)));

        $rows = [];
        foreach ($lines as $line) {
            $parts = str_getcsv($line);
            $email = trim($parts[0] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $rows[] = [
                'campaign_id'      => $campaign->id,
                'email'            => strtolower($email),
                'first_name'       => trim($parts[1] ?? ''),
                'last_name'        => trim($parts[2] ?? ''),
                'company'          => trim($parts[3] ?? ''),
                'status'           => 'pending',
                'last_activity_at' => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        DB::transaction(function () use ($campaign, $mode, $rows) {
            if ($mode === 'replace') {
                OutreachLead::where('campaign_id', $campaign->id)->delete();
            }

            foreach (array_chunk($rows, 200) as $chunk) {
                OutreachLead::insertOrIgnore($chunk);
            }

            $campaign->update([
                'leads_count' => OutreachLead::where('campaign_id', $campaign->id)->count(),
            ]);
        });

        return redirect()->route('customer.outreach.campaigns.show', [$campaign->id, 'tab' => 'leads'])
            ->with('success', __('Leads imported successfully.'));
    }

    public function destroyLead(int $campaign, OutreachLead $lead): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        if ($lead->campaign_id !== $campaign->id) {
            abort(404);
        }

        $lead->delete();
        $campaign->decrement('leads_count');

        return back()->with('success', __('Lead removed.'));
    }

    public function updateSequences(Request $request, int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        $steps = $request->input('steps', []);

        DB::transaction(function () use ($campaign, $steps) {
            OutreachSequence::where('campaign_id', $campaign->id)->delete();

            foreach ($steps as $i => $step) {
                OutreachSequence::create([
                    'campaign_id'   => $campaign->id,
                    'sort_order'    => $i,
                    'delay_days'    => max(0, (int) ($step['delay_days'] ?? 0)),
                    'delay_type'    => in_array(($step['delay_type'] ?? 'days'), ['minutes', 'hours', 'days', 'weeks', 'months'], true)
                        ? $step['delay_type']
                        : 'days',
                    'subject_a'     => $step['subject_a'] ?? '',
                    'body_a'        => $step['body_a'] ?? '',
                    'subject_b'     => $step['subject_b'] ?? null,
                    'body_b'        => $step['body_b'] ?? null,
                    'variant_split' => min(100, max(0, (int) ($step['variant_split'] ?? 50))),
                    'has_variant_b' => !empty($step['has_variant_b']),
                ]);
            }
        });

        return back()->with('success', __('Sequences saved.'));
    }

    public function updateSchedule(Request $request, int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        $timeBlocks = collect($request->input('send_time_blocks', []))
            ->map(function ($block) {
                if (!is_array($block)) {
                    return null;
                }

                $start = (string) ($block['start'] ?? '');
                $end = (string) ($block['end'] ?? '');

                if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) {
                    return null;
                }

                return [
                    'start' => $start,
                    'end' => $end,
                ];
            })
            ->filter()
            ->values();

        if ($timeBlocks->isEmpty()) {
            $timeBlocks = collect([[
                'start' => (string) $request->input('send_hours_start', '09:00'),
                'end' => (string) $request->input('send_hours_end', '17:30'),
            ]]);
        }

        $primaryBlock = $timeBlocks->first();

        $settings = array_merge($campaign->settings ?? $campaign->getDefaultSettings(), [
            'timezone'          => $request->input('timezone', 'UTC'),
            'send_days'         => $request->input('send_days', []),
            'send_hours_start'  => $primaryBlock['start'] ?? '09:00',
            'send_hours_end'    => $primaryBlock['end'] ?? '17:30',
            'send_time_blocks'  => $timeBlocks->all(),
            'max_per_day'       => max(1, (int) $request->input('max_per_day', 150)),
            'min_delay_minutes' => max(1, (int) $request->input('min_delay_minutes', 5)),
            'track_opens'       => $request->boolean('track_opens'),
            'track_clicks'      => $request->boolean('track_clicks'),
        ]);

        $campaign->update(['settings' => $settings]);

        return back()->with('success', __('Schedule saved.'));
    }

    public function updateOptions(Request $request, int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        $settings = array_merge($campaign->settings ?? $campaign->getDefaultSettings(), [
            'stop_on_reply'            => $request->boolean('stop_on_reply'),
            'stop_on_auto_reply'       => $request->boolean('stop_on_auto_reply'),
            'tracking_domain'          => $request->input('tracking_domain'),
            'bcc_email'                => $request->input('bcc_email'),
            'sender_account_ids'       => $request->input('sender_account_ids', []),
            'enable_account_rotation'  => $request->boolean('enable_account_rotation'),
            'include_unsubscribe_link' => $request->boolean('include_unsubscribe_link'),
            'unsubscribe_text'         => $request->input('unsubscribe_text', ''),
        ]);

        $campaign->update([
            'name'     => $request->input('name', $campaign->name),
            'settings' => $settings,
        ]);

        return back()->with('success', __('Options saved.'));
    }

    public function pause(int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);
        $campaign->update(['status' => 'paused']);
        $campaign->appendStatusLog('paused', __('Campaign was paused manually.'));

        return back()->with('success', __('Campaign paused.'));
    }

    public function resume(int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        $issues = $this->buildStatusInsights($campaign)['issues'];

        if (!empty($issues)) {
            $campaign->update(['status' => 'failed']);
            $campaign->appendStatusLog('failed', __('Campaign could not be started: :reasons', ['reasons' => implode(' ', $issues)]));

            return back()->with('error', __('Campaign could not be started until the setup issues are fixed.'));
        }

        $campaign->update(['status' => 'active']);
        $campaign->appendStatusLog('active', __('Campaign was started successfully.'));

        return back()->with('success', __('Campaign resumed.'));
    }

    public function destroy(int $campaign): RedirectResponse
    {
        $this->guardAddon();
        $campaign = $this->ownedCampaign($campaign);

        DB::transaction(function () use ($campaign) {
            OutreachSequence::where('campaign_id', $campaign->id)->delete();
            OutreachLead::where('campaign_id', $campaign->id)->delete();
            $campaign->delete();
        });

        return redirect()->route('customer.outreach.index')
            ->with('success', __('Campaign deleted.'));
    }
}
