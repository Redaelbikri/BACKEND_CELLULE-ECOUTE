<?php

namespace App\Http\Controllers\FollowUp;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowUp\CreateGoalRequest;
use App\Http\Requests\FollowUp\SuggestGoalRequest;
use App\Http\Requests\FollowUp\UpdateGoalRequest;
use App\Http\Requests\FollowUp\UpdateGoalStatusRequest;
use App\Http\Resources\PersonalGoalResource;
use App\Services\FollowUp\PersonalGoalService;
use Illuminate\Http\Request;

class PersonalGoalController extends Controller
{
    public function __construct(
        private readonly PersonalGoalService $goalService
    ) {
    }

    public function studentIndex(Request $request)
    {
        return response()->json([
            'data' => PersonalGoalResource::collection($this->goalService->studentIndex($request->user()))->resolve(),
        ]);
    }

    public function store(CreateGoalRequest $request)
    {
        return response()->json([
            'data' => PersonalGoalResource::make($this->goalService->store(
                $request->user(),
                $request->validated()
            ))->resolve(),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        return response()->json([
            'data' => PersonalGoalResource::make($this->goalService->show($request->user(), $id))->resolve(),
        ]);
    }

    public function update(UpdateGoalRequest $request, int $id)
    {
        return response()->json([
            'data' => PersonalGoalResource::make($this->goalService->update(
                $request->user(),
                $id,
                $request->validated()
            ))->resolve(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->goalService->delete($request->user(), $id);

        return response()->json(['message' => 'Goal deleted successfully.']);
    }

    public function updateStatus(UpdateGoalStatusRequest $request, int $id)
    {
        return response()->json([
            'data' => PersonalGoalResource::make($this->goalService->updateStatus(
                $request->user(),
                $id,
                $request->validated('status')
            ))->resolve(),
        ]);
    }

    public function counselorStudentIndex(Request $request, int $studentId)
    {
        return response()->json([
            'data' => PersonalGoalResource::collection(
                $this->goalService->counselorStudentIndex($request->user(), $studentId)
            )->resolve(),
        ]);
    }

    public function suggest(SuggestGoalRequest $request, int $studentId)
    {
        return response()->json([
            'data' => PersonalGoalResource::make($this->goalService->suggest(
                $request->user(),
                $studentId,
                $request->validated()
            ))->resolve(),
        ], 201);
    }
}
