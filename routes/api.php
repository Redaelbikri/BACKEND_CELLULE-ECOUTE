<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Ai\EmotionAnalysisController;
use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Chat\ChatAttachmentController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Event\EventRegistrationController;
use App\Http\Controllers\Feedback\FeedbackController;
use App\Http\Controllers\FollowUp\FollowUpPlanController;
use App\Http\Controllers\FollowUp\MoodJournalController;
use App\Http\Controllers\FollowUp\PersonalGoalController;
use App\Http\Controllers\Resource\EducationalResourceController;
use App\Http\Controllers\Resource\RecommendedResourceController;
use App\Http\Controllers\Resource\SavedResourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google/mobile', [GoogleAuthController::class, 'mobileLogin']);
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);

    Route::middleware('jwt')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::post('/ai/chat', [EmotionAnalysisController::class, 'chat']);

Route::middleware('jwt')->group(function () {
    Route::get('/users/counselors', [UserController::class, 'counselors']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/users/counselors', [UserController::class, 'counselors']);
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::patch('/users/{id}/activate', [UserController::class, 'activate']);
        Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate']);

        Route::get('/events', [EventController::class, 'adminIndex']);
        Route::post('/events', [EventController::class, 'store']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
        Route::patch('/events/{id}/cancel', [EventController::class, 'cancel']);
        Route::patch('/events/{id}/complete', [EventController::class, 'complete']);
        Route::get('/events/{id}/registrations', [EventRegistrationController::class, 'adminRegistrations']);

        Route::get('/emotion-analyses', [EmotionAnalysisController::class, 'adminIndex']);

        Route::get('/resources', [EducationalResourceController::class, 'adminIndex']);
        Route::post('/resources', [EducationalResourceController::class, 'store']);
        Route::get('/resources/{id}', [EducationalResourceController::class, 'show']);
        Route::put('/resources/{id}', [EducationalResourceController::class, 'update']);
        Route::delete('/resources/{id}', [EducationalResourceController::class, 'destroy']);
        Route::patch('/resources/{id}/publish', [EducationalResourceController::class, 'publish']);
        Route::patch('/resources/{id}/unpublish', [EducationalResourceController::class, 'unpublish']);
    });

    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/events', [EventController::class, 'studentIndex']);
        Route::post('/events/{id}/register', [EventRegistrationController::class, 'register']);
        Route::patch('/events/{id}/cancel-registration', [EventRegistrationController::class, 'cancelRegistration']);
        Route::get('/my-events', [EventController::class, 'studentMyEvents']);

        Route::get('/resources', [EducationalResourceController::class, 'studentIndex']);
        Route::get('/resources/{id}', [EducationalResourceController::class, 'show']);
        Route::post('/resources/{id}/save', [SavedResourceController::class, 'save']);
        Route::delete('/resources/{id}/unsave', [SavedResourceController::class, 'unsave']);
        Route::get('/saved-resources', [SavedResourceController::class, 'index']);
        Route::get('/recommended-resources', [RecommendedResourceController::class, 'studentIndex']);

        Route::get('/mood-journal', [MoodJournalController::class, 'studentIndex']);
        Route::post('/mood-journal', [MoodJournalController::class, 'store']);
        Route::put('/mood-journal/{id}', [MoodJournalController::class, 'update']);

        Route::get('/goals', [PersonalGoalController::class, 'studentIndex']);
        Route::post('/goals', [PersonalGoalController::class, 'store']);
        Route::get('/goals/{id}', [PersonalGoalController::class, 'show']);
        Route::put('/goals/{id}', [PersonalGoalController::class, 'update']);
        Route::delete('/goals/{id}', [PersonalGoalController::class, 'destroy']);
        Route::patch('/goals/{id}/status', [PersonalGoalController::class, 'updateStatus']);

        Route::get('/follow-up-plans', [FollowUpPlanController::class, 'studentIndex']);
    });

    Route::prefix('counselor')->middleware('role:counselor')->group(function () {
        Route::get('/events', [EventController::class, 'counselorIndex']);
        Route::get('/events/{id}/registrations', [EventRegistrationController::class, 'counselorRegistrations']);
        Route::get('/students/{studentId}/emotion-analyses', [EmotionAnalysisController::class, 'counselorStudentAnalyses']);
        Route::get('/emotion-alerts', [EmotionAnalysisController::class, 'counselorAlerts']);

        Route::get('/resources', [EducationalResourceController::class, 'counselorIndex']);
        Route::get('/resources/{id}', [EducationalResourceController::class, 'show']);
        Route::post('/students/{studentId}/recommend-resource', [RecommendedResourceController::class, 'recommend']);
        Route::get('/students/{studentId}/recommended-resources', [RecommendedResourceController::class, 'counselorStudentIndex']);
        Route::get('/students/{studentId}/mood-journal', [MoodJournalController::class, 'counselorStudentIndex']);
        Route::get('/students/{studentId}/goals', [PersonalGoalController::class, 'counselorStudentIndex']);
        Route::post('/students/{studentId}/suggest-goal', [PersonalGoalController::class, 'suggest']);
        Route::get('/students/{studentId}/follow-up-plans', [FollowUpPlanController::class, 'counselorStudentIndex']);
        Route::post('/students/{studentId}/follow-up-plans', [FollowUpPlanController::class, 'store']);
        Route::put('/students/{studentId}/follow-up-plans/{id}', [FollowUpPlanController::class, 'update']);
        Route::patch('/students/{studentId}/follow-up-plans/{id}/complete', [FollowUpPlanController::class, 'complete']);
    });

    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('role:student');
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::get('/appointments/{appointmentId}/emotion-analysis', [EmotionAnalysisController::class, 'appointmentAnalysis']);
    Route::get('/messages/{messageId}/emotion-analysis', [EmotionAnalysisController::class, 'messageAnalysis']);
    Route::put('/appointments/{id}', [AppointmentController::class, 'update'])->middleware('role:admin');
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy'])->middleware('role:admin');
    Route::patch('/appointments/{id}/accept', [AppointmentController::class, 'accept'])->middleware('role:counselor');
    Route::patch('/appointments/{id}/reject', [AppointmentController::class, 'reject'])->middleware('role:counselor');
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::patch('/appointments/{id}/complete', [AppointmentController::class, 'complete'])->middleware('role:counselor');

    Route::post('/ai/analyze-text', [EmotionAnalysisController::class, 'analyzeText']);
    Route::post('/ai/analyze-document', [EmotionAnalysisController::class, 'analyzeDocument']);
    Route::post('/chat/attachments', [ChatAttachmentController::class, 'store'])->middleware('role:student,counselor');

    Route::get('/feedbacks', [FeedbackController::class, 'index']);
    Route::post('/feedbacks', [FeedbackController::class, 'store'])->middleware('role:student,counselor');
    Route::get('/feedbacks/{id}', [FeedbackController::class, 'show']);
    Route::delete('/feedbacks/{id}', [FeedbackController::class, 'destroy'])->middleware('role:admin');
});
