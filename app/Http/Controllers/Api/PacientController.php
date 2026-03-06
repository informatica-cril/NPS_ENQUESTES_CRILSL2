<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pacient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PacientController extends Controller
{
    /**
     * List all patients
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pacient::with(['centre']);

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
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('cip', 'like', "%{$search}%");
            });
        }

        $pacients = $query->orderBy('cognoms')->paginate($request->get('per_page', 15));

        return response()->json($pacients);
    }

    /**
     * Create new patient
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'cognoms' => 'required|string|max:150',
            'dni' => 'nullable|string|max:15',
            'cip' => 'nullable|string|max:20',
            'data_naixement' => 'nullable|date',
            'sexe' => 'nullable|in:home,dona,altre',
            'telefon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adreca' => 'nullable|string|max:255',
            'poblacio' => 'nullable|string|max:100',
            'codi_postal' => 'nullable|string|max:10',
            'centre_id' => 'nullable|exists:centres,id',
            'consentiment_rgpd' => 'boolean',
        ]);

        $validated['data_alta'] = now();
        if (!empty($validated['consentiment_rgpd'])) {
            $validated['data_consentiment'] = now();
        }

        $pacient = Pacient::create($validated);

        return response()->json([
            'pacient' => $pacient->load('centre'),
            'message' => 'Pacient creat correctament',
        ], 201);
    }

    /**
     * Get single patient
     */
    public function show(Pacient $pacient): JsonResponse
    {
        $pacient->load(['centre']);
        $pacient->loadCount(['participacions']);

        return response()->json($pacient);
    }

    /**
     * Update patient
     */
    public function update(Request $request, Pacient $pacient): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'cognoms' => 'sometimes|string|max:150',
            'dni' => 'nullable|string|max:15',
            'cip' => 'nullable|string|max:20',
            'data_naixement' => 'nullable|date',
            'sexe' => 'nullable|in:home,dona,altre',
            'telefon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adreca' => 'nullable|string|max:255',
            'poblacio' => 'nullable|string|max:100',
            'codi_postal' => 'nullable|string|max:10',
            'centre_id' => 'nullable|exists:centres,id',
            'actiu' => 'boolean',
            'consentiment_rgpd' => 'boolean',
        ]);

        // Update consent timestamp if newly given
        if (!empty($validated['consentiment_rgpd']) && !$pacient->consentiment_rgpd) {
            $validated['data_consentiment'] = now();
        }

        $pacient->update($validated);

        return response()->json([
            'pacient' => $pacient->fresh()->load('centre'),
            'message' => 'Pacient actualitzat correctament',
        ]);
    }

    /**
     * Delete patient (soft delete)
     */
    public function destroy(Pacient $pacient): JsonResponse
    {
        $pacient->delete();

        return response()->json([
            'message' => 'Pacient eliminat correctament',
        ]);
    }

    /**
     * Get patient survey history
     */

    public function senseEnquestes(): JsonResponse
    {
        $pacients = \App\Models\Pacient::whereDoesntHave('participacions')->with('centre:id,name')->orderBy('cognoms')->get()->map(function($p){
            return ['id'=>$p->id,'nom_complet'=>$p->nom_complet??"{$p->nom} {$p->cognoms}",'email'=>$p->email,'telefon'=>$p->telefon,'centre'=>$p->centre?['name'=>$p->centre->name]:null,'data_alta'=>$p->data_alta,'actiu'=>$p->actiu];
        });
        return response()->json($pacients);
    }

    public function historial(Pacient $pacient): JsonResponse
    {
        $participacions = $pacient->participacions()
            ->with(['enquesta:id,titol,tipus', 'respostes', 'npsResultat'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($participacions);
    }
}
