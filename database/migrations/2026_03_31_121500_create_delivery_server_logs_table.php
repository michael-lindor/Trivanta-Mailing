<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_server_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_server_id')->constrained()->onDelete('cascade');
            $table->string('event'); // test_sent, test_failed, bounce, auth_error, connection_error
            $table->string('to_email')->nullable();
            $table->string('status'); // success, failed, bounced
            $table->string('error_code')->nullable(); // e.g. 550, 5.7.26
            $table->text('error_message')->nullable(); // human-readable error
            $table->text('diagnostic')->nullable(); // raw SMTP diagnostic / bounce message
            $table->string('error_category')->nullable(); // spf_fail, dkim_fail, auth_fail, connection, quota, unknown
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['delivery_server_id', 'created_at']);
            $table->index('event');
            $table->index('status');
            $table->index('error_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_server_logs');
    }
};
