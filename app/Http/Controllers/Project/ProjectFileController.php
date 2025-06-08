<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectFileStoreRequest;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProjectFileController extends Controller
{
    function index(Request $request, $project_id)
    {
        $project = Project::findOrFail($project_id);
        $user = Auth::user();

        if ($project::visible($user)) {
            $files = $project->files()->with(['user'])->get();
            return response()->json(['files' => $files], 200);
        } else {
            return response()->json(['message' => 'У вас нет прав на просмотр файлов этого проекта'], 403);
        }
    }

    function upload(Request $request, FileService $fileService)
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:pdf,docx,doc,dot,xlsx,xls,csv'],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $project = Project::find($request['project_id']);

        if ($request->user()->cannot('storeFiles', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        error_log($project);

        $user = request()->user();

        $file = $request->file('file');

        try {
            $fileService->store($user, $file, $project->id);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['message' => 'Файл загружен успешно'], 200);
    }

    function destroy(Request $request, FileService $fileService) {
        $validator = Validator::make($request->all(), [
            'file_id' => ['required', 'integer', 'exists:files,id'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = ProjectFile::findOrFail($request['file_id']);
        $user = $request->user();

        if (Gate::denies('can-edit-project', [$file->project, $user])) {
            return response()->json(['message' => 'У вас нет прав на удаление этого файла'], 403);
        }

        try {
            $fileService->delete($user, $request['file_id']);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['message' => 'Файл удалён успешно'], 200);
    }

    function download(Request $request, $file_id)
    {
        $file = ProjectFile::findOrFail($file_id);

        $user = Auth::user();

        if (Gate::denies('can-edit-project', [$file->project, $user])) {
            return response()->json(['message' => 'У вас нет прав на загрузку этого файла'], 403);
        }

        try {
            return Storage::disk('project-files')->download($file->s3_key, $file->original_filename);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 500);
        }
    }

}
