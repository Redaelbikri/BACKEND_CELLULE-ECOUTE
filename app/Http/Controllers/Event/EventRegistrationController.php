<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\RegisterEventRequest;
use App\Http\Resources\EventRegistrationResource;
use App\Http\Resources\EventResource;
use App\Services\Event\EventRegistrationService;
use App\Services\Event\EventService;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function __construct(
        private readonly EventRegistrationService $eventRegistrationService,
        private readonly EventService $eventService
    ) {
    }

    public function register(RegisterEventRequest $request, int $id)
    {
        return response()->json([
            'data' => EventRegistrationResource::make($this->eventRegistrationService->register($request->user(), $id))->resolve(),
        ], 201);
    }

    public function cancelRegistration(Request $request, int $id)
    {
        return response()->json([
            'data' => EventRegistrationResource::make($this->eventRegistrationService->cancel($request->user(), $id))->resolve(),
        ]);
    }

    public function adminRegistrations(Request $request, int $id)
    {
        return response()->json([
            'data' => EventResource::make($this->eventService->adminRegistrations($request->user(), $id))->resolve(),
        ]);
    }

    public function counselorRegistrations(Request $request, int $id)
    {
        return response()->json([
            'data' => EventResource::make($this->eventService->counselorRegistrations($request->user(), $id))->resolve(),
        ]);
    }
}
