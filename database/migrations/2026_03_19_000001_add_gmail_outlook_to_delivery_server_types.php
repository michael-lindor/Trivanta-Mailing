<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE delivery_servers MODIFY type ENUM('smtp','sendmail','gmail','outlook','amazon-ses','mailgun','sendgrid','postmark','sparkpost','zeptomail','zeptomail-api') DEFAULT 'smtp'");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE delivery_servers MODIFY type ENUM('smtp','sendmail','amazon-ses','mailgun','sendgrid','postmark','sparkpost','zeptomail','zeptomail-api') DEFAULT 'smtp'");
        } catch (\Throwable $e) {
        }
    }
};
