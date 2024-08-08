<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropGenreIdForeignKeyAndColumnFromBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['genre_id']);

            // Then drop the genre_id column
            $table->dropColumn('genre_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Add the genre_id column back
            $table->unsignedBigInteger('genre_id')->nullable();

            // Add the foreign key constraint back
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');
        });
    }
}
