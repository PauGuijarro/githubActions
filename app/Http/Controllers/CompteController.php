<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compte;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompteController extends Controller
{
    public function crearCompte()
    {
        $compte = Compte::create();
        return response()->json($compte);
    }

    public function ingres(Request $request, $id)
    {
        $quantitat = $request->input('quantitat');

        if (!is_numeric($quantitat) || $quantitat <= 0 || $quantitat > 6000 || round($quantitat, 2) != $quantitat) {
            return response()->json(['error' => 'Quantitat no vàlida'], 400);
        }

        $compte = Compte::findOrFail($id);
        $compte->saldo += $quantitat;
        $compte->save();

        return response()->json($compte);
    }

    public function retirada(Request $request, $id)
    {
        $quantitat = $request->input('quantitat');

        if (!is_numeric($quantitat) || $quantitat <= 0 || $quantitat > 6000 || round($quantitat, 2) != $quantitat) {
            return response()->json(['error' => 'Quantitat no vàlida'], 400);
        }

        $compte = Compte::findOrFail($id);
        if ($compte->saldo >= $quantitat) {
            $compte->saldo -= $quantitat;
            $compte->save();
        }

        return response()->json($compte);
    }

    public function transferencia(Request $request)
    {
        $origen_id = $request->input('origen');
        $desti_id = $request->input('desti');
        $quantitat = $request->input('quantitat');

        if (!is_numeric($quantitat) || $quantitat <= 0 || $quantitat > 3000 || round($quantitat, 2) != $quantitat) {
            return response()->json(['error' => 'Quantitat no vàlida'], 400);
        }

        $origen = Compte::findOrFail($origen_id);
        $desti = Compte::findOrFail($desti_id);

        // Comprovar límit de transferències en el dia
        $transferitAvui = DB::table('transferencies')
            ->where('origen_id', $origen_id)
            ->whereDate('created_at', Carbon::today())
            ->sum('quantitat');

        if ($transferitAvui + $quantitat > 3000 || $origen->saldo < $quantitat) {
            return response()->json(['error' => 'Transferència no vàlida'], 400);
        }

        DB::transaction(function () use ($origen, $desti, $quantitat) {
            $origen->saldo -= $quantitat;
            $desti->saldo += $quantitat;

            $origen->save();
            $desti->save();

            DB::table('transferencies')->insert([
                'origen_id' => $origen->id,
                'desti_id' => $desti->id,
                'quantitat' => $quantitat,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return response()->json([
            'origen' => $origen->fresh(),
            'desti' => $desti->fresh()
        ]);
    }
}

