<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectUserController extends Controller
{
    function index(Request $request, $project_id)
    {
        $project = Project::find($project_id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $users = $project->users;

        return response()->json([
            'users' => $users,
        ], 200);
    }

    function store(Request $request, $project_id)
    {
        Gate::authorize('can-edit-project', [$project_id]);

        $project = Project::find($project_id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $user_id = $request->input('users');

        if (!$user_id) {
            return response()->json(['error' => 'User ID are required'], 422);
        }

        foreach ($user_id as $id) {
            $project->users()->attach($id);
        }

        return response()->json([
            'message' => 'User added to project successfully',
        ], 200);
    }
}
