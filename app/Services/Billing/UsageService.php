<?php

namespace App\Services\Billing;

use App\Models\CampaignRecipient;
use App\Models\Customer;
use App\Models\UsageLog;
use Carbon\Carbon;

class UsageService
{
    public function log(Customer $customer, string $metric, int $amount = 1, array $context = []): UsageLog
    {
        [$periodStart, $periodEnd] = $this->currentPeriodBounds();

        $log = UsageLog::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'metric' => $metric,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            ['amount' => 0]
        );

        $log->increment('amount', $amount);
        $log->update(['context' => array_merge($log->context ?? [], $context)]);

        return $log->fresh();
    }

    public function getUsage(Customer $customer): array
    {
        [$periodStart, $periodEnd] = $this->currentPeriodBounds();

        $usage = UsageLog::where('customer_id', $customer->id)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->get()
            ->mapWithKeys(fn ($log) => [$log->metric => $log->amount])
            ->toArray();

        $emailsSentThisMonth = CampaignRecipient::query()
            ->whereNotNull('sent_at')
            ->whereBetween('sent_at', [
                Carbon::parse($periodStart)->startOfDay(),
                Carbon::parse($periodEnd)->endOfDay(),
            ])
            ->whereIn('status', ['sent', 'opened', 'clicked'])
            ->whereHas('campaign', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->count();

        $usage['emails_sent_this_month'] = max(
            (int) ($usage['emails_sent_this_month'] ?? 0),
            $emailsSentThisMonth
        );

        return $usage;
    }

    private function currentPeriodBounds(): array
    {
        $now = Carbon::now();
        $periodStart = $now->copy()->startOfMonth()->toDateString();
        $periodEnd = $now->copy()->endOfMonth()->toDateString();

        return [$periodStart, $periodEnd];
    }
}

