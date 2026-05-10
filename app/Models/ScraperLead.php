<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScraperLead extends Model
{
    protected $fillable = [
        'job_id',
        'customer_id',
        'name',
        'email',
        'phone',
        'website',
        'address',
        'rating',
        'reviews_count',
        'category',
        'source_type',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'rating'        => 'float',
            'reviews_count' => 'integer',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ScraperJob::class, 'job_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
