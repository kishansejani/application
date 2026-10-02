<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->default('Home'); // Home, Work, Other
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('house_no')->nullable(); // Flat/House/Building
            $table->string('street_address'); // Street / Locality
            $table->string('landmark')->nullable();
            $table->string('city')->default('Ahmedabad');
            $table->string('state')->default('Gujarat');
            $table->string('pincode');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('formatted_address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
