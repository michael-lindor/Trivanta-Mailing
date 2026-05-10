<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('delay_days')->default(0);
            $table->string('subject_a')->nullable();
            $table->text('body_a')->nullable();
            $table->string('subject_b')->nullable();
            $table->text('body_b')->nullable();
            $table->unsignedTinyInteger('variant_split')->default(50);
            $table->boolean('has_variant_b')->default(false);
            $table->timestamps();

            $table->index(['campaign_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_sequences');
    }
};
