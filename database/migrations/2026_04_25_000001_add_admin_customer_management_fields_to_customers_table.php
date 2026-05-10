<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('monthly_sending_limit')->default(0)->after('max_campaigns');
            $table->unsignedBigInteger('daily_sending_limit')->default(0)->after('monthly_sending_limit');
            $table->unsignedBigInteger('max_campaigns_per_day')->default(0)->after('daily_sending_limit');
            $table->boolean('welcome_campaign')->default(true)->after('max_campaigns_per_day');
            $table->json('auto_tagging_rules')->nullable()->after('welcome_campaign');
            $table->string('plan_id')->nullable()->after('auto_tagging_rules');
            $table->enum('renewal_type', ['monthly', 'yearly'])->nullable()->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_sending_limit',
                'daily_sending_limit',
                'max_campaigns_per_day',
                'welcome_campaign',
                'auto_tagging_rules',
                'plan_id',
                'renewal_type',
            ]);
        });
    }
};
