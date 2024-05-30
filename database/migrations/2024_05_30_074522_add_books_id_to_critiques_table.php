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
        Schema::table('critiques', function (Blueprint $table) {
            $table->unsignedBigInteger('books_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('critiques', function (Blueprint $table) {
            $table->dropForeign('books_id');
            $table->dropColumn('books_id');

        });
    }
};
