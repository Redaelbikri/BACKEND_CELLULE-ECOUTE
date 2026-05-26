<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\CreateAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Services\Appointment\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService
    ) {
    }

    public function index(Request $request)
    {
        return response()->json([
            'data' => AppointmentResource::collection($this->appointmentService->index($request->user()))->resolve(),
        ]);
    }

    public function store(CreateAppointmentRequest $request)
    {
        return response()->json([
            'data' => AppointmentResource::make($this->appointmentService->create($request->user(), $request->validated()))->resolve(),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        return response()->json([
            'data' => AppointmentResource::make($this->appointmentService->show($request->user(), $id))->resolve(),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, int $id)
    {
        return response()->json([
            'data' => AppointmentResource::make($this->appointmentService->update($request->user(), $id, $request->validated()))->resolve(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->appointmentService->delete($request->user(), $id);

        return response()->json([
            'message' => 'Appointment deleted successfully.',
        ]);
    }

    public function accept(Request $request, int $id)
    {
        return response()->json([
            'data' => AppointmentResource::make($this->appointmentService->accept($request->user(), $id))->resolve(),
        ]);
    }

    public function reject(Request $request, int $id)
    {
        return response()->json([
            'data' => AppointmentResource::make($this->appointmentService->reject($request->user(), $id))->resolve(),
        ]);
    }

    public function cancel(Request $request, int $id)
    {
        return response()->json([
            'data' => AppointmentResource::make($this->appointmentService->cancel($request->user(), $id))->resolve(),
        ]);
    }

    public function complete(Request $request, int $id)
    {
        return response()->json([
            'data' => AppointmentResource::make($this->appointmentService->complete($request->user(), $id))->resolve(),
        ]);
    }
}
