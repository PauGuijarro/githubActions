<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_created_with_zero_balance()
    {
        $account = Account::create();
        $this->assertEquals(0.00, $account->balance);
    }

    public function test_deposit_adds_to_balance()
    {
        $account = Account::create();
        $account->deposit(100.00);
        $this->assertEquals(100.00, $account->fresh()->balance);
    }

    public function test_deposit_with_negative_amount_fails()
    {
        $account = Account::create();
        $account->deposit(-100.00);
        $this->assertEquals(0.00, $account->fresh()->balance);
    }

    public function test_deposit_with_more_than_two_decimals_fails()
    {
        $account = Account::create();
        $account->deposit(100.457);
        $this->assertEquals(0.00, $account->fresh()->balance);
    }

    public function test_deposit_over_maximum_fails()
    {
        $account = Account::create();
        $account->deposit(6000.01);
        $this->assertEquals(0.00, $account->fresh()->balance);
    }

    public function test_withdraw_subtracts_from_balance()
    {
        $account = Account::create(['balance' => 500.00]);
        $account->withdraw(100.00);
        $this->assertEquals(400.00, $account->fresh()->balance);
    }

    public function test_withdraw_more_than_balance_fails()
    {
        $account = Account::create(['balance' => 200.00]);
        $account->withdraw(500.00);
        $this->assertEquals(200.00, $account->fresh()->balance);
    }

    public function test_transfer_between_accounts()
    {
        $account1 = Account::create(['balance' => 500.00]);
        $account2 = Account::create(['balance' => 50.00]);
        $account1->transfer(100.00, $account2);
        $this->assertEquals(400.00, $account1->fresh()->balance);
        $this->assertEquals(150.00, $account2->fresh()->balance);
    }

    public function test_transfer_over_daily_limit_fails()
    {
        $account1 = Account::create(['balance' => 3500.00]);
        $account2 = Account::create(['balance' => 50.00]);
        $account1->transfer(3000.01, $account2);
        $this->assertEquals(3500.00, $account1->fresh()->balance);
        $this->assertEquals(50.00, $account2->fresh()->balance);
    }
}