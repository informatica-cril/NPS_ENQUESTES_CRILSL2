<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquesta;
use App\Models\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PreguntaController extends Controller
{
    /**
     * List questions for a survey
     */
    public function index(Enquesta $enquesta): JsonResponse
    {
        $preguntes = $enquesta->preguntes()->orderBy('ordre')->get();

        return response()->json($preguntes);
    }

    /**
     * Create new question
     */
    public function store(Request $request, Enquesta $enquesta): JsonResponse
    {
        $validated = $request->validate([
            'text_pregunta' => 'required|string|max:500',
            'descripcio' => 'nullable|string',
            'tipus' => 'required|in:nps,escala,text_curt,text_llarg,opcio_unica,opcio_multiple,si_no,data,valoracio_estrelles',
            'obligatoria' => 'boolean',
            'opcions' => 'nullable|array',
            'configuracio' => 'nullable|array',
        ]);

        // Get max order
        $maxOrdre = $enquesta->preguntes()->max('ordre') ?? -1;
        $validated['ordre'] = $maxOrdre + 1;

        $pregunta = $enquesta->preguntes()->create($validated);

        return response()->json([
            'pregunta' => $pregunta,
            'message' => 'Pregunta creada correctament',
        ], 201);
    }

    /**
     * Update question
     */
    public function update(Request $request, Enquesta $enquesta, Pregunta $pregunta): JsonResponse
    {
        $validated = $request->validate([
            'text_pregunta' => 'sometimes|string|max:500',
            'descripcio' => 'nullable|string',
            'tipus' => 'sometimes|in:nps,escala,text_curt,text_llarg,opcio_unica,opcio_multiple,si_no,data,valoracio_estrelles',
            'obligatoria' => 'boolean',
            'opcions' => 'nullable|array',
            'configuracio' => 'nullable|array',
            'activa' => 'boolean',
        ]);

        $pregunta->update($validated);

        return response()->json([
            'pregunta' => $pregunta->fresh(),
            'message' => 'Pregunta actualitzada correctament',
        ]);
    }

    /**
     * Delete question
     */
    public function destroy(Enquesta $enquesta, Pregunta $pregunta): JsonResponse
    {
        $pregunta->delete();

        return response()->json([
            'message' => 'Pregunta eliminada correctament',
        ]);
    }

    /**
     * Reorder questions
     */
    public function reorder(Request $request, Enquesta $enquesta): JsonResponse
    {
        $validated = $request->validate([
            'ordre' => 'required|array',
            'ordre.*' => 'integer|exists:preguntes,id',
        ]);

        foreach ($validated['ordre'] as $index => $preguntaId) {
            Pregunta::where('id', $preguntaId)
                ->where('enquesta_id', $enquesta->id)
                ->update(['ordre' => $index]);
        }

        return response()->json([
            'message' => 'Ordre actualitzat correctament',
            'preguntes' => $enquesta->preguntes()->orderBy('ordre')->get(),
        ]);
    }
}
