<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $user = Auth::user();
        $file_uuid = Str::uuid();
        $file_objectKey = "user$user->id/{$file_uuid}.{$file->getClientOriginalExtension()}";
        error_log($file_objectKey);

        try {
            Storage::disk('project-files')->put($file_objectKey, $file->get(), ['Metadata' => ['original_name' => $file->getClientOriginalName()]]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        try {
            Redis::lpush('file-tasks', json_encode([
                'file_id' => $file_uuid,
                'object_key' => $file_objectKey,
            ], JSON_UNESCAPED_SLASHES));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'File uploaded successfully'], 200);
    }
}
