<?php

namespace Tests\Feature;

use App\Models\Cuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuentaTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_created_with_zero_balance()
    {
        $cuenta = Cuenta::create();
        $this->assertEquals(0.00, $cuenta->balance);
    }

    public function test_deposit_adds_to_balance()
    {
        $cuenta = Cuenta::create();
        $cuenta->deposit(100.00);
        $this->assertEquals(100.00, $cuenta->fresh()->balance);
    }

    public function test_deposit_with_negative_amount_fails()
    {
        $cuenta = Cuenta::create();
        $cuenta->deposit(-100.00);
        $this->assertEquals(0.00, $cuenta->fresh()->balance);
    }

    public function test_deposit_with_more_than_two_decimals_fails()
    {
        $cuenta = Cuenta::create();
        $cuenta->deposit(100.457);
        $this->assertEquals(0.00, $cuenta->fresh()->balance);
    }

    public function test_deposit_over_maximum_fails()
    {
        $cuenta = Cuenta::create();
        $cuenta->deposit(6000.01);
        $this->assertEquals(0.00, $cuenta->fresh()->balance);
    }

    public function test_withdraw_subtracts_from_balance()
    {
        $cuenta = Cuenta::create(['balance' => 500.00]);
        $cuenta->withdraw(100.00);
        $this->assertEquals(400.00, $cuenta->fresh()->balance);
    }

    public function test_withdraw_more_than_balance_fails()
    {
        $cuenta = Cuenta::create(['balance' => 200.00]);
        $cuenta->withdraw(500.00);
        $this->assertEquals(200.00, $cuenta->fresh()->balance);
    }

    public function test_transfer_between_accounts()
    {
        $cuenta1 = Cuenta::create(['balance' => 500.00]);
        $cuenta2 = Cuenta::create(['balance' => 50.00]);
        $cuenta1->transfer(100.00, $cuenta2);
        $this->assertEquals(400.00, $cuenta1->fresh()->balance);
        $this->assertEquals(150.00, $cuenta2->fresh()->balance);
    }

    public function test_transfer_over_daily_limit_fails()
    {
        $cuenta1 = Cuenta::create(['balance' => 3500.00]);
        $cuenta2 = Cuenta::create(['balance' => 50.00]);
        $cuenta1->transfer(3000.01, $cuenta2);
        $this->assertEquals(3500.00, $cuenta1->fresh()->balance);
        $this->assertEquals(50.00, $cuenta2->fresh()->balance);
    }
}
