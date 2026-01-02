<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarImagesTable extends Migration
{
    /**
     * تشغيل الهجرة.
     */
    public function up()
    {
        Schema::create('car_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id'); // مفتاح أجنبي
            $table->string('image_path'); // مسار الصورة
            $table->timestamps();

            // الربط بجدول السيارات
            $table->foreign('car_id')->references('id')->on('cars')->onDelete('cascade');
        });
    }

    /**
     * التراجع عن الهجرة.
     */
    public function down()
    {
        Schema::dropIfExists('car_images');
    }
}

