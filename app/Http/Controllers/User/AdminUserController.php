<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function Pest\Laravel\json;

class AdminUserController extends Controller
{
    function index(Request $request): JsonResponse
    {
        $isAdmin = Auth::user()->hasRole('admin');

        if (! $isAdmin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'users' => User::all(),
        ], 200);
    }

    function destroy(string $user): JsonResponse
    {
        $isAdmin = Auth::user()->hasRole('admin');

        if (! $isAdmin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::find($user);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->softDelete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }

    function search(Request $request) {
        $username = $request->input('username');
        $users = User::where('name', 'like', "%$username%")->get();
        return response()->json([
            'users' => $users,
        ], 200);
    }

    function show(string $user): JsonResponse
    {
        $isAdmin = Auth::user()->hasRole('admin');

        if (! $isAdmin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::find($user);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user' => $user,
        ], 200);
    }


}
