<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('status')->default('pending'); // pending, sent, opened, clicked, replied, bounced, unsubscribed
            $table->json('meta')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['campaign_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_leads');
    }
};
