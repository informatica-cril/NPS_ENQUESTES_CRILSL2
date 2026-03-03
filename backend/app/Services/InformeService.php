<?php

namespace App\Services;

use App\Models\Informe;
use App\Models\NpsResultat;
use App\Models\Enquesta;
use Illuminate\Support\Facades\Storage;

class InformeService
{
    protected NpsService $npsService;

    public function __construct(NpsService $npsService)
    {
        $this->npsService = $npsService;
    }

    /**
     * Generate report
     */
    public function generar(Informe $informe): void
    {
        try {
            $informe->update(['estat' => 'processant']);

            $dades = match ($informe->tipus) {
                'nps' => $this->generarInformeNps($informe),
                'fisioterapeuta' => $this->generarInformeFisioterapeuta($informe),
                'centre' => $this->generarInformeCentre($informe),
                'enquesta' => $this->generarInformeEnquesta($informe),
                default => $this->generarInformeGeneral($informe),
            };

            $informe->update([
                'dades' => $dades,
                'estat' => 'completat',
            ]);

        } catch (\Exception $e) {
            $informe->update([
                'estat' => 'error',
                'dades' => ['error' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Generate NPS report
     */
    protected function generarInformeNps(Informe $informe): array
    {
        $stats = $this->npsService->getEstadistiques(
            $informe->data_inici->toDateString(),
            $informe->data_fi->toDateString(),
            $informe->centre_id
        );

        $evolucio = $this->npsService->getEvolucio(
            $informe->data_inici->toDateString(),
            $informe->data_fi->toDateString(),
            'mensual',
            $informe->centre_id
        );

        $comentaris = NpsResultat::whereNotNull('comentari')
            ->whereBetween('data', [$informe->data_inici, $informe->data_fi])
            ->when($informe->centre_id, fn($q) => $q->where('centre_id', $informe->centre_id))
            ->orderByDesc('data')
            ->limit(50)
            ->get(['puntuacio', 'categoria', 'comentari', 'data']);

        return [
            'resum' => $stats,
            'evolucio' => $evolucio,
            'comentaris' => $comentaris,
        ];
    }

    /**
     * Generate physiotherapist report
     */
    protected function generarInformeFisioterapeuta(Informe $informe): array
    {
        if (!$informe->fisioterapeuta_id) {
            return ['error' => 'Fisioterapeuta no especificat'];
        }

        $fisio = $informe->fisioterapeuta;

        $npsResultats = $fisio->npsResultats()
            ->whereBetween('data', [$informe->data_inici, $informe->data_fi])
            ->get();

        $total = $npsResultats->count();
        $promotors = $npsResultats->where('categoria', 'promotor')->count();
        $detractors = $npsResultats->where('categoria', 'detractor')->count();

        return [
            'fisioterapeuta' => [
                'nom' => $fisio->nom_complet,
                'especialitat' => $fisio->especialitat,
                'centre' => $fisio->centre?->name,
            ],
            'estadistiques' => [
                'total_respostes' => $total,
                'nps_score' => $total > 0 ? round((($promotors - $detractors) / $total) * 100, 1) : null,
                'mitjana' => $npsResultats->avg('puntuacio'),
                'promotors' => $promotors,
                'passius' => $npsResultats->where('categoria', 'passiu')->count(),
                'detractors' => $detractors,
            ],
            'comentaris' => $npsResultats->whereNotNull('comentari')->values(),
        ];
    }

    /**
     * Generate center report
     */
    protected function generarInformeCentre(Informe $informe): array
    {
        if (!$informe->centre_id) {
            return ['error' => 'Centre no especificat'];
        }

        $centre = $informe->centre;

        $stats = $this->npsService->getEstadistiques(
            $informe->data_inici->toDateString(),
            $informe->data_fi->toDateString(),
            $centre->id
        );

        // Stats by physiotherapist
        $perFisio = NpsResultat::where('centre_id', $centre->id)
            ->whereBetween('data', [$informe->data_inici, $informe->data_fi])
            ->whereNotNull('fisioterapeuta_id')
            ->with('fisioterapeuta:id,nom,cognoms')
            ->get()
            ->groupBy('fisioterapeuta_id')
            ->map(function ($group) {
                $total = $group->count();
                $promotors = $group->where('categoria', 'promotor')->count();
                $detractors = $group->where('categoria', 'detractor')->count();

                return [
                    'nom' => $group->first()->fisioterapeuta->nom_complet,
                    'total' => $total,
                    'nps_score' => round((($promotors - $detractors) / $total) * 100, 1),
                ];
            })
            ->sortByDesc('nps_score')
            ->values();

        return [
            'centre' => [
                'nom' => $centre->name,
                'adreca' => $centre->address,
                'ciutat' => $centre->city,
            ],
            'estadistiques' => $stats,
            'per_fisioterapeuta' => $perFisio,
        ];
    }

    /**
     * Generate survey-specific report
     */
    protected function generarInformeEnquesta(Informe $informe): array
    {
        if (!$informe->enquesta_id) {
            return ['error' => 'Enquesta no especificada'];
        }

        $enquesta = $informe->enquesta;

        $participacions = $enquesta->participacions()
            ->whereBetween('created_at', [$informe->data_inici, $informe->data_fi])
            ->with('respostes.pregunta')
            ->get();

        $totalParticipacions = $participacions->count();
        $completades = $participacions->where('estat', 'completada')->count();

        // Aggregate responses per question
        $respostesPerPregunta = [];
        foreach ($enquesta->preguntes as $pregunta) {
            $respostes = $participacions->flatMap->respostes
                ->where('pregunta_id', $pregunta->id);

            $respostesPerPregunta[$pregunta->id] = [
                'pregunta' => $pregunta->text_pregunta,
                'tipus' => $pregunta->tipus,
                'total_respostes' => $respostes->count(),
            ];

            if (in_array($pregunta->tipus, ['nps', 'escala', 'valoracio_estrelles'])) {
                $respostesPerPregunta[$pregunta->id]['mitjana'] = $respostes->avg('valor_numeric');
                $respostesPerPregunta[$pregunta->id]['distribucio'] = $respostes
                    ->groupBy('valor_numeric')
                    ->map->count()
                    ->sortKeys();
            }
        }

        return [
            'enquesta' => [
                'titol' => $enquesta->titol,
                'tipus' => $enquesta->tipus,
            ],
            'participacio' => [
                'total' => $totalParticipacions,
                'completades' => $completades,
                'taxa_completat' => $totalParticipacions > 0
                    ? round(($completades / $totalParticipacions) * 100, 1)
                    : 0,
            ],
            'respostes' => $respostesPerPregunta,
        ];
    }

    /**
     * Generate general report
     */
    protected function generarInformeGeneral(Informe $informe): array
    {
        return [
            'nps_general' => $this->npsService->getEstadistiques(
                $informe->data_inici->toDateString(),
                $informe->data_fi->toDateString()
            ),
            'evolucio' => $this->npsService->getEvolucio(
                $informe->data_inici->toDateString(),
                $informe->data_fi->toDateString()
            ),
        ];
    }
}
