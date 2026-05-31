<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'appointment_id',
        'title',
        'objective',
        'actions',
        'next_step',
        'next_follow_up_date',
        'status',
    ];

    protected $casts = [
        'next_follow_up_date' => 'date',
    ];

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
