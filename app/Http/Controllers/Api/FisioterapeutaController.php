<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fisioterapeuta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FisioterapeutaController extends Controller
{
    /**
     * List all physiotherapists
     */
    public function index(Request $request): JsonResponse
    {
        $query = Fisioterapeuta::with(['user', 'centre']);

        if ($request->has('actiu')) {
            $query->where('actiu', $request->actiu);
        }

        if ($request->has('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('cognoms', 'like', "%{$search}%")
                    ->orWhere('num_colegiat', 'like', "%{$search}%");
            });
        }

        $fisioterapeutes = $query->orderBy('nom')->paginate($request->get('per_page', 15));

        return response()->json($fisioterapeutes);
    }

    /**
     * Create new physiotherapist
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'cognoms' => 'required|string|max:150',
            'num_colegiat' => 'nullable|string|max:50|unique:fisioterapeutes',
            'especialitat' => 'nullable|string|max:100',
            'centre_id' => 'nullable|exists:centres,id',
            'data_alta' => 'nullable|date',
            'email' => 'nullable|email|unique:users,email',
            'username' => 'nullable|string|max:255|unique:users,username',
            'password' => 'nullable|string|min:8',
            'create_user' => 'boolean',
        ]);

        return DB::transaction(function () use ($validated) {
            $userId = null;

            // Create user if requested
            if (!empty($validated['create_user']) && (!empty($validated['email']) || !empty($validated['username']))) {
                $user = User::create([
                    'name' => $validated['nom'] . ' ' . $validated['cognoms'],
                    'username' => $validated['username'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'password' => md5($validated['password'] ?? 'password123'), // Using MD5 for compatibility
                    'role' => 'fisioterapeuta',
                ]);
                $userId = $user->id;
            }

            $fisioterapeuta = Fisioterapeuta::create([
                'user_id' => $userId,
                'centre_id' => $validated['centre_id'] ?? null,
                'nom' => $validated['nom'],
                'cognoms' => $validated['cognoms'],
                'num_colegiat' => $validated['num_colegiat'] ?? null,
                'especialitat' => $validated['especialitat'] ?? null,
                'data_alta' => $validated['data_alta'] ?? now(),
            ]);

            return response()->json([
                'fisioterapeuta' => $fisioterapeuta->load(['user', 'centre']),
                'message' => 'Fisioterapeuta creat correctament',
            ], 201);
        });
    }

    /**
     * Get single physiotherapist
     */
    public function show(Fisioterapeuta $fisioterapeuta): JsonResponse
    {
        $fisioterapeuta->load(['user', 'centre']);
        $fisioterapeuta->loadCount(['participacions', 'npsResultats', 'informes']);

        return response()->json($fisioterapeuta);
    }

    /**
     * Update physiotherapist
     */
    public function update(Request $request, Fisioterapeuta $fisioterapeuta): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'cognoms' => 'sometimes|string|max:150',
            'num_colegiat' => 'nullable|string|max:50|unique:fisioterapeutes,num_colegiat,' . $fisioterapeuta->id,
            'especialitat' => 'nullable|string|max:100',
            'centre_id' => 'nullable|exists:centres,id',
            'data_alta' => 'nullable|date',
            'actiu' => 'boolean',
        ]);

        $fisioterapeuta->update($validated);

        return response()->json([
            'fisioterapeuta' => $fisioterapeuta->fresh()->load(['user', 'centre']),
            'message' => 'Fisioterapeuta actualitzat correctament',
        ]);
    }

    /**
     * Delete physiotherapist
     */
    public function destroy(Fisioterapeuta $fisioterapeuta): JsonResponse
    {
        $fisioterapeuta->delete();

        return response()->json([
            'message' => 'Fisioterapeuta eliminat correctament',
        ]);
    }

    /**
     * Get physiotherapist statistics
     */
    public function estadistiques(Fisioterapeuta $fisioterapeuta, Request $request): JsonResponse
    {
        $dataInici = $request->get('data_inici', now()->subMonths(3)->toDateString());
        $dataFi = $request->get('data_fi', now()->toDateString());

        $npsResultats = $fisioterapeuta->npsResultats()
            ->whereBetween('data', [$dataInici, $dataFi])
            ->get();

        $total = $npsResultats->count();
        $promotors = $npsResultats->where('categoria', 'promotor')->count();
        $passius = $npsResultats->where('categoria', 'passiu')->count();
        $detractors = $npsResultats->where('categoria', 'detractor')->count();

        $stats = [
            'total_respostes' => $total,
            'promotors' => $promotors,
            'passius' => $passius,
            'detractors' => $detractors,
            'nps_score' => $total > 0 ? round((($promotors - $detractors) / $total) * 100, 1) : null,
            'mitjana' => $npsResultats->avg('puntuacio'),
            'participacions_totals' => $fisioterapeuta->participacions()->count(),
        ];

        return response()->json($stats);
    }
}
