<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Services\InformeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class InformeController extends Controller
{
    protected InformeService $informeService;

    public function __construct(InformeService $informeService)
    {
        $this->informeService = $informeService;
    }

    /**
     * List all reports
     */
    public function index(Request $request): JsonResponse
    {
        $query = Informe::with(['enquesta', 'centre', 'fisioterapeuta', 'creador']);

        if ($request->has('tipus')) {
            $query->where('tipus', $request->tipus);
        }

        if ($request->has('estat')) {
            $query->where('estat', $request->estat);
        }

        $informes = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($informes);
    }

    /**
     * Create new report
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titol' => 'required|string|max:255',
            'descripcio' => 'nullable|string',
            'tipus' => 'required|in:nps,fisioterapeuta,centre,enquesta,general',
            'enquesta_id' => 'nullable|exists:enquestes,id',
            'centre_id' => 'nullable|exists:centres,id',
            'fisioterapeuta_id' => 'nullable|exists:fisioterapeutes,id',
            'data_inici' => 'required|date',
            'data_fi' => 'required|date|after_or_equal:data_inici',
            'filtres' => 'nullable|array',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['estat'] = 'pendent';

        $informe = Informe::create($validated);

        // Generate report asynchronously (or immediately for simple reports)
        $this->informeService->generar($informe);

        return response()->json([
            'informe' => $informe->fresh(),
            'message' => 'Informe creat. Es generarà en breu.',
        ], 201);
    }

    /**
     * Get single report
     */
    public function show(Informe $informe): JsonResponse
    {
        $informe->load(['enquesta', 'centre', 'fisioterapeuta', 'creador']);

        return response()->json($informe);
    }

    /**
     * Download report file
     */
    public function download(Informe $informe): mixed
    {
        if ($informe->estat !== 'completat' || !$informe->fitxer_path) {
            return response()->json([
                'message' => 'L\'informe encara no està disponible',
            ], 404);
        }

        return Storage::download($informe->fitxer_path, $informe->titol . '.pdf');
    }

    /**
     * Regenerate report
     */
    public function regenerar(Informe $informe): JsonResponse
    {
        $informe->update(['estat' => 'pendent']);
        $this->informeService->generar($informe);

        return response()->json([
            'message' => 'Regenerant informe...',
            'informe' => $informe->fresh(),
        ]);
    }

    /**
     * Delete report
     */
    public function destroy(Informe $informe): JsonResponse
    {
        // Delete file if exists
        if ($informe->fitxer_path) {
            Storage::delete($informe->fitxer_path);
        }

        $informe->delete();

        return response()->json([
            'message' => 'Informe eliminat correctament',
        ]);
    }
}
