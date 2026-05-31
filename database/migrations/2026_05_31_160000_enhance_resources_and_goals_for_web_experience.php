<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educational_resources', function (Blueprint $table) {
            $table->string('external_url')->nullable()->after('image_path');
            $table->string('embed_url')->nullable()->after('external_url');
            $table->string('video_url')->nullable()->after('embed_url');
            $table->string('source_name')->nullable()->after('video_url');
            $table->json('practical_tips')->nullable()->after('source_name');
            $table->json('checklist')->nullable()->after('practical_tips');
        });

        Schema::table('personal_goals', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('status');
            $table->unsignedBigInteger('created_by')->nullable()->after('suggested_by');
        });
    }

    public function down(): void
    {
        Schema::table('personal_goals', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'priority']);
        });

        Schema::table('educational_resources', function (Blueprint $table) {
            $table->dropColumn([
                'external_url',
                'embed_url',
                'video_url',
                'source_name',
                'practical_tips',
                'checklist',
            ]);
        });
    }
};
