<?php

namespace App\Http\Controllers\FollowUp;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowUp\CreateMoodJournalRequest;
use App\Http\Requests\FollowUp\UpdateMoodJournalRequest;
use App\Http\Resources\MoodJournalResource;
use App\Services\FollowUp\MoodJournalService;
use Illuminate\Http\Request;

class MoodJournalController extends Controller
{
    public function __construct(
        private readonly MoodJournalService $moodJournalService
    ) {
    }

    public function studentIndex(Request $request)
    {
        return response()->json([
            'data' => MoodJournalResource::collection($this->moodJournalService->studentIndex($request->user()))->resolve(),
        ]);
    }

    public function store(CreateMoodJournalRequest $request)
    {
        return response()->json([
            'data' => MoodJournalResource::make($this->moodJournalService->store(
                $request->user(),
                $request->validated()
            ))->resolve(),
        ], 201);
    }

    public function update(UpdateMoodJournalRequest $request, int $id)
    {
        return response()->json([
            'data' => MoodJournalResource::make($this->moodJournalService->update(
                $request->user(),
                $id,
                $request->validated()
            ))->resolve(),
        ]);
    }

    public function counselorStudentIndex(Request $request, int $studentId)
    {
        return response()->json([
            'data' => MoodJournalResource::collection(
                $this->moodJournalService->counselorStudentIndex($request->user(), $studentId)
            )->resolve(),
        ]);
    }
}
