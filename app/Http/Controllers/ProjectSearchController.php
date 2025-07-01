<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectSearchController extends Controller
{
    function searchByCos(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer|exists:projects,id',
            'limit' => 'required|integer|min:1|max:12',
        ]);

        // 1. Задаём параметры:
        // $targetId — ID проекта, для которого ищем похожие.
        // $limit — сколько похожих проектов вернуть.

        // 2. Выполняем запрос к функции get_similar_projects:
        // возвращает столбцы project_id, project_title, distance
        $similarProjects = DB::select(
            'SELECT project_id, project_title, distance FROM get_similar_projects(?, ?)', [$request->targetId, $request->limit]);

        return response()->json($similarProjects, 200);
    }

    function searchByTag(Request $request)
    {
        $request->validate([
            'tags' => 'required|array',
        ]);
    }
}
