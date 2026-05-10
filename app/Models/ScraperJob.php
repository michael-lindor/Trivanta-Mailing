<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScraperJob extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'query',
        'location',
        'language',
        'max_results',
        'extract_emails',
        'status',
        'records_count',
        'credits_used',
        'error_message',
        'debug_data',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'extract_emails' => 'boolean',
            'max_results'    => 'integer',
            'records_count'  => 'integer',
            'credits_used'   => 'integer',
            'debug_data'     => 'array',
            'completed_at'   => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ScraperLead::class, 'job_id');
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'maps'    => 'Maps',
            'places'  => 'Places',
            'reviews' => 'Reviews',
            'news'    => 'News',
            'images'  => 'Images',
            default   => ucfirst($this->type),
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'running'   => 'blue',
            'completed' => 'green',
            'failed'    => 'red',
            default     => 'gray',
        };
    }
}
