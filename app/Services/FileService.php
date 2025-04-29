<?php

namespace App\Services;

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
        error_log($file_objectKey);

        try {
            Storage::disk('project-files')->put($file_objectKey, $file->get(), ['Metadata' => ['original_name' => $file->getClientOriginalName()]]);

            Redis::lpush('file-tasks', json_encode([
                'file_id' => $file_uuid,
                'object_key' => $file_objectKey,
            ], JSON_UNESCAPED_SLASHES));

            $file = $user->files()->create([
                's3_key' => $file_objectKey,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            $file->project()->attach($project_id); // TODO: attach to project, needs testing
        } catch (\Exception $e) {
            throw new \Exception('Failed to store file: ' . $e->getMessage(), 500);
        }
    }
}
