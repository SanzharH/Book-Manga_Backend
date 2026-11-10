<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('sku', 64)->unique();
            $table->string('title');
            $table->string('author');
            $table->string('format', 20);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(false);

            $table->index(['is_published', 'category_id']);
            $table->index('price');
            if (DB::connection()->getDriverName() === 'mysql') {
            $table->fullText(['title', 'author']);
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
