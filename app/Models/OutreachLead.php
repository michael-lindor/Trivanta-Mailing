<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachLead extends Model
{
    protected $fillable = [
        'campaign_id',
        'first_name',
        'last_name',
        'email',
        'company',
        'status',
        'meta',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'meta'             => 'array',
            'last_activity_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(OutreachCampaign::class, 'campaign_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name ?? '', 0, 1);
        $last  = mb_substr($this->last_name ?? '', 0, 1);
        return strtoupper($first . $last) ?: strtoupper(mb_substr($this->email, 0, 2));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'replied'      => 'text-green-700 bg-green-50 border-green-200 dark:bg-green-900/20 dark:text-green-400',
            'opened'       => 'text-blue-700 bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400',
            'clicked'      => 'text-purple-700 bg-purple-50 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400',
            'bounced'      => 'text-red-700 bg-red-50 border-red-200 dark:bg-red-900/20 dark:text-red-400',
            'unsubscribed' => 'text-gray-600 bg-gray-100 border-gray-200 dark:bg-white/10 dark:text-gray-400',
            'sent'         => 'text-gray-500 bg-gray-50 border-gray-200 dark:bg-white/5 dark:text-gray-400',
            default        => 'text-gray-500 bg-gray-50 border-gray-200 dark:bg-white/5 dark:text-gray-400',
        };
    }
}
