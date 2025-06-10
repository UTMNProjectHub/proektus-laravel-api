<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProjectReadmeController extends Controller
{
    function index(Request $request, $project_id)
    {
        $project = \App\Models\Project::findOrFail($project_id);

        $user = $request->user();

        if (!$user) {
            if ($project->privacy === 'private') {
                return response()->json([
                    'error' => 'У вас нет доступа к этому проекту',
                ], 403);
            }
            return response()->json($project->readme, 200);
        }

        try {
            if (!$project::visible($user)) {
                return response()->json([
                    'error' => 'У вас нет доступа к этому проекту',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'readme' => $project->readme,
        ], 200);
    }

    function update(Request $request, $project_id, FileService $fileService)
    {
        $project = \App\Models\Project::findOrFail($project_id);
        $user = $request->user();

        try {
            if (!$project::visible($user)) {
                return response()->json([
                    'error' => 'У вас нет доступа к этому проекту',
                ], 403);
            }

            if ($user->cannot('update', $project)) {
                return response()->json([
                    'error' => 'У вас нет прав для редактирования этого проекта',
                ], 403);
            }

            $data = $request->validate([
                'readme' => 'required|string',
            ]);

            $fileService->readmeUpdate($user, $project_id, $request->get('readme'));

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Readme обновлён успешно',
        ], 200);
    }
}
