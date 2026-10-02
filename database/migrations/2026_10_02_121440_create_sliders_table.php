<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title_en')->nullable();
            $table->string('title_gu')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->string('subtitle_gu')->nullable();
            $table->string('image');
            $table->string('link_type')->default('none'); // 'none', 'category', 'subcategory', 'product', 'offer', 'custom'
            $table->string('link_url')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('badge_en')->nullable();
            $table->string('badge_gu')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
