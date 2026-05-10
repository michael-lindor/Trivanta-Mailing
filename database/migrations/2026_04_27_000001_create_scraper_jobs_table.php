<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraper_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('type', 30)->default('maps'); // maps, places, reviews, news, images
            $table->string('query');
            $table->string('location')->nullable();
            $table->string('language', 10)->default('en');
            $table->unsignedSmallInteger('max_results')->default(100);
            $table->boolean('extract_emails')->default(false);
            $table->string('status', 20)->default('pending'); // pending, running, completed, failed
            $table->unsignedInteger('records_count')->default(0);
            $table->unsignedInteger('credits_used')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraper_jobs');
    }
};
