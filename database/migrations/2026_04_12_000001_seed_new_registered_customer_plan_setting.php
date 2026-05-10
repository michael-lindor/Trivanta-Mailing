<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'new_registered_customer_plan_id'],
            [
                'category' => 'account',
                'value' => null,
                'type' => 'integer',
                'description' => 'Pricing plan automatically assigned to newly registered customers.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'new_registered_customer_plan_id')->delete();
    }
};
