<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('location');
        });

        Schema::table('emotion_analyses', function (Blueprint $table) {
            $table->string('emotional_state')->nullable()->after('original_text');
            $table->json('key_signals')->nullable()->after('summary');
            $table->string('suggested_action')->nullable()->after('recommendation');
        });
    }

    public function down(): void
    {
        Schema::table('emotion_analyses', function (Blueprint $table) {
            $table->dropColumn(['emotional_state', 'key_signals', 'suggested_action']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
