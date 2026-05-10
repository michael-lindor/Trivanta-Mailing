<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachSequence extends Model
{
    protected $fillable = [
        'campaign_id',
        'sort_order',
        'delay_days',
        'delay_type',
        'subject_a',
        'body_a',
        'subject_b',
        'body_b',
        'variant_split',
        'has_variant_b',
    ];

    protected function casts(): array
    {
        return [
            'delay_days'    => 'integer',
            'delay_type'    => 'string',
            'sort_order'    => 'integer',
            'variant_split' => 'integer',
            'has_variant_b' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(OutreachCampaign::class, 'campaign_id');
    }
}
