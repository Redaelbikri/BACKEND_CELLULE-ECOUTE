<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'mood',
        'note',
        'mood_date',
    ];

    protected $casts = [
        'mood_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
