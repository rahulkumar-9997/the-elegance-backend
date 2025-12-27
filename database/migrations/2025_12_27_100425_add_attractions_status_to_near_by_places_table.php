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
        Schema::table('near_by_places', function (Blueprint $table) {
            $table->boolean('attractions_status')
                  ->default(1)
                  ->after('status')
                  ->comment('1 = Active, 0 = Inactive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('near_by_places', function (Blueprint $table) {
            $table->dropColumn('attractions_status');
        });
    }
};
