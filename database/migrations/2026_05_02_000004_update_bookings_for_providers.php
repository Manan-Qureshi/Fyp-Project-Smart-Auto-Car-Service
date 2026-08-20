<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            // worker_id now references the new workers table (not users)
            $table->unsignedBigInteger('provider_worker_id')->nullable()->after('service_provider_id');
            $table->text('notes')->nullable()->after('appointment_time');
        });

        // Drop old columns separately (SQLite requires separate table alteration)
        if (Schema::hasColumn('bookings', 'payment_method')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn(['payment_method']);
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('provider_worker_id')->references('id')->on('workers')->onDelete('set null');
        });

        // Update status enum to support new statuses
        // MySQL doesn't support modifying enums cleanly, so we use a raw statement
        if (\DB::getDriverName() !== 'sqlite') {
            \DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending','payment_pending','confirmed','accepted','assigned','in_progress','completed','cancelled') DEFAULT 'payment_pending'");
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropForeign(['provider_worker_id']);
            $table->dropColumn(['service_provider_id', 'provider_worker_id', 'notes']);
            $table->string('payment_method')->nullable();
        });
    }
};
