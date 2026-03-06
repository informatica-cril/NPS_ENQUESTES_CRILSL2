<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Physiotherapist;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login admin or fisioterapeuta
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $passwordHash = md5($request->password);

        // Try Admin
        $admin = Admin::where('username', $request->username)->first();
        if ($admin && $admin->password === $passwordHash) {
            $token = $admin->createToken('auth-token')->plainTextToken;
            return response()->json([
                'user' => array_merge($admin->toArray(), ['role' => 'admin']),
                'token' => $token,
            ]);
        }

        // Try Fisioterapeuta
        $fisio = Physiotherapist::where('email', $request->username)
            ->orWhere('full_name', 'like', '%' . $request->username . '%')
            ->first();
        
        if ($fisio && $fisio->password === $passwordHash) {
            if (!$fisio->is_active) {
                throw ValidationException::withMessages([
                    'username' => ['Aquest compte està desactivat.'],
                ]);
            }
            
            $token = $fisio->createToken('auth-token')->plainTextToken;
            return response()->json([
                'user' => array_merge($fisio->toArray(), ['role' => 'fisioterapeuta']),
                'token' => $token,
            ]);
        }

        throw ValidationException::withMessages([
            'username' => ['Credencials incorrectes.'],
        ]);
    }

    /**
     * Login pacient by CIP + DNI
     */
    public function pacientLogin(Request $request): JsonResponse
    {
        $request->validate([
            'cip' => 'required|string',
            'dni' => 'required|string',
        ]);

        $patient = Patient::whereRaw('LOWER(cip) = ?', [strtolower($request->cip)])
            ->whereRaw('LOWER(dni) = ?', [strtolower($request->dni)])
            ->first();

        if (!$patient) {
            throw ValidationException::withMessages([
                'cip' => ['Usuari no trobat.'],
            ]);
        }

        $token = $patient->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => array_merge($patient->toArray(), ['role' => 'pacient']),
            'token' => $token,
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Determine role based on model type
        if ($user instanceof Admin) {
            $role = 'admin';
        } elseif ($user instanceof Physiotherapist) {
            $role = 'fisioterapeuta';
        } elseif ($user instanceof Patient) {
            $role = 'pacient';
        } else {
            $role = 'unknown';
        }

        $userData = $user->toArray();
        $userData['role'] = $role;

        return response()->json([
            'user' => $userData,
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
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = md5($validated['password']);
        }

        $user->update($validated);

        // Determine role
        if ($user instanceof Admin) {
            $role = 'admin';
        } elseif ($user instanceof Physiotherapist) {
            $role = 'fisioterapeuta';
        } elseif ($user instanceof Patient) {
            $role = 'pacient';
        } else {
            $role = 'unknown';
        }

        $userData = $user->fresh()->toArray();
        $userData['role'] = $role;

        return response()->json([
            'user' => $userData,
            'message' => 'Perfil actualitzat correctament',
        ]);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        // Find user by username
        $admin = Admin::where('username', $request->username)->first();
        $fisio = Physiotherapist::where('email', $request->username)
            ->orWhere('full_name', 'like', '%' . $request->username . '%')
            ->first();

        if (!$admin && !$fisio) {
            throw ValidationException::withMessages([
                'username' => ['Usuari no trobat.'],
            ]);
        }

        // TODO: Implement password reset email logic
        return response()->json([
            'message' => 'Si l\'usuari existeix, rebràs un enllaç per restablir la contrasenya.',
        ]);
    }

    /**
     * Register (disabled for new schema)
     */
    public function register(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Registre no disponible',
        ], 403);
    }
}
