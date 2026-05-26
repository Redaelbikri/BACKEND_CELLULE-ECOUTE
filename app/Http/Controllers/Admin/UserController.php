<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    public function index(Request $request)
    {
        return response()->json([
            'data' => UserResource::collection($this->userService->list($request->query('role')))->resolve(),
        ]);
    }

    public function store(CreateUserRequest $request)
    {
        return response()->json([
            'data' => UserResource::make($this->userService->create($request->validated()))->resolve(),
        ], 201);
    }

    public function show(int $id)
    {
        return response()->json([
            'data' => UserResource::make($this->userService->show($id))->resolve(),
        ]);
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        return response()->json([
            'data' => UserResource::make($this->userService->update($id, $request->validated()))->resolve(),
        ]);
    }

    public function destroy(int $id)
    {
        $this->userService->delete($id);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    public function activate(int $id)
    {
        return response()->json([
            'data' => UserResource::make($this->userService->activate($id))->resolve(),
        ]);
    }

    public function deactivate(int $id)
    {
        return response()->json([
            'data' => UserResource::make($this->userService->deactivate($id))->resolve(),
        ]);
    }

    public function counselors()
    {
        return response()->json([
            'data' => UserResource::collection($this->userService->counselors())->resolve(),
        ]);
    }
}
