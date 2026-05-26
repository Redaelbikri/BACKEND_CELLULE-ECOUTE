<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Services\Event\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService
    ) {
    }

    public function adminIndex(Request $request)
    {
        return response()->json([
            'data' => EventResource::collection($this->eventService->adminIndex($request->user()))->resolve(),
        ]);
    }

    public function studentIndex(Request $request)
    {
        return response()->json([
            'data' => EventResource::collection($this->eventService->studentIndex($request->user()))->resolve(),
        ]);
    }

    public function studentMyEvents(Request $request)
    {
        return response()->json([
            'data' => EventResource::collection($this->eventService->studentMyEvents($request->user()))->resolve(),
        ]);
    }

    public function counselorIndex(Request $request)
    {
        return response()->json([
            'data' => EventResource::collection($this->eventService->counselorIndex($request->user()))->resolve(),
        ]);
    }

    public function store(CreateEventRequest $request)
    {
        return response()->json([
            'data' => EventResource::make($this->eventService->create($request->user(), $request->validated()))->resolve(),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        return response()->json([
            'data' => EventResource::make($this->eventService->show($request->user(), $id))->resolve(),
        ]);
    }

    public function update(UpdateEventRequest $request, int $id)
    {
        return response()->json([
            'data' => EventResource::make($this->eventService->update($request->user(), $id, $request->validated()))->resolve(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->eventService->delete($request->user(), $id);

        return response()->json([
            'message' => 'Event deleted successfully.',
        ]);
    }

    public function cancel(Request $request, int $id)
    {
        return response()->json([
            'data' => EventResource::make($this->eventService->cancel($request->user(), $id))->resolve(),
        ]);
    }

    public function complete(Request $request, int $id)
    {
        return response()->json([
            'data' => EventResource::make($this->eventService->complete($request->user(), $id))->resolve(),
        ]);
    }
}
