<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = request()->user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        $user->load('roles');

        return response()->json([
            'user' => $user,
        ], 200);
    }


    function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = request()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'firstname' => 'string|max:255',
            'lastname' => 'string|max:255',
            'middlename' => 'nullable|string|max:255',
            'old_password' => ['required', 'string','min:8', 'current_password'],
            'password' => [Rules\Password::defaults()]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->fill([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'middlename' => $request->input('middlename'),
        ]);

        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
            auth()->login($user);
        }

        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось обновить данные пользователя: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Данные пользователя успешно обновлены', 'user' => $user], 200);
    }
}
