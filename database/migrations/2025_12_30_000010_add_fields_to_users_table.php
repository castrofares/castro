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
        Schema::table('users', function (Blueprint $table) {
            // Add role column if not exists
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', [
                    'super_admin',
                    'local_admin',
                    'marketing_manager',
                    'consultant',
                    'delivery_company',
                    'seller',
                    'buyer',
                    'renter',
                    'viewer',
                    'system_user'
                ])->default('viewer')->after('email');
            }

            // Add other fields if not exists
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'region')) {
                $table->string('region')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('region');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'address', 'region', 'is_active']);
        });
    }
};
