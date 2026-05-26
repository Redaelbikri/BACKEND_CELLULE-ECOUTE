<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('type');
            $table->date('event_date');
            $table->string('start_time', 5);
            $table->string('end_time', 5);
            $table->string('location');
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('max_participants');
            $table->string('status');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
