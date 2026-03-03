<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CentreController extends Controller
{
    /**
     * List all centers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Centre::query();

        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $centres = $query->orderBy('name')->get();

        return response()->json($centres);
    }

    /**
     * Create new center
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:centres',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'province' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $centre = Centre::create($validated);

        return response()->json([
            'centre' => $centre,
            'message' => 'Centre creat correctament',
        ], 201);
    }

    /**
     * Get single center
     */
    public function show(Centre $centre): JsonResponse
    {
        $centre->loadCount(['fisioterapeutes', 'pacients', 'enquestes']);

        return response()->json($centre);
    }

    /**
     * Update center
     */
    public function update(Request $request, Centre $centre): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:centres,code,' . $centre->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'province' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $centre->update($validated);

        return response()->json([
            'centre' => $centre->fresh(),
            'message' => 'Centre actualitzat correctament',
        ]);
    }

    /**
     * Delete center
     */
    public function destroy(Centre $centre): JsonResponse
    {
        $centre->delete();

        return response()->json([
            'message' => 'Centre eliminat correctament',
        ]);
    }

    /**
     * Get centers for map
     */
    public function map(): JsonResponse
    {
        $centres = Centre::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount(['fisioterapeutes as total_fisioterapeutes', 'pacients as total_pacients'])
            ->get()
            ->map(function ($centre) {
                return [
                    'id' => $centre->id,
                    'name' => $centre->name,
                    'coordinates' => $centre->coordinates,
                    'address' => $centre->address,
                    'city' => $centre->city,
                    'total_fisioterapeutes' => $centre->total_fisioterapeutes,
                    'total_pacients' => $centre->total_pacients,
                ];
            });

        return response()->json($centres);
    }
}
