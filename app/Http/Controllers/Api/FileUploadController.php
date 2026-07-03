<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    private array $allowedMimes = [
        'images' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'videos' => ['video/mp4', 'video/mpeg', 'video/quicktime'],
        'documents' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'avatars' => ['image/jpeg', 'image/png', 'image/webp'],
    ];

    private array $allowedExtensions = [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'videos' => ['mp4', 'mpeg', 'mov'],
        'documents' => ['pdf', 'doc', 'docx'],
        'avatars' => ['jpg', 'jpeg', 'png', 'webp'],
    ];

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'folder' => 'nullable|string|in:images,videos,documents,avatars',
        ]);

        $file = $request->file('file');
        $folder = $request->get('folder', 'images');
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($mime, $this->allowedMimes[$folder] ?? [])) {
            abort(422, 'File type not allowed for this folder.');
        }
        if (!in_array($extension, $this->allowedExtensions[$folder] ?? [])) {
            abort(422, 'File extension not allowed.');
        }

        $filename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs($folder, $filename, 'public_uploads');

        return response()->json([
            'url' => '/uploads/' . $folder . '/' . $filename,
            'path' => $path,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => $file->getSize(),
        ], 201);
    }

    public function uploadMultiple(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:51200',
            'folder' => 'nullable|string|in:images,videos,documents,avatars',
        ]);

        $folder = $request->get('folder', 'images');
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $mime = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($mime, $this->allowedMimes[$folder] ?? [])) {
                abort(422, 'File type not allowed for this folder.');
            }
            if (!in_array($extension, $this->allowedExtensions[$folder] ?? [])) {
                abort(422, 'File extension not allowed.');
            }

            $filename = Str::uuid() . '.' . $extension;
            $path = $file->storeAs($folder, $filename, 'public_uploads');

            $uploaded[] = [
                'url' => '/uploads/' . $folder . '/' . $filename,
                'path' => $path,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size' => $file->getSize(),
            ];
        }

        return response()->json(['files' => $uploaded], 201);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'path' => 'required|string|max:500',
        ]);

        $path = $request->input('path');
        $path = str_replace(['../', '..\\', '%2e%2e', '%252e%252e', '..'], '', $path);

        if (str_starts_with($path, '/uploads/')) {
            $fullPath = public_path($path);
        } else {
            $fullPath = public_path('uploads/' . $path);
        }

        $uploadsDir = realpath(public_path('uploads'));
        $realPath = realpath($fullPath);

        if ($realPath === false || !$uploadsDir || !str_starts_with($realPath, $uploadsDir)) {
            abort(403, 'Invalid file path.');
        }

        if (file_exists($realPath)) {
            unlink($realPath);
            return response()->json(['message' => 'File deleted']);
        }

        return response()->json(['message' => 'File not found'], 404);
    }
}
