<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmotionAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'appointment_id',
        'message_id',
        'source_type',
        'original_text',
        'title',
        'emotional_state',
        'main_emotion',
        'emotion',
        'sentiment',
        'urgency_level',
        'problem_type',
        'summary',
        'key_signals',
        'possible_causes',
        'recommendation',
        'suggested_response',
        'suggested_action',
        'risk_level_explanation',
        'confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'float',
            'key_signals' => 'array',
            'possible_causes' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
