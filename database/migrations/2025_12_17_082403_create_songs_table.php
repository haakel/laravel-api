<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // آپلودکننده
            $table->string('title');
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('album')->nullable();
            $table->foreignId('year_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('genre_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('duration')->default(0); // بر حسب ثانیه
            $table->string('path'); // مسیر ذخیره‌سازی فایل موسیقی
            $table->string('cover_path')->nullable(); // مسیر ذخیره‌سازی کاور
            $table->integer('plays')->default(0); // تعداد پخش
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};