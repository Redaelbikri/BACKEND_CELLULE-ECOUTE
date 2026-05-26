<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studentAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'student_id');
    }

    public function counselorAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'counselor_id');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function assignedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'counselor_id');
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'student_id');
    }

    public function authoredFeedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'author_id');
    }

    public function receivedFeedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'target_id');
    }

    public function emotionAnalyses(): HasMany
    {
        return $this->hasMany(EmotionAnalysis::class, 'student_id');
    }
}
