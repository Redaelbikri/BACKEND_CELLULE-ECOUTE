<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\CreateResourceRequest;
use App\Http\Requests\Resource\UpdateResourceRequest;
use App\Http\Resources\EducationalResourceResource;
use App\Services\Resource\EducationalResourceService;
use Illuminate\Http\Request;

class EducationalResourceController extends Controller
{
    public function __construct(
        private readonly EducationalResourceService $resourceService
    ) {
    }

    public function adminIndex(Request $request)
    {
        return response()->json([
            'data' => EducationalResourceResource::collection($this->resourceService->adminIndex(
                $request->user(),
                $request->query('search'),
                $request->query('category')
            ))->resolve(),
        ]);
    }

    public function studentIndex(Request $request)
    {
        return response()->json([
            'data' => EducationalResourceResource::collection($this->resourceService->studentIndex(
                $request->user(),
                $request->query('search'),
                $request->query('category')
            ))->resolve(),
        ]);
    }

    public function counselorIndex(Request $request)
    {
        return response()->json([
            'data' => EducationalResourceResource::collection($this->resourceService->counselorIndex(
                $request->user(),
                $request->query('search'),
                $request->query('category')
            ))->resolve(),
        ]);
    }

    public function store(CreateResourceRequest $request)
    {
        return response()->json([
            'data' => EducationalResourceResource::make($this->resourceService->create(
                $request->user(),
                $request->validated()
            ))->resolve(),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        return response()->json([
            'data' => EducationalResourceResource::make($this->resourceService->show($request->user(), $id))->resolve(),
        ]);
    }

    public function update(UpdateResourceRequest $request, int $id)
    {
        return response()->json([
            'data' => EducationalResourceResource::make($this->resourceService->update(
                $request->user(),
                $id,
                $request->validated()
            ))->resolve(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->resourceService->delete($request->user(), $id);

        return response()->json(['message' => 'Resource deleted successfully.']);
    }

    public function publish(Request $request, int $id)
    {
        return response()->json([
            'data' => EducationalResourceResource::make($this->resourceService->publish($request->user(), $id))->resolve(),
        ]);
    }

    public function unpublish(Request $request, int $id)
    {
        return response()->json([
            'data' => EducationalResourceResource::make($this->resourceService->unpublish($request->user(), $id))->resolve(),
        ]);
    }
}
