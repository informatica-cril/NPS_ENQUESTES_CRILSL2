<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquesta;
use App\Models\Participacio;
use App\Models\Resposta;
use App\Models\NpsResultat;
use App\Services\NpsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParticipacioController extends Controller
{
    protected NpsService $npsService;

    public function __construct(NpsService $npsService)
    {
        $this->npsService = $npsService;
    }

    /**
     * List participations for a survey
     */
    public function index(Request $request, Enquesta $enquesta): JsonResponse
    {
        $query = $enquesta->participacions()
            ->with(['pacient', 'fisioterapeuta']);

        if ($request->has('estat')) {
            $query->where('estat', $request->estat);
        }

        $participacions = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($participacions);
    }

    /**
     * Create participation (generate survey link)
     */
    public function store(Request $request, Enquesta $enquesta): JsonResponse
    {
        $validated = $request->validate([
            'pacient_id' => 'nullable|exists:pacients,id',
            'fisioterapeuta_id' => 'nullable|exists:fisioterapeutes,id',
        ]);

        $participacio = $enquesta->participacions()->create([
            'pacient_id' => $validated['pacient_id'] ?? null,
            'fisioterapeuta_id' => $validated['fisioterapeuta_id'] ?? null,
            'estat' => 'pendent',
        ]);

        return response()->json([
            'participacio' => $participacio,
            'url' => config('app.frontend_url') . '/enquesta/' . $enquesta->slug . '?token=' . $participacio->token,
            'message' => 'Participació creada correctament',
        ], 201);
    }

    /**
     * Get participation by token (public)
     */
    public function showByToken(string $token): JsonResponse
    {
        $participacio = Participacio::where('token', $token)
            ->with(['enquesta.preguntes' => function ($q) {
                $q->activa()->orderBy('ordre');
            }])
            ->firstOrFail();

        if ($participacio->estat === 'completada') {
            return response()->json([
                'message' => 'Aquesta enquesta ja ha estat completada',
                'completada' => true,
            ]);
        }

        if ($participacio->estat === 'expirada') {
            return response()->json([
                'message' => 'Aquesta enquesta ha expirat',
            ], 410);
        }

        // Start participation if pending
        $participacio->iniciar();

        return response()->json($participacio);
    }

    /**
     * Submit survey responses
     */
    public function submit(Request $request, string $token): JsonResponse
    {
        $participacio = Participacio::where('token', $token)
            ->with('enquesta.preguntes')
            ->firstOrFail();

        if ($participacio->estat === 'completada') {
            return response()->json([
                'message' => 'Aquesta enquesta ja ha estat completada',
            ], 400);
        }

        $validated = $request->validate([
            'respostes' => 'required|array',
            'respostes.*.pregunta_id' => 'required|exists:preguntes,id',
            'respostes.*.valor' => 'nullable',
        ]);

        // Validate required questions
        $requiredQuestions = $participacio->enquesta->preguntes
            ->where('obligatoria', true)
            ->pluck('id');

        $answeredQuestions = collect($validated['respostes'])
            ->pluck('pregunta_id');

        $missingRequired = $requiredQuestions->diff($answeredQuestions);
        if ($missingRequired->isNotEmpty()) {
            return response()->json([
                'message' => 'Falten respostes obligatòries',
                'missing' => $missingRequired->values(),
            ], 422);
        }

        // Save responses
        foreach ($validated['respostes'] as $respostaData) {
            $pregunta = $participacio->enquesta->preguntes
                ->firstWhere('id', $respostaData['pregunta_id']);

            $resposta = [
                'participacio_id' => $participacio->id,
                'pregunta_id' => $respostaData['pregunta_id'],
            ];

            // Determine value type based on question type
            if (in_array($pregunta->tipus, ['nps', 'escala', 'valoracio_estrelles'])) {
                $resposta['valor_numeric'] = $respostaData['valor'];
            } elseif (in_array($pregunta->tipus, ['opcio_multiple'])) {
                $resposta['valor_json'] = $respostaData['valor'];
            } else {
                $resposta['valor_text'] = $respostaData['valor'];
            }

            Resposta::updateOrCreate(
                [
                    'participacio_id' => $participacio->id,
                    'pregunta_id' => $respostaData['pregunta_id'],
                ],
                $resposta
            );

            // If NPS question, create NPS result
            if ($pregunta->tipus === 'nps' && $respostaData['valor'] !== null) {
                $this->npsService->registrarResultat(
                    $participacio,
                    (int) $respostaData['valor'],
                    $request->input('comentari_nps')
                );
            }
        }

        // Mark as completed
        $participacio->update([
            'estat' => 'completada',
            'data_completat' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Gràcies per completar l\'enquesta!',
            'participacio' => $participacio->fresh(),
        ]);
    }

    /**
     * Get participation details
     */
    public function show(Enquesta $enquesta, Participacio $participacio): JsonResponse
    {
        $participacio->load(['pacient', 'fisioterapeuta', 'respostes.pregunta', 'npsResultat']);

        return response()->json($participacio);
    }

    /**
     * Delete participation
     */
    public function destroy(Enquesta $enquesta, Participacio $participacio): JsonResponse
    {
        $participacio->delete();

        return response()->json([
            'message' => 'Participació eliminada correctament',
        ]);
    }

    /**
     * Resend survey invitation
     */
    public function resend(Enquesta $enquesta, Participacio $participacio): JsonResponse
    {
        // TODO: Implement email sending
        // Mail::to($participacio->pacient->email)->send(new SurveyInvitation($participacio));

        return response()->json([
            'message' => 'Invitació enviada correctament',
        ]);
    }
}
