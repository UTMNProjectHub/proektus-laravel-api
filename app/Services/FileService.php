<?php

namespace App\Services;

use App\Jobs\ProcessProjectFile;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function store(Authenticatable $user, UploadedFile $file, $project_id) {
        $file_uuid = Str::uuid();
        $file_objectKey = "user$user->id/{$file_uuid}.{$file->getClientOriginalExtension()}";

        try {
            $uploaded_file = Storage::disk('project-files')->put($file_objectKey, $file->get(), ['Metadata' => ['original_name' => $file->getClientOriginalName()]]);

            $task = Redis::lpush('file-tasks-requests', json_encode([
                'user_id' => $user->id,
                'project_id' => $project_id,
                'object_keys' => [$file_objectKey],
            ], JSON_UNESCAPED_SLASHES));

            $file = $user->files()->create([
                's3_key' => $file_objectKey,
                'original_filename' => $file->getClientOriginalName(),
                'project_id' => $project_id,
            ]);
        } catch (\Exception $e) {
            if ($uploaded_file) {
                Storage::disk('project-files')->delete($file_objectKey);
            }

            if ($task) {
                $task->delete();
            }

            if ($file) {
                $file->delete();
            }

            throw new \Exception('Failed to store file: ' . $e->getMessage(), 500);


        }
    }

    public function delete(Authenticatable $user, $file_id) {
        try {
            $file = $user->files()->findOrFail($file_id);
            Storage::disk('project-files')->delete($file->s3_key);
            $file->delete();
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete file: ' . $e->getMessage(), 500);
        }

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
