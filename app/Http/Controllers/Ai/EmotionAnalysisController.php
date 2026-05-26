<?php

namespace App\Http\Controllers\Ai;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\AnalyzeDocumentRequest;
use App\Http\Requests\Ai\AnalyzeTextRequest;
use App\Http\Resources\EmotionAnalysisResource;
use App\Services\Ai\AiProviderService;
use App\Services\Ai\EmotionAnalysisService;
use Illuminate\Http\Request;

class EmotionAnalysisController extends Controller
{
    public function __construct(
        private readonly EmotionAnalysisService $emotionAnalysisService,
        private readonly AiProviderService $aiProviderService
    ) {
    }

    public function analyzeText(AnalyzeTextRequest $request)
    {
        $analysis = $this->emotionAnalysisService->analyzeText($request->user(), $request->validated());

        if ($request->user()?->role === RoleEnum::STUDENT->value) {
            return response()->json([
                'message' => 'Analysis stored successfully.',
            ], 202);
        }

        return response()->json([
            'data' => EmotionAnalysisResource::make($analysis)->resolve(),
        ], 201);
    }

    public function analyzeDocument(AnalyzeDocumentRequest $request)
    {
        $analysis = $this->emotionAnalysisService->analyzeDocument($request->user(), $request->validated());

        if ($request->user()?->role === RoleEnum::STUDENT->value) {
            return response()->json([
                'message' => 'Document analysis stored successfully.',
            ], 202);
        }

        return response()->json([
            'data' => EmotionAnalysisResource::make($analysis)->resolve(),
        ], 201);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json([
            'data' => [
                'response' => $this->aiProviderService->chat($request->input('message')),
            ],
        ]);
    }

    public function counselorStudentAnalyses(Request $request, int $studentId)
    {
        return response()->json([
            'data' => EmotionAnalysisResource::collection(
                $this->emotionAnalysisService->getStudentAnalyses($request->user(), $studentId)
            )->resolve(),
        ]);
    }

    public function appointmentAnalysis(Request $request, int $appointmentId)
    {
        $analysis = $this->emotionAnalysisService->getAppointmentAnalysis($request->user(), $appointmentId);

        return response()->json([
            'data' => $analysis ? EmotionAnalysisResource::make($analysis)->resolve() : null,
        ]);
    }

    public function messageAnalysis(Request $request, string $messageId)
    {
        $analysis = $this->emotionAnalysisService->getMessageAnalysis($request->user(), $messageId);

        return response()->json([
            'data' => $analysis ? EmotionAnalysisResource::make($analysis)->resolve() : null,
        ]);
    }

    public function counselorAlerts(Request $request)
    {
        return response()->json([
            'data' => EmotionAnalysisResource::collection(
                $this->emotionAnalysisService->getCounselorAlerts($request->user())
            )->resolve(),
        ]);
    }

    public function adminIndex(Request $request)
    {
        return response()->json([
            'data' => EmotionAnalysisResource::collection(
                $this->emotionAnalysisService->getAdminAnalyses($request->user())
            )->resolve(),
        ]);
    }
}
