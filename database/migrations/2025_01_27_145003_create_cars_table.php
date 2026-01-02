<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم السيارة
            $table->string('brand'); // العلامة التجارية
            $table->string('model'); // الموديل
            $table->decimal('price', 10, 2); // السعر
            $table->text('description')->nullable(); // الوصف
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // حالة السيارة
            $table->unsignedBigInteger('user_id'); // ارتباط مع اللوكل أدمن
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
