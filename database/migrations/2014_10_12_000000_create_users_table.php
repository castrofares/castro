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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
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
            ])->default('viewer');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('region')->nullable(); // للمسؤولين المحليين
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
