<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectFileController extends Controller
{
    function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:pdf,docx,doc,dot,xlsx,xls,csv']
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->messages()], 500);
        }

        $file = $request->file('file');

        try {
            Storage::disk('project-files')->put($file->getClientOriginalName(), $file->get());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        try {
            Redis::lpush('file-tasks', json_encode([
                'file' => $file->getClientOriginalName(),
                'user' => $request->user()->id,
            ]));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'File uploaded successfully'], 200);
    }
}
