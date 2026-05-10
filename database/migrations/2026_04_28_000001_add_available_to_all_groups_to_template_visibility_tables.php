<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_templates', function (Blueprint $table) {
            $table->boolean('available_to_all_groups')->default(true)->after('is_active');
        });

        Schema::table('built_in_template_settings', function (Blueprint $table) {
            $table->boolean('available_to_all_groups')->default(true)->after('is_active');
        });

        DB::table('public_templates')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('public_template_customer_group')
                    ->whereColumn('public_template_customer_group.public_template_id', 'public_templates.id');
            })
            ->update(['available_to_all_groups' => false]);

        DB::table('built_in_template_settings')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('built_in_template_customer_group')
                    ->whereColumn('built_in_template_customer_group.built_in_template_setting_id', 'built_in_template_settings.id');
            })
            ->update(['available_to_all_groups' => false]);
    }

    public function down(): void
    {
        Schema::table('public_templates', function (Blueprint $table) {
            $table->dropColumn('available_to_all_groups');
        });

        Schema::table('built_in_template_settings', function (Blueprint $table) {
            $table->dropColumn('available_to_all_groups');
        });
    }
};
