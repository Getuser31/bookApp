<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBookRatingTableAndDeleteRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('book_rating', function (Blueprint $table) {
            // Remove the foreign key constraint and the rating_id column
            $table->dropForeign(['rating_id']);
            $table->dropColumn('rating_id');

            // Add the new rating column
            $table->integer('rating')->between(0, 10)->after('user_id'); // add the column after user_id for better readability
        });

        // Drop the rating table
        Schema::dropIfExists('ratings');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate the ratings table
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('rating');
            $table->timestamps();

            // Define foreign key constraints if needed
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('book_rating', function (Blueprint $table) {
            // Remove the new rating column
            $table->dropColumn('rating');

            // Re-add the rating_id column
            $table->unsignedBigInteger('rating_id')->after('user_id');

            // Re-add the foreign key constraint
            $table->foreign('rating_id')->references('id')->on('ratings')->onDelete('cascade');
        });
    }
}
