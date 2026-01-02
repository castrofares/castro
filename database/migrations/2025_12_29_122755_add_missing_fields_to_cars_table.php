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
        Schema::table('cars', function (Blueprint $table) {
            $table->string('year')->nullable(); // سنة الصنع
            $table->enum('transmission', ['manual', 'automatic', 'both'])->default('manual'); // ناقل الحركة
            $table->string('fuel_type')->nullable(); // نوع الوقود (بنزين، ديزل، كهرباء، هجين)
            $table->string('color')->nullable(); // اللون
            $table->string('location')->nullable(); // الموقع
            $table->enum('availability', ['sale', 'rent', 'both'])->default('sale'); // التوفر (للبيع، للإيجار، كلاهما)
            $table->integer('mileage')->nullable(); // المسافة المقطوعة
            $table->integer('doors')->nullable(); // عدد الأبواب
            $table->integer('seats')->nullable(); // عدد المقاعد
            $table->string('engine_size')->nullable(); // حجم المحرك
            $table->decimal('rental_price_hourly', 10, 2)->nullable(); // سعر الإيجار بالساعة
            $table->decimal('rental_price_daily', 10, 2)->nullable(); // سعر الإيجار اليومي
            $table->decimal('rental_price_weekly', 10, 2)->nullable(); // سعر الإيجار الأسبوعي
            $table->decimal('rental_price_monthly', 10, 2)->nullable(); // سعر الإيجار الشهري
            $table->boolean('is_featured')->default(false); // سيارة مميزة
            $table->integer('views_count')->default(0); // عدد المشاهدات
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn([
                'year',
                'transmission',
                'fuel_type',
                'color',
                'location',
                'availability',
                'mileage',
                'doors',
                'seats',
                'engine_size',
                'rental_price_hourly',
                'rental_price_daily',
                'rental_price_weekly',
                'rental_price_monthly',
                'is_featured',
                'views_count'
            ]);
        });
    }
};
