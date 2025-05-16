<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuentaController extends Controller
{
    // Crea una nueva cuenta con balance por defecto en 0
    public function crearCuenta()
    {
        $cuenta = Cuenta::create();
        return response()->json($cuenta);
    }

    // Realiza un ingreso a una cuenta específica
    public function ingresar(Request $request, $id)
    {
        $cantidad = $request->input('cantidad');

        // Validación de la cantidad ingresada
        if (
            !is_numeric($cantidad) ||
            $cantidad <= 0 ||
            $cantidad > 6000 ||
            round($cantidad, 2) != $cantidad
        ) {
            return response()->json(['error' => 'Cantidad no válida'], 400);
        }

        $cuenta = Cuenta::findOrFail($id);
        $cuenta->balance += $cantidad;
        $cuenta->save();

        return response()->json($cuenta);
    }

    // Realiza una retirada de saldo de una cuenta específica
    public function retirar(Request $request, $id)
    {
        $cantidad = $request->input('cantidad');

        // Validación de la cantidad retirada
        if (
            !is_numeric($cantidad) ||
            $cantidad <= 0 ||
            $cantidad > 6000 ||
            round($cantidad, 2) != $cantidad
        ) {
            return response()->json(['error' => 'Cantidad no válida'], 400);
        }

        $cuenta = Cuenta::findOrFail($id);

        // Solo retirar si hay saldo suficiente
        if ($cuenta->balance >= $cantidad) {
            $cuenta->balance -= $cantidad;
            $cuenta->save();
        }

        return response()->json($cuenta);
    }

    // Realiza una transferencia entre dos cuentas
    public function transferir(Request $request)
    {
        $cuenta_origen_id = $request->input('cuenta_origen');
        $cuenta_destino_id = $request->input('cuenta_destino');
        $cantidad = $request->input('cantidad');

        // Validación de la cantidad transferida
        if (
            !is_numeric($cantidad) ||
            $cantidad <= 0 ||
            $cantidad > 3000 ||
            round($cantidad, 2) != $cantidad
        ) {
            return response()->json(['error' => 'Cantidad no válida'], 400);
        }

        $origen = Cuenta::findOrFail($cuenta_origen_id);
        $destino = Cuenta::findOrFail($cuenta_destino_id);

        // Suma de lo transferido hoy desde la cuenta origen
        $transferidoHoy = DB::table('transferencias')
            ->where('cuenta_origen_id', $cuenta_origen_id)
            ->whereDate('created_at', Carbon::today())
            ->sum('cantidad');

        // Verificar límites diarios y saldo suficiente
        if ($transferidoHoy + $cantidad > 3000 || $origen->balance < $cantidad) {
            return response()->json(['error' => 'Transferencia no válida'], 400);
        }

        // Ejecutar la transferencia en una transacción
        DB::transaction(function () use ($origen, $destino, $cantidad) {
            $origen->balance -= $cantidad;
            $destino->balance += $cantidad;

            $origen->save();
            $destino->save();

            DB::table('transferencias')->insert([
                'cuenta_origen_id' => $origen->id,
                'cuenta_destino_id' => $destino->id,
                'cantidad' => $cantidad,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return response()->json([
            'origen' => $origen->fresh(),
            'destino' => $destino->fresh()
        ]);
    }
}
