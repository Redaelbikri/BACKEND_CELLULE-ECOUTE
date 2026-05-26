<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\CreateFeedbackRequest;
use App\Http\Resources\FeedbackResource;
use App\Services\Feedback\FeedbackService;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct(
        private readonly FeedbackService $feedbackService
    ) {
    }

    public function index(Request $request)
    {
        return response()->json([
            'data' => FeedbackResource::collection($this->feedbackService->index($request->user()))->resolve(),
        ]);
    }

    public function store(CreateFeedbackRequest $request)
    {
        return response()->json([
            'data' => FeedbackResource::make($this->feedbackService->create($request->user(), $request->validated()))->resolve(),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        return response()->json([
            'data' => FeedbackResource::make($this->feedbackService->show($request->user(), $id))->resolve(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->feedbackService->delete($request->user(), $id);

        return response()->json([
            'message' => 'Feedback deleted successfully.',
        ]);
    }
}
