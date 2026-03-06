<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Ensure at least username or email is provided
        if (empty($validated['username']) && empty($validated['email'])) {
            throw ValidationException::withMessages([
                'username' => ['Debes proporcionar un nombre de usuario o correo electrónico.'],
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'viewer',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string', // Changed from email to username
            'password' => 'required',
        ]);

        // Find user by username or email
        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => ['Usuari no trobat.'],
            ]);
        }

        // Check password with multiple hash types for migration
        $passwordValid = false;
        if (Hash::check($request->password, $user->password)) {
            $passwordValid = true;
        } elseif (md5($request->password) === $user->password) {
            $passwordValid = true;
            // Convert MD5 to bcrypt
            $user->password = Hash::make($request->password);
            $user->save();
        } elseif ($request->password === $user->password) {
            $passwordValid = true;
            // Convert plain text to bcrypt
            $user->password = Hash::make($request->password);
            $user->save();
        }

        if (!$passwordValid) {
            throw ValidationException::withMessages([
                'username' => ['Credencials incorrectes.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'username' => ['Aquest compte està desactivat.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load(['fisioterapeuta', 'pacient']),
            'token' => $token,
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sessió tancada correctament',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['fisioterapeuta', 'pacient']),
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'user' => $user->fresh(),
            'message' => 'Perfil actualitzat correctament',
        ]);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        // Find user by username or email
        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => ['Usuari no trobat.'],
            ]);
        }

        // TODO: Implement password reset email logic
        // Password::sendResetLink($request->only('username'));

        return response()->json([
            'message' => 'Si l\'usuari existeix, rebràs un enllaç per restablir la contrasenya.',
        ]);
    }
}
