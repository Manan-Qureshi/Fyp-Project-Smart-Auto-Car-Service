<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add category to services table (remove type concept, all are standard)
        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        // Add provider role to users
        if (\DB::getDriverName() !== 'sqlite') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','provider','worker','customer','user') DEFAULT 'customer'");
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
