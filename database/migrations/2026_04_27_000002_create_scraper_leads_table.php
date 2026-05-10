<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraper_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('reviews_count')->nullable();
            $table->string('category')->nullable();
            $table->string('source_type', 30)->default('maps');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index(['job_id']);
            $table->index(['customer_id', 'source_type']);
            $table->index(['customer_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraper_leads');
    }
};
