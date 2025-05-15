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
        $project = Project::find($project_id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if ($request->user()->cannot('addUser', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
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

    function destroy(Request $request, $project_id)
    {
        $project = Project::find($project_id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if ($request->user()->cannot('removeUser', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user_id = $request->input('users');

        if (!$user_id) {
            return response()->json(['error' => 'User ID are required'], 422);
        }

        foreach ($user_id as $id) {
            $project->users()->detach($id);
        }

        return response()->json([
            'message' => 'User removed from project successfully',
        ], 200);
    }

    function update(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        $user = $request->user();

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if ($user->cannot('updateUser', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string|in:admin,owner,member',
        ]);

        $editableUser = $project->users()->where('user_id', $request->input('user_id'))->first();

        if (!$editableUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->id === $editableUser->user_id) {
            return response()->json(['error' => 'You cannot change your own role'], 403);
        }

        if ($editableUser->pivot->role === 'owner') {
            return response()->json(['error' => 'You cannot change the role of an admin or owner'], 403);
        }

        if ($user->pivot->role === 'admin' && $editableUser->pivot->role === 'admin') {
            return response()->json(['error' => 'You cannot change the role of an admin'], 403);
        }

        try {
            $project->users()->updateExistingPivot($editableUser->user_id, ['role' => $request->input('role')]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user role: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'User role updated successfully',
        ], 200);
    }
}
