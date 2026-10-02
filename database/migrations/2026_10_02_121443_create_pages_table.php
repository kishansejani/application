<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // 'about-us', 'legal-information', 'privacy-policy', 'terms'
            $table->string('title_en');
            $table->string('title_gu');
            $table->longText('content_en');
            $table->longText('content_gu');
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_gu')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_gu')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
