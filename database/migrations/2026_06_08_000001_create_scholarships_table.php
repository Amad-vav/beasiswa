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
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('nama_beasiswa', 200);
            $table->text('url_tautan');
            $table->string('penyelenggara', 150);
            $table->text('deskripsi')->nullable();
            $table->unsignedTinyInteger('semester_min');
            $table->unsignedTinyInteger('semester_max');
            $table->decimal('ipk_minimum', 3, 2);
            $table->unsignedBigInteger('batas_penghasilan');
            $table->unsignedTinyInteger('skor_status_c2');
            $table->dateTime('batas_waktu');
            $table->unsignedInteger('jumlah_klik')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
