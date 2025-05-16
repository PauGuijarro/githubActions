<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compte;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TDDController extends Controller
{
    public function crear()
    {
        $nouCompte = Compte::create();
        return response()->json($nouCompte);
    }

    public function ingressar(Request $request, $compteId)
    {
        $import = $request->input('quantitat');

        if (!is_numeric($import) || $import <= 0 || $import > 6000 || round($import, 2) != $import) {
            return response()->json(['error' => 'Import no vàlid'], 400);
        }

        $compte = Compte::findOrFail($compteId);
        $compte->saldo += $import;
        $compte->save();

        return response()->json($compte);
    }

    public function retirar(Request $request, $compteId)
    {
        $import = $request->input('quantitat');

        if (!is_numeric($import) || $import <= 0 || $import > 6000 || round($import, 2) != $import) {
            return response()->json(['error' => 'Import no vàlid'], 400);
        }

        $compte = Compte::findOrFail($compteId);

        if ($compte->saldo >= $import) {
            $compte->saldo -= $import;
            $compte->save();
        }

        return response()->json($compte);
    }

    public function transferir(Request $request)
    {
        $compteOrigenId = $request->input('origen');
        $compteDestiId = $request->input('desti');
        $import = $request->input('quantitat');

        if (!is_numeric($import) || $import <= 0 || $import > 3000 || round($import, 2) != $import) {
            return response()->json(['error' => 'Import no vàlid'], 400);
        }

        $compteOrigen = Compte::findOrFail($compteOrigenId);
        $compteDesti = Compte::findOrFail($compteDestiId);

        $importTransferitAvui = DB::table('transferencies')
            ->where('origen_id', $compteOrigenId)
            ->whereDate('created_at', Carbon::today())
            ->sum('quantitat');

        if ($importTransferitAvui + $import > 3000 || $compteOrigen->saldo < $import) {
            return response()->json(['error' => 'Transferència no vàlida'], 400);
        }

        DB::transaction(function () use ($compteOrigen, $compteDesti, $import) {
            $compteOrigen->saldo -= $import;
            $compteDesti->saldo += $import;

            $compteOrigen->save();
            $compteDesti->save();

            DB::table('transferencies')->insert([
                'origen_id' => $compteOrigen->id,
                'desti_id' => $compteDesti->id,
                'quantitat' => $import,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return response()->json([
            'origen' => $compteOrigen->fresh(),
            'desti' => $compteDesti->fresh()
        ]);
    }
}
