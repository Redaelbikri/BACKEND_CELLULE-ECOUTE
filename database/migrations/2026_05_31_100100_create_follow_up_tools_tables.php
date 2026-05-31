<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('mood');
            $table->text('note')->nullable();
            $table->date('mood_date');
            $table->timestamps();
            $table->unique(['student_id', 'mood_date']);
        });

        Schema::create('personal_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('suggested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->default('autre');
            $table->string('status')->default('todo');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('follow_up_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('title');
            $table->text('objective');
            $table->longText('actions');
            $table->text('next_step')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_plans');
        Schema::dropIfExists('personal_goals');
        Schema::dropIfExists('mood_journals');
    }
};
