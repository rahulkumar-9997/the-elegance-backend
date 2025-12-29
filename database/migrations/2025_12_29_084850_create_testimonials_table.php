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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('guest_type')->nullable();
            $table->string('visit_date')->nullable();
            $table->longText('review_text');
            $table->decimal('value_rating', 2, 1)->default(0);
            $table->decimal('rooms_rating', 2, 1)->default(0);
            $table->decimal('location_rating', 2, 1)->default(0);
            $table->decimal('cleanliness_rating', 2, 1)->default(0);
            $table->decimal('service_rating', 2, 1)->default(0);
            $table->decimal('sleep_quality_rating', 2, 1)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
