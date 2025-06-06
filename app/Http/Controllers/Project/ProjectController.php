<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    // список проектов с пагинацией
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 8);
        $user = Auth::user();


        if ($user) {
            $projects = Project::visible($user);
        } else {
            return response()->json(Project::where('privacy', 'public'), 200);
        }

        if ($request->has('user')) {
            $projects->whereHas('users', function ($query) use ($request) {
                $query->where('user_id', $request->input('user'));
            });
        }

        try {
            $projects = $projects->paginate($per_page);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось получить проекты ' . $e->getMessage()], 500);
        }

        return response()->json($projects, 200);
    }

//    public function indexByUser($page = null)
//    {
//        Auth::guard()->user();
//
//        try {
//            $projects = Auth::user()->projects()->paginate(10);
//        } catch (\Exception $e) {
//            return response()->json(['error' => 'Failed to retrieve projects: ' . $e->getMessage()], 500);
//        }
//
//        return response()->json($projects, 200);
//    }

    public function show($id)
    {
        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found: ' . $e->getMessage()], 404);
        }

        if (request()->user()->cannot('view', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $project->load(['users', 'tags', 'urls']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load project details: ' . $e->getMessage()], 500);
        }

        return response()->json($project, 200);
    }

    public function getFiles($id)
    {
        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found: ' . $e->getMessage()], 404);
        }

        if (request()->user()->cannot('view', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $files = $project->files()->with(['user'])->get();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load project files: ' . $e->getMessage()], 500);
        }

        return response()->json($files, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $project = Project::make(
            [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'logo' => null,
                'cover' => null,
            ]
        );

        try {
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
                $project->logo = $logoPath;
            }

            if ($request->hasFile('cover')) {
                $coverPath = $request->file('cover')->store('covers', 'public');
                $project->cover = $coverPath;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload files: ' . $e->getMessage()], 500);
        }


        try {
            $project->save();
            $project->users()->attach(Auth::user()->id, ['role' => 'owner']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create project: ' . $e->getMessage()], 500);
        }

        return response()->json($project->id, 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'privacy' => 'string|in:public,private',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found: ' . $e->getMessage()], 404);
        }

        if (request()->user()->cannot('update', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->name = $request->input('name');
        $project->description = $request->input('description');
        $project->privacy = $request->input('privacy', 'public');

        try {
            $project->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update project: ' . $e->getMessage()], 500);
        }

        return response()->json($project, 200);

    }

    public function destroy($id)
    {
        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found: ' . $e->getMessage()], 404);
        }

        if (request()->user()->cannot('delete', $project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $project->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete project: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
}
