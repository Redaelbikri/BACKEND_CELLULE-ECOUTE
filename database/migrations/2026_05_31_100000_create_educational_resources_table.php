<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educational_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('type');
            $table->text('description');
            $table->longText('content');
            $table->unsignedSmallInteger('reading_time')->default(3);
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('saved_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('educational_resource_id')->constrained('educational_resources')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'educational_resource_id']);
        });

        Schema::create('recommended_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('educational_resource_id')->constrained('educational_resources')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommended_resources');
        Schema::dropIfExists('saved_resources');
        Schema::dropIfExists('educational_resources');
    }
};
