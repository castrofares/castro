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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['sales', 'inventory', 'performance', 'custom'])->default('custom');
            $table->date('period_from');
            $table->date('period_to');
            $table->json('data')->nullable(); // تخزين بيانات التقرير كـ JSON
            $table->enum('status', ['draft', 'completed', 'sent'])->default('draft');
            $table->json('sent_to')->nullable(); // قائمة بالمستلمين
            $table->timestamp('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for faster queries
            $table->index('user_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
