<?php

namespace App\Services;

use App\Models\NpsResultat;
use App\Models\NpsEstadistica;
use App\Models\Participacio;
use App\Models\Centre;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NpsService
{
    /**
     * Register NPS result from survey response
     */
    public function registrarResultat(Participacio $participacio, int $puntuacio, ?string $comentari = null): NpsResultat
    {
        return NpsResultat::create([
            'enquesta_id' => $participacio->enquesta_id,
            'participacio_id' => $participacio->id,
            'centre_id' => $participacio->enquesta->centre_id ?? $participacio->pacient?->centre_id,
            'fisioterapeuta_id' => $participacio->fisioterapeuta_id,
            'puntuacio' => $puntuacio,
            'comentari' => $comentari,
            'data' => now()->toDateString(),
        ]);
    }

    /**
     * Get NPS statistics for a period
     */
    public function getEstadistiques(string $dataInici, string $dataFi, ?int $centreId = null): array
    {
        $query = NpsResultat::whereBetween('data', [$dataInici, $dataFi]);

        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $resultats = $query->get();
        $total = $resultats->count();

        if ($total === 0) {
            return [
                'total_respostes' => 0,
                'promotors' => 0,
                'passius' => 0,
                'detractors' => 0,
                'nps_score' => null,
                'mitjana_puntuacio' => null,
                'distribucio' => [],
            ];
        }

        $promotors = $resultats->where('categoria', 'promotor')->count();
        $passius = $resultats->where('categoria', 'passiu')->count();
        $detractors = $resultats->where('categoria', 'detractor')->count();

        // Distribution by score
        $distribucio = $resultats->groupBy('puntuacio')
            ->map(fn($group) => $group->count())
            ->sortKeys()
            ->toArray();

        return [
            'total_respostes' => $total,
            'promotors' => $promotors,
            'passius' => $passius,
            'detractors' => $detractors,
            'percent_promotors' => round(($promotors / $total) * 100, 1),
            'percent_passius' => round(($passius / $total) * 100, 1),
            'percent_detractors' => round(($detractors / $total) * 100, 1),
            'nps_score' => round((($promotors - $detractors) / $total) * 100, 1),
            'mitjana_puntuacio' => round($resultats->avg('puntuacio'), 2),
            'distribucio' => $distribucio,
        ];
    }

    /**
     * Get NPS evolution over time
     */
    public function getEvolucio(string $dataInici, string $dataFi, string $periode = 'mensual', ?int $centreId = null): array
    {
        $groupBy = match ($periode) {
            'diari' => 'DATE(data)',
            'setmanal' => 'YEARWEEK(data, 1)',
            default => 'DATE_FORMAT(data, "%Y-%m")',
        };

        $query = NpsResultat::whereBetween('data', [$dataInici, $dataFi])
            ->select(
                DB::raw("{$groupBy} as periode"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN categoria = "promotor" THEN 1 ELSE 0 END) as promotors'),
                DB::raw('SUM(CASE WHEN categoria = "passiu" THEN 1 ELSE 0 END) as passius'),
                DB::raw('SUM(CASE WHEN categoria = "detractor" THEN 1 ELSE 0 END) as detractors'),
                DB::raw('AVG(puntuacio) as mitjana')
            )
            ->groupBy('periode')
            ->orderBy('periode');

        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return $query->get()
            ->map(function ($row) {
                return [
                    'periode' => $row->periode,
                    'total' => $row->total,
                    'promotors' => $row->promotors,
                    'passius' => $row->passius,
                    'detractors' => $row->detractors,
                    'nps_score' => $row->total > 0
                        ? round((($row->promotors - $row->detractors) / $row->total) * 100, 1)
                        : null,
                    'mitjana' => round($row->mitjana, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Prepare data for export
     */
    public function prepareExportData(string $dataInici, string $dataFi, ?int $centreId = null): array
    {
        $query = NpsResultat::with(['enquesta:id,titol', 'centre:id,name', 'fisioterapeuta:id,nom,cognoms'])
            ->whereBetween('data', [$dataInici, $dataFi]);

        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return $query->get()
            ->map(function ($resultat) {
                return [
                    'data' => $resultat->data->format('Y-m-d'),
                    'enquesta' => $resultat->enquesta?->titol,
                    'centre' => $resultat->centre?->name,
                    'fisioterapeuta' => $resultat->fisioterapeuta?->nom_complet,
                    'puntuacio' => $resultat->puntuacio,
                    'categoria' => $resultat->categoria,
                    'comentari' => $resultat->comentari,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate and store periodic statistics
     */
    public function calcularEstadistiquesPeriodiques(string $periode, ?Carbon $data = null): void
    {
        $data = $data ?? now();

        [$dataInici, $dataFi] = match ($periode) {
            'diari' => [$data->copy()->startOfDay(), $data->copy()->endOfDay()],
            'setmanal' => [$data->copy()->startOfWeek(), $data->copy()->endOfWeek()],
            'mensual' => [$data->copy()->startOfMonth(), $data->copy()->endOfMonth()],
            'trimestral' => [$data->copy()->startOfQuarter(), $data->copy()->endOfQuarter()],
            'anual' => [$data->copy()->startOfYear(), $data->copy()->endOfYear()],
            default => [$data->copy()->startOfMonth(), $data->copy()->endOfMonth()],
        };

        // Calculate for each centre
        $centres = Centre::active()->get();

        foreach ($centres as $centre) {
            $stats = $this->getEstadistiques(
                $dataInici->toDateString(),
                $dataFi->toDateString(),
                $centre->id
            );

            if ($stats['total_respostes'] > 0) {
                NpsEstadistica::updateOrCreate(
                    [
                        'centre_id' => $centre->id,
                        'periode' => $periode,
                        'data_inici' => $dataInici->toDateString(),
                    ],
                    [
                        'data_fi' => $dataFi->toDateString(),
                        'total_respostes' => $stats['total_respostes'],
                        'promotors' => $stats['promotors'],
                        'passius' => $stats['passius'],
                        'detractors' => $stats['detractors'],
                        'nps_score' => $stats['nps_score'],
                        'mitjana_puntuacio' => $stats['mitjana_puntuacio'],
                    ]
                );
            }
        }
    }
}
