<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquesta;
use App\Models\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class EnquestaController extends Controller
{
    /**
     * List all surveys with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Enquesta::with(['centre', 'creador'])
            ->withCount(['participacions as total_participacions' => function ($q) {
                $q->where('estat', 'completada');
            }]);

        // Filters
        if ($request->has('estat')) {
            $query->where('estat', $request->estat);
        }

        if ($request->has('tipus')) {
            $query->where('tipus', $request->tipus);
        }

        if ($request->has('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titol', 'like', "%{$search}%")
                    ->orWhere('descripcio', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $enquestes = $query->paginate($request->get('per_page', 15));

        return response()->json($enquestes);
    }

    /**
     * Create new survey
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titol' => 'required|string|max:255',
            'descripcio' => 'nullable|string',
            'tipus' => 'required|in:nps,satisfaccio,qualitat,general',
            'centre_id' => 'nullable|exists:centres,id',
            'data_inici' => 'nullable|date',
            'data_fi' => 'nullable|date|after_or_equal:data_inici',
            'anonima' => 'boolean',
            'requereix_autenticacio' => 'boolean',
            'temps_estimat_minuts' => 'nullable|integer|min:1',
            'configuracio' => 'nullable|array',
            'preguntes' => 'nullable|array',
            'preguntes.*.text_pregunta' => 'required|string',
            'preguntes.*.tipus' => 'required|string',
            'preguntes.*.obligatoria' => 'boolean',
            'preguntes.*.opcions' => 'nullable|array',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['titol']) . '-' . Str::random(6);
        $validated['estat'] = 'esborrany';

        $enquesta = Enquesta::create($validated);

        // Create questions if provided
        if (isset($validated['preguntes'])) {
            foreach ($validated['preguntes'] as $index => $preguntaData) {
                $enquesta->preguntes()->create([
                    ...$preguntaData,
                    'ordre' => $index,
                ]);
            }
        }

        return response()->json([
            'enquesta' => $enquesta->load('preguntes'),
            'message' => 'Enquesta creada correctament',
        ], 201);
    }

    /**
     * Get single survey
     */
    public function show(Enquesta $enquesta): JsonResponse
    {
        $enquesta->load(['centre', 'creador', 'preguntes' => function ($q) {
            $q->orderBy('ordre');
        }]);

        $enquesta->loadCount(['participacions as total_participacions' => function ($q) {
            $q->where('estat', 'completada');
        }]);

        return response()->json($enquesta);
    }

    /**
     * Get survey by slug (public)
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $enquesta = Enquesta::where('slug', $slug)
            ->with(['centre', 'preguntes' => function ($q) {
                $q->activa()->orderBy('ordre');
            }])
            ->firstOrFail();

        if (!$enquesta->isActiva()) {
            return response()->json([
                'message' => 'Aquesta enquesta no està disponible',
            ], 404);
        }

        return response()->json($enquesta);
    }

    /**
     * Update survey
     */
    public function update(Request $request, Enquesta $enquesta): JsonResponse
    {
        $validated = $request->validate([
            'titol' => 'sometimes|string|max:255',
            'descripcio' => 'nullable|string',
            'tipus' => 'sometimes|in:nps,satisfaccio,qualitat,general',
            'estat' => 'sometimes|in:esborrany,activa,tancada,arxivada',
            'centre_id' => 'nullable|exists:centres,id',
            'data_inici' => 'nullable|date',
            'data_fi' => 'nullable|date|after_or_equal:data_inici',
            'anonima' => 'boolean',
            'requereix_autenticacio' => 'boolean',
            'temps_estimat_minuts' => 'nullable|integer|min:1',
            'configuracio' => 'nullable|array',
        ]);

        $enquesta->update($validated);

        $enquesta->refresh();
        $enquesta->load('preguntes');
        return response()->json([
            'enquesta' => $enquesta,
            'message' => 'Enquesta actualitzada correctament',
        ]);
    }

    /**
     * Delete survey
     */
    public function destroy(Enquesta $enquesta): JsonResponse
    {
        $enquesta->delete();

        return response()->json([
            'message' => 'Enquesta eliminada correctament',
        ]);
    }

    /**
     * Duplicate survey
     */
    public function duplicate(Enquesta $enquesta): JsonResponse
    {
        $newEnquesta = $enquesta->replicate();
        $newEnquesta->titol = $enquesta->titol . ' (còpia)';
        $newEnquesta->slug = Str::slug($newEnquesta->titol) . '-' . Str::random(6);
        $newEnquesta->estat = 'esborrany';
        $newEnquesta->created_by = request()->user()->id;
        $newEnquesta->save();

        // Duplicate questions
        foreach ($enquesta->preguntes as $pregunta) {
            $newPregunta = $pregunta->replicate();
            $newPregunta->enquesta_id = $newEnquesta->id;
            $newPregunta->save();
        }

        return response()->json([
            'enquesta' => $newEnquesta->load('preguntes'),
            'message' => 'Enquesta duplicada correctament',
        ], 201);
    }

    /**
     * Get survey statistics
     */
    public function estadistiques(Enquesta $enquesta): JsonResponse
    {
        $stats = [
            'total_participacions' => $enquesta->participacions()->where('estat', 'completada')->count(),
            'en_curs' => $enquesta->participacions()->where('estat', 'en_curs')->count(),
            'pendents' => $enquesta->participacions()->where('estat', 'pendent')->count(),
        ];

        if ($enquesta->tipus === 'nps') {
            $npsStats = $enquesta->npsResultats()
                ->selectRaw('categoria, COUNT(*) as total')
                ->groupBy('categoria')
                ->pluck('total', 'categoria')
                ->toArray();

            $stats['nps'] = [
                'promotors' => $npsStats['promotor'] ?? 0,
                'passius' => $npsStats['passiu'] ?? 0,
                'detractors' => $npsStats['detractor'] ?? 0,
            ];

            $total = array_sum($stats['nps']);
            if ($total > 0) {
                $stats['nps']['score'] = round(
                    (($stats['nps']['promotors'] - $stats['nps']['detractors']) / $total) * 100,
                    1
                );
            } else {
                $stats['nps']['score'] = null;
            }

            $stats['nps']['mitjana'] = $enquesta->npsResultats()->avg('puntuacio');
        }

        return response()->json($stats);
    }
}
