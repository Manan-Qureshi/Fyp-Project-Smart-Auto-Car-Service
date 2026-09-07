<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('service_providers', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->after('close_time');
            }
            if (!Schema::hasColumn('service_providers', 'stripe_onboarding_completed')) {
                $table->boolean('stripe_onboarding_completed')->default(false)->after('stripe_account_id');
            }
        });

        Schema::table('commissions', function (Blueprint $table) {
            if (!Schema::hasColumn('commissions', 'stripe_transfer_id')) {
                $table->string('stripe_transfer_id')->nullable()->after('provider_earning');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (Schema::hasColumn('service_providers', 'stripe_account_id')) {
                $table->dropColumn('stripe_account_id');
            }
            if (Schema::hasColumn('service_providers', 'stripe_onboarding_completed')) {
                $table->dropColumn('stripe_onboarding_completed');
            }
        });

        Schema::table('commissions', function (Blueprint $table) {
            if (Schema::hasColumn('commissions', 'stripe_transfer_id')) {
                $table->dropColumn('stripe_transfer_id');
            }
        });
    }
};
