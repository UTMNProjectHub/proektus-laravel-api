<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    // список проектов с пагинацией
    public function index(Request $request)
    {

        if ($request->has('user')) {
            try {
                $user = $request->input('user');
                $projects = Project::whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user);
                })->paginate(10);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to retrieve projects: ' . $e->getMessage()], 500);
            }
        } else {
            try {
                $projects = Project::paginate(10);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to retrieve projects: ' . $e->getMessage()], 500);
            }
        }

        return response()->json($projects, 200);
    }

    public function indexByUser($page = null)
    {
        Auth::guard()->user();

        try {
            $projects = Auth::user()->projects()->paginate(10);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve projects: ' . $e->getMessage()], 500);
        }

        return response()->json($projects, 200);
    }

    public function show($id)
    {
        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found: ' . $e->getMessage()], 404);
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

        try {
            $files = $project->files()->with(['user'])->get();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load project files: ' . $e->getMessage()], 500);
        }

        return response()->json($files, 200);
    }

    public function store(Request $request)
    {

        Auth::guard();

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


        try
        {
            $project->save();
            $project->users()->attach(Auth::user()->id, ['role' => 'owner']);
        }
        catch (\Exception $e)
        {
            return response()->json(['error' => 'Failed to create project: ' . $e->getMessage()], 500);
        }

        return response()->json($project->id, 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found: ' . $e->getMessage()], 404);
        }

        $project->name = $request->input('name');
        $project->description = $request->input('description');

        try
        {
            $project->save();
        }
        catch (\Exception $e)
        {
            return response()->json(['error' => 'Failed to update project: ' . $e->getMessage()], 500);
        }

        return response()->json($project, 200);

    }

    public function destroy($id)
    {

    }
}
