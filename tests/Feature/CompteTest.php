<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Compte;

class CompteTest extends TestCase
{
    use RefreshDatabase;

    public function test_creacio_compte()
    {
        $response = $this->post('/compte');
        $response->assertStatus(200);
        $this->assertDatabaseCount('comptes', 1);
        $this->assertEquals(0, Compte::first()->saldo);
    }

    public function test_ingres_valid()
    {
        $compte = Compte::create();
        $response = $this->post("/compte/{$compte->id}/ingres", ['quantitat' => 100.45]);
        $response->assertStatus(200);
        $this->assertEquals(100.45, $compte->fresh()->saldo);
    }

    public function test_ingres_invalid_decimals()
    {
        $compte = Compte::create();
        $response = $this->post("/compte/{$compte->id}/ingres", ['quantitat' => 100.457]);
        $response->assertStatus(400);
        $this->assertEquals(0, $compte->fresh()->saldo);
    }

    public function test_retirada_major_que_saldo()
    {
        $compte = Compte::create(['saldo' => 100]);
        $response = $this->post("/compte/{$compte->id}/retirada", ['quantitat' => 200]);
        $response->assertStatus(200);
        $this->assertEquals(100, $compte->fresh()->saldo);
    }

    public function test_transferencia_valida()
    {
        $origen = Compte::create(['saldo' => 1000]);
        $desti = Compte::create(['saldo' => 0]);

        $response = $this->post('/transferencia', [
            'origen' => $origen->id,
            'desti' => $desti->id,
            'quantitat' => 200,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(800, $origen->fresh()->saldo);
        $this->assertEquals(200, $desti->fresh()->saldo);
    }
}
