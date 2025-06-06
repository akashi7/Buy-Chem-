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
        Schema::create('registration_wizards', function (Blueprint $table) {
            $table->id();
            $table->string('unique_identifier')->unique();
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->unique();
            $table->string('nationality')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('apartment_name')->nullable();
            $table->string('room_number')->nullable();
            $table->boolean('is_expatriate')->default(false);
            $table->boolean('two_factor_verified')->default(false);
            $table->string('password')->nullable();
            $table->integer('current_step')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_wizards');
    }
};
