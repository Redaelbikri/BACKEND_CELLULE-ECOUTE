<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\RecommendResourceRequest;
use App\Http\Resources\RecommendedResourceResource;
use App\Services\Resource\RecommendedResourceService;
use Illuminate\Http\Request;

class RecommendedResourceController extends Controller
{
    public function __construct(
        private readonly RecommendedResourceService $recommendedResourceService
    ) {
    }

    public function studentIndex(Request $request)
    {
        return response()->json([
            'data' => RecommendedResourceResource::collection(
                $this->recommendedResourceService->studentIndex($request->user())
            )->resolve(),
        ]);
    }

    public function counselorStudentIndex(Request $request, int $studentId)
    {
        return response()->json([
            'data' => RecommendedResourceResource::collection(
                $this->recommendedResourceService->counselorStudentIndex($request->user(), $studentId)
            )->resolve(),
        ]);
    }

    public function recommend(RecommendResourceRequest $request, int $studentId)
    {
        return response()->json([
            'data' => RecommendedResourceResource::make($this->recommendedResourceService->recommend(
                $request->user(),
                $studentId,
                $request->validated()
            ))->resolve(),
            'message' => 'Resource recommended successfully.',
        ], 201);
    }
}
