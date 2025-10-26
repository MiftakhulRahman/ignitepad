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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'private', 'university_only'])->default('public');
            $table->integer('semester')->nullable();
            $table->string('academic_year', 10)->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
