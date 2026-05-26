<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emotion_analyses', function (Blueprint $table) {
            $table->string('title')->nullable()->after('original_text');
            $table->string('main_emotion')->nullable()->after('emotional_state');
            $table->json('possible_causes')->nullable()->after('key_signals');
            $table->text('suggested_response')->nullable()->after('recommendation');
            $table->text('risk_level_explanation')->nullable()->after('suggested_action');
            $table->index(['student_id', 'created_at'], 'emotion_analyses_student_created_at_idx');
            $table->index(['counselor_id', 'urgency_level'], 'emotion_analyses_counselor_urgency_idx');
            $table->index('message_id');
            $table->index('source_type');
        });
    }

    public function down(): void
    {
        Schema::table('emotion_analyses', function (Blueprint $table) {
            $table->dropIndex('emotion_analyses_student_created_at_idx');
            $table->dropIndex('emotion_analyses_counselor_urgency_idx');
            $table->dropIndex(['message_id']);
            $table->dropIndex(['source_type']);
            $table->dropColumn([
                'title',
                'main_emotion',
                'possible_causes',
                'suggested_response',
                'risk_level_explanation',
            ]);
        });
    }
};
