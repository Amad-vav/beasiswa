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
        Schema::create('premium_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscriber_type');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('institution_name')->nullable();
            $table->string('paket_langganan');
            $table->decimal('harga_bulanan', 12, 2);
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->boolean('is_active')->default(true);
            $table->foreignId('featured_scholarship_id')->nullable()->constrained('scholarships')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_subscriptions');
    }
};
