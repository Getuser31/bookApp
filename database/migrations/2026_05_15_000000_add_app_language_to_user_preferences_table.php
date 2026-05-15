<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->string('app_language', 5)->default('en')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn('app_language');
        });
    }
};
