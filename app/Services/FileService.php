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
            $file_content = $file->get();

            $task = Redis::lpush('file-tasks-requests', json_encode([
                'user_id' => $user->id,
                'project_id' => $project_id,
                'object_keys' => [$file_objectKey],
            ], JSON_UNESCAPED_SLASHES));

            $new_file = $user->files()->create([
                's3_key' => $file_objectKey,
                'original_filename' => $file->getClientOriginalName(),
                'project_id' => $project_id,
            ]);

            $new_file->contents = $file_content;  // ->content saves the file to s3
        } catch (\Exception $e) {
            throw new \Exception('Не получилось сохранить файл: ' . $e->getMessage(), 500);
        }
    }

    public function delete(Authenticatable $user, $file_id) {
        try {
            $file = $user->files()->findOrFail($file_id);
            $file->delete();
        } catch (\Exception $e) {
            throw new \Exception('Не получилось удалить файл: ' . $e->getMessage(), 500);
        }

        return response()->json(['message' => 'Файл удалён успешно.'], 200);
    }

    public function readmeUpdate(Authenticatable $user, $project_id, string $readme_content) {
        try {
            $project = $user->projects()->findOrFail($project_id);
            if ($user->cannot('update', $project)) {
                throw new \Exception('У вас нет прав на редактирование этого проекта', 403);
            }

            $file_objectKey = "project{$project_id}/README.md";
            $project->files()->updateOrCreate([
                's3_key' => $file_objectKey,
                'original_filename' => 'README.md',
                'user_id' => $user->id,
                'project_id' => $project_id,
            ])->contents = $readme_content; // ->content saves the file to s3
        } catch (\Exception $e) {
            throw new \RuntimeException('Не удалось отредактировать README: ' . $e->getMessage(), 500);
        }
    }
}
