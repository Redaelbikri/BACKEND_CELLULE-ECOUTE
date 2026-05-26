<?php

namespace App\Services\Chat;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatAttachmentService
{
    public function upload(UploadedFile $file): array
    {
        $type = $this->resolveMessageType($file);
        $path = $file->store('chat-attachments/'.now()->format('Y/m'), 'public');

        return [
            'type' => $type,
            'file_url' => url(Storage::url($path)),
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_mime_type' => $file->getMimeType(),
        ];
    }

    private function resolveMessageType(UploadedFile $file): string
    {
        $mimeType = Str::lower((string) $file->getMimeType());
        $extension = Str::lower((string) $file->getClientOriginalExtension());

        if (str_starts_with($mimeType, 'image/') || in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return 'image';
        }

        if ($extension === 'pdf' || $mimeType === 'application/pdf') {
            return 'pdf';
        }

        return 'document';
    }
}
