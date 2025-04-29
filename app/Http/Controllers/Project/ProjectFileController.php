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

        error_log($project);

        $user = Auth::user();

        if (Gate::allows('can-edit-project', [$project, $user])) {
            $file = $request->file('file');

            try {
                $fileService->store($user, $file, $project->id);
            } catch (\Exception $e) {
                return response()->json([$e->getMessage()], 500);
            }

            return response()->json(['message' => 'File uploaded successfully'], 200);
        } else {
            return response()->json(['message' => 'You do not have permission to upload files to this project'], 403);
        }
    }

    function destroy(Request $request, FileService $fileService) {
        $validator = Validator::make($request->all(), [
            'file_id' => ['required', 'integer', 'exists:project_files,id'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = ProjectFile::findOrFail($request['file_id']);

        $user = Auth::user();

        if (Gate::denies('can-edit-project', [$file->project(), $user])) {
            return response()->json(['message' => 'You do not have permission to delete this file'], 403);
        }

        try {
            $fileService->delete($user, $request['file_id']);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 500);
        }

        return response()->json(['message' => 'File deleted successfully'], 200);
    }

}
