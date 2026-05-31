<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendedResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'educational_resource_id',
        'note',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(EducationalResource::class, 'educational_resource_id');
    }
}
