<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationalResourceResource;
use App\Services\Resource\SavedResourceService;
use Illuminate\Http\Request;

class SavedResourceController extends Controller
{
    public function __construct(
        private readonly SavedResourceService $savedResourceService
    ) {
    }

    public function index(Request $request)
    {
        $savedResources = $this->savedResourceService->index($request->user());

        return response()->json([
            'data' => EducationalResourceResource::collection($savedResources->pluck('resource'))->resolve(),
        ]);
    }

    public function save(Request $request, int $id)
    {
        return response()->json([
            'data' => EducationalResourceResource::make($this->savedResourceService->save($request->user(), $id)->resource)->resolve(),
            'message' => 'Resource saved successfully.',
        ]);
    }

    public function unsave(Request $request, int $id)
    {
        $this->savedResourceService->unsave($request->user(), $id);

        return response()->json(['message' => 'Resource removed from saved resources.']);
    }
}
