<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\UploadChatAttachmentRequest;
use App\Services\Chat\ChatAttachmentService;

class ChatAttachmentController extends Controller
{
    public function __construct(
        private readonly ChatAttachmentService $chatAttachmentService
    ) {
    }

    public function store(UploadChatAttachmentRequest $request)
    {
        return response()->json([
            'data' => $this->chatAttachmentService->upload($request->file('file')),
        ], 201);
    }
}
