<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class OutreachCampaign extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'status',
        'leads_count',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings'    => 'array',
            'leads_count' => 'integer',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(OutreachLead::class, 'campaign_id');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(OutreachSequence::class, 'campaign_id')->orderBy('sort_order');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'text-green-700 bg-green-50 dark:bg-green-900/20 dark:text-green-400',
            'paused'    => 'text-amber-700 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400',
            'failed'    => 'text-red-700 bg-red-50 dark:bg-red-900/20 dark:text-red-400',
            'completed' => 'text-blue-700 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-400',
            default     => 'text-gray-600 bg-gray-100 dark:bg-white/10 dark:text-gray-400',
        };
    }

    public function getDefaultSettings(): array
    {
        return [
            'timezone'                 => 'UTC',
            'send_days'                => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'send_hours_start'         => '09:00',
            'send_hours_end'           => '17:30',
            'send_time_blocks'         => [
                ['start' => '09:00', 'end' => '17:30'],
            ],
            'max_per_day'              => 150,
            'min_delay_minutes'        => 5,
            'track_opens'              => true,
            'track_clicks'             => false,
            'stop_on_reply'            => true,
            'stop_on_auto_reply'       => false,
            'tracking_domain'          => null,
            'bcc_email'                => null,
            'sender_account_ids'       => [],
            'status_logs'              => [],
            'enable_account_rotation'  => true,
            'include_unsubscribe_link' => true,
            'unsubscribe_text'         => 'If you no longer wish to receive these emails, click here to unsubscribe.',
        ];
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $this->getDefaultSettings()[$key] ?? $default;
    }

    public function statusLogs(): Collection
    {
        return collect($this->getSetting('status_logs', []))
            ->filter(fn ($log) => is_array($log) && !empty($log['message']))
            ->sortByDesc(fn ($log) => $log['created_at'] ?? null)
            ->values();
    }

    public function appendStatusLog(string $status, string $message, array $context = []): void
    {
        $settings = array_merge($this->getDefaultSettings(), $this->settings ?? []);
        $logs = collect($settings['status_logs'] ?? []);

        $logs->prepend([
            'status' => $status,
            'message' => $message,
            'context' => $context,
            'created_at' => now()->toDateTimeString(),
        ]);

        $settings['status_logs'] = $logs
            ->take(20)
            ->values()
            ->all();

        $this->forceFill(['settings' => $settings])->save();
    }
}
