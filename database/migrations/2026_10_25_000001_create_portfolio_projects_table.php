<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_mr')->nullable();
            $table->string('slug')->unique();
            $table->string('category', 50);
            $table->string('location')->nullable();
            $table->string('location_mr')->nullable();
            $table->text('summary');
            $table->text('summary_mr')->nullable();
            $table->longText('description');
            $table->longText('description_mr')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_sample')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_projects');
    }
};
