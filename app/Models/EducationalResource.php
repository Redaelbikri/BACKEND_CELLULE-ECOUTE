<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'type',
        'description',
        'content',
        'reading_time',
        'image_path',
        'external_url',
        'embed_url',
        'video_url',
        'source_name',
        'practical_tips',
        'checklist',
        'is_published',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'reading_time' => 'integer',
        'practical_tips' => 'array',
        'checklist' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function savedResources(): HasMany
    {
        return $this->hasMany(SavedResource::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(RecommendedResource::class);
    }
}
