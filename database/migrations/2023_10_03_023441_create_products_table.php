<?php

use App\Models\Enum\ProductStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code');
            $table->enum('status', ProductStatus::values());
            $table->timestamp('imported_t');
            $table->string('url', 512);
            $table->string('creator');
            $table->integer('created_t');
            $table->integer('last_modified_t');
            $table->string('product_name');
            $table->string('quantity');
            $table->string('brands');
            $table->string('categories', 512);
            $table->string('labels');
            $table->string('cities');
            $table->string('purchase_places');
            $table->string('stores');
            $table->text('ingredients_text');
            $table->string('traces');
            $table->string('serving_size');
            $table->double('serving_quantity')->nullable();
            $table->double('nutriscore_score')->nullable();
            $table->string('nutriscore_grade', 1);
            $table->string('main_category');
            $table->string('image_url', 512);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
