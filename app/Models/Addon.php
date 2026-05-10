<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'category',
        'status',
        'license_key',
        'meta',
        'installed_at',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'meta'         => 'array',
            'installed_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    public static function isActive(string $slug): bool
    {
        try {
            return static::where('slug', $slug)->where('status', 'active')->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function isInstalled(string $slug): bool
    {
        try {
            return static::where('slug', $slug)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function isActive_(): bool
    {
        return $this->status === 'active';
    }
}
