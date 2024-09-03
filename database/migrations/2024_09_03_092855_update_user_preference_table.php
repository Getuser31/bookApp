<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn('language');
            $table->unsignedBigInteger('default_language_id')->nullable();
            $table->foreign('default_language_id')->references('id')->on('default_language')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['default_language_id']);
            $table->dropColumn('default_language_id');

            // Re-add the language column
            $table->string('language')->nullable();
        });
    }
};
