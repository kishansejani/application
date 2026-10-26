<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 360° product view: frames are stored in product_images with type = 'spin'.
     * Normal gallery photos keep type = 'gallery' (the default), so no data changes.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('product_images', 'type')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->string('type', 16)->default('gallery')->after('image_path');
                $table->index(['product_id', 'type', 'sort_order'], 'product_images_product_type_sort_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_images', 'type')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropIndex('product_images_product_type_sort_idx');
                $table->dropColumn('type');
            });
        }
    }
};
