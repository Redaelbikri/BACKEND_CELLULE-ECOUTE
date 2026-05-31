<?php

namespace App\Providers;

use App\Repositories\Eloquent\AppointmentRepository;
use App\Repositories\Eloquent\EmotionAnalysisRepository;
use App\Repositories\Eloquent\EducationalResourceRepository;
use App\Repositories\Eloquent\EventRegistrationRepository;
use App\Repositories\Eloquent\EventRepository;
use App\Repositories\Eloquent\FeedbackRepository;
use App\Repositories\Eloquent\FollowUpPlanRepository;
use App\Repositories\Eloquent\MoodJournalRepository;
use App\Repositories\Eloquent\PersonalGoalRepository;
use App\Repositories\Eloquent\RecommendedResourceRepository;
use App\Repositories\Eloquent\SavedResourceRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\EmotionAnalysisRepositoryInterface;
use App\Repositories\Interfaces\EducationalResourceRepositoryInterface;
use App\Repositories\Interfaces\EventRegistrationRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Interfaces\FeedbackRepositoryInterface;
use App\Repositories\Interfaces\FollowUpPlanRepositoryInterface;
use App\Repositories\Interfaces\MoodJournalRepositoryInterface;
use App\Repositories\Interfaces\PersonalGoalRepositoryInterface;
use App\Repositories\Interfaces\RecommendedResourceRepositoryInterface;
use App\Repositories\Interfaces\SavedResourceRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AppointmentRepositoryInterface::class, AppointmentRepository::class);
        $this->app->bind(FeedbackRepositoryInterface::class, FeedbackRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventRegistrationRepositoryInterface::class, EventRegistrationRepository::class);
        $this->app->bind(EmotionAnalysisRepositoryInterface::class, EmotionAnalysisRepository::class);
        $this->app->bind(EducationalResourceRepositoryInterface::class, EducationalResourceRepository::class);
        $this->app->bind(SavedResourceRepositoryInterface::class, SavedResourceRepository::class);
        $this->app->bind(RecommendedResourceRepositoryInterface::class, RecommendedResourceRepository::class);
        $this->app->bind(MoodJournalRepositoryInterface::class, MoodJournalRepository::class);
        $this->app->bind(PersonalGoalRepositoryInterface::class, PersonalGoalRepository::class);
        $this->app->bind(FollowUpPlanRepositoryInterface::class, FollowUpPlanRepository::class);
    }
}
