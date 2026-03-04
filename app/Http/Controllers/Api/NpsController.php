<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NpsResultat;
use App\Models\NpsEstadistica;
use App\Models\Centre;
use App\Services\NpsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class NpsController extends Controller
{
    protected NpsService $npsService;

    public function __construct(NpsService $npsService)
    {
        $this->npsService = $npsService;
    }

    /**
     * Get NPS dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $dataInici = $request->get('data_inici', now()->subMonths(3)->toDateString());
        $dataFi = $request->get('data_fi', now()->toDateString());
        $centreId = $request->get('centre_id');

        $stats = $this->npsService->getEstadistiques($dataInici, $dataFi, $centreId);

        return response()->json($stats);
    }

    /**
     * Get NPS evolution over time
     */
    public function evolucio(Request $request): JsonResponse
    {
        $dataInici = $request->get('data_inici', now()->subYear()->toDateString());
        $dataFi = $request->get('data_fi', now()->toDateString());
        $periode = $request->get('periode', 'mensual'); // diari, setmanal, mensual
        $centreId = $request->get('centre_id');

        $evolucio = $this->npsService->getEvolucio($dataInici, $dataFi, $periode, $centreId);

        return response()->json($evolucio);
    }

    /**
     * Get NPS by center (for map visualization)
     */
    public function perCentre(Request $request): JsonResponse
    {
        $dataInici = $request->get('data_inici', now()->subMonths(3)->toDateString());
        $dataFi = $request->get('data_fi', now()->toDateString());

        $centres = Centre::active()
            ->with(['npsResultats' => function ($q) use ($dataInici, $dataFi) {
                $q->whereBetween('data', [$dataInici, $dataFi]);
            }])
            ->get()
            ->map(function ($centre) {
                $resultats = $centre->npsResultats;
                $total = $resultats->count();

                $npsData = [
                    'centre_id' => $centre->id,
                    'nom' => $centre->name,
                    'coordinates' => $centre->coordinates,
                    'total_respostes' => $total,
                ];

                if ($total > 0) {
                    $promotors = $resultats->where('categoria', 'promotor')->count();
                    $detractors = $resultats->where('categoria', 'detractor')->count();
                    $npsData['nps_score'] = round((($promotors - $detractors) / $total) * 100, 1);
                    $npsData['mitjana'] = round($resultats->avg('puntuacio'), 1);
                } else {
                    $npsData['nps_score'] = null;
                    $npsData['mitjana'] = null;
                }

                return $npsData;
            });

        return response()->json($centres);
    }

    /**
     * Get NPS by physiotherapist
     */
    public function perFisioterapeuta(Request $request): JsonResponse
    {
        $dataInici = $request->get('data_inici', now()->subMonths(3)->toDateString());
        $dataFi = $request->get('data_fi', now()->toDateString());
        $centreId = $request->get('centre_id');

        $query = NpsResultat::whereBetween('data', [$dataInici, $dataFi])
            ->whereNotNull('fisioterapeuta_id');

        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $resultats = $query
            ->with('fisioterapeuta')
            ->get()
            ->groupBy('fisioterapeuta_id')
            ->map(function ($group) {
                $fisio = $group->first()->fisioterapeuta;
                $total = $group->count();
                $promotors = $group->where('categoria', 'promotor')->count();
                $detractors = $group->where('categoria', 'detractor')->count();

                return [
                    'fisioterapeuta_id' => $fisio->id,
                    'nom' => $fisio->nom_complet,
                    'total_respostes' => $total,
                    'nps_score' => round((($promotors - $detractors) / $total) * 100, 1),
                    'mitjana' => round($group->avg('puntuacio'), 1),
                    'promotors' => $promotors,
                    'passius' => $group->where('categoria', 'passiu')->count(),
                    'detractors' => $detractors,
                ];
            })
            ->sortByDesc('nps_score')
            ->values();

        return response()->json($resultats);
    }

    /**
     * Get recent NPS comments
     */
    public function comentaris(Request $request): JsonResponse
    {
        $query = NpsResultat::whereNotNull('comentari')
            ->where('comentari', '!=', '')
            ->with(['enquesta:id,titol', 'centre:id,name', 'fisioterapeuta:id,nom,cognoms'])
            ->orderByDesc('data');

        if ($request->has('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->has('centre_id')) {
            $query->where('centre_id', $request->centre_id);
        }

        $comentaris = $query->paginate($request->get('per_page', 20));

        return response()->json($comentaris);
    }

    /**
     * Export NPS data to CSV
     */
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data_inici' => 'required|date',
            'data_fi' => 'required|date|after_or_equal:data_inici',
            'centre_id' => 'nullable|exists:centres,id',
            'format' => 'in:csv,excel',
        ]);

        // Generate export file
        $exportData = $this->npsService->prepareExportData(
            $validated['data_inici'],
            $validated['data_fi'],
            $validated['centre_id'] ?? null
        );

        // Return download URL or data
        return response()->json([
            'data' => $exportData,
            'message' => 'Dades preparades per exportar',
        ]);
    }
}
