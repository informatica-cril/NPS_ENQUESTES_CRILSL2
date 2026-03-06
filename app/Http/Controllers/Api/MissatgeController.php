<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Missatge;
use App\Models\Fisioterapeuta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
class MissatgeController extends Controller
{
    public function index(Request $request, int $fisioterapeutaId): JsonResponse
    {
        $user = Auth::user();
        if ($user->role === 'fisioterapeuta' && $user->fisioterapeuta?->id !== $fisioterapeutaId) return response()->json(['message'=>'No autoritzat'],403);
        if (!in_array($user->role, ['admin','fisioterapeuta'])) return response()->json(['message'=>'No autoritzat'],403);
        $missatges = Missatge::with('emissor:id,name,avatar')->where('fisioterapeuta_id',$fisioterapeutaId)->orderBy('created_at','asc')->get();
        $rol = $this->rolActual($user);
        if ($rol) Missatge::where('fisioterapeuta_id',$fisioterapeutaId)->where('emissor_rol','!=',$rol)->where('llegit',false)->update(['llegit'=>true,'llegit_at'=>now()]);
        return response()->json($missatges);
    }
    public function store(Request $request, int $fisioterapeutaId): JsonResponse
    {
        $user = Auth::user();
        $rol = $this->rolActual($user);
        if (!$rol) return response()->json(['message'=>'No autoritzat'],403);
        if ($rol==='fisioterapeuta' && $user->fisioterapeuta?->id!==$fisioterapeutaId) return response()->json(['message'=>'No autoritzat'],403);
        Fisioterapeuta::findOrFail($fisioterapeutaId);
        $v = $request->validate(['contingut'=>'required|string|max:2000']);
        $m = Missatge::create(['fisioterapeuta_id'=>$fisioterapeutaId,'emissor_user_id'=>$user->id,'emissor_rol'=>$rol,'contingut'=>$v['contingut'],'llegit'=>false]);
        $m->load('emissor:id,name,avatar');
        return response()->json($m,201);
    }
    public function noLlegits(): JsonResponse
    {
        $user = Auth::user();
        $rol = $this->rolActual($user);
        if (!$rol) return response()->json(['count'=>0]);
        $q = Missatge::where('emissor_rol','!=',$rol)->where('llegit',false);
        if ($rol==='fisioterapeuta') $q->where('fisioterapeuta_id',$user->fisioterapeuta?->id);
        return response()->json(['count'=>$q->count()]);
    }
    public function fisiosPerAdmin(): JsonResponse
    {
        $user = Auth::user();
        if (!in_array($user->role,['admin','viewer'])) return response()->json(['message'=>'No autoritzat'],403);
        $fisios = Fisioterapeuta::with('user:id,name')->get()->map(function($fisio){
            $ult = Missatge::where('fisioterapeuta_id',$fisio->id)->latest()->first();
            $nl = Missatge::where('fisioterapeuta_id',$fisio->id)->where('emissor_rol','fisioterapeuta')->where('llegit',false)->count();
            return ['id'=>$fisio->id,'nom_complet'=>$fisio->nom_complet??"{$fisio->nom} {$fisio->cognoms}",'especialitat'=>$fisio->especialitat,'actiu'=>$fisio->actiu,'ultim_missatge'=>$ult?['contingut'=>$ult->contingut,'created_at'=>$ult->created_at,'emissor_rol'=>$ult->emissor_rol]:null,'no_llegits'=>$nl];
        });
        return response()->json($fisios);
    }
    private function rolActual($user): ?string
    {
        return match($user->role){'fisioterapeuta'=>'fisioterapeuta','admin'=>'admin',default=>null};
    }
}
