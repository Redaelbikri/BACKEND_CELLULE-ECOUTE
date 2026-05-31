<?php

namespace App\Http\Controllers\FollowUp;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowUp\CreateFollowUpPlanRequest;
use App\Http\Requests\FollowUp\UpdateFollowUpPlanRequest;
use App\Http\Resources\FollowUpPlanResource;
use App\Services\FollowUp\FollowUpPlanService;
use Illuminate\Http\Request;

class FollowUpPlanController extends Controller
{
    public function __construct(
        private readonly FollowUpPlanService $planService
    ) {
    }

    public function studentIndex(Request $request)
    {
        return response()->json([
            'data' => FollowUpPlanResource::collection($this->planService->studentIndex($request->user()))->resolve(),
        ]);
    }

    public function counselorStudentIndex(Request $request, int $studentId)
    {
        return response()->json([
            'data' => FollowUpPlanResource::collection(
                $this->planService->counselorStudentIndex($request->user(), $studentId)
            )->resolve(),
        ]);
    }

    public function store(CreateFollowUpPlanRequest $request, int $studentId)
    {
        return response()->json([
            'data' => FollowUpPlanResource::make($this->planService->store(
                $request->user(),
                $studentId,
                $request->validated()
            ))->resolve(),
        ], 201);
    }

    public function update(UpdateFollowUpPlanRequest $request, int $studentId, int $id)
    {
        return response()->json([
            'data' => FollowUpPlanResource::make($this->planService->update(
                $request->user(),
                $studentId,
                $id,
                $request->validated()
            ))->resolve(),
        ]);
    }

    public function complete(Request $request, int $studentId, int $id)
    {
        return response()->json([
            'data' => FollowUpPlanResource::make($this->planService->complete(
                $request->user(),
                $studentId,
                $id
            ))->resolve(),
        ]);
    }
}
