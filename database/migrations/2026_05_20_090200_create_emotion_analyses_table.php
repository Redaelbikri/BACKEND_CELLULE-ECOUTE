<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emotion_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->cascadeOnDelete();
            $table->string('message_id')->nullable();
            $table->string('source_type');
            $table->text('original_text');
            $table->string('emotion');
            $table->string('sentiment');
            $table->string('urgency_level');
            $table->string('problem_type');
            $table->text('summary');
            $table->text('recommendation');
            $table->decimal('confidence_score', 4, 2)->default(0.50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emotion_analyses');
    }
};
