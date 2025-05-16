<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    public function deposit($amount)
    {
        if ($amount <= 0 || $amount > 6000 || $this->hasMoreThanTwoDecimals($amount)) {
            return false;
        }
        $this->balance += $amount;
        $this->save();
        return true;
    }

    public function withdraw($amount)
    {
        if ($amount <= 0 || $amount > $this->balance || $amount > 6000 || $this->hasMoreThanTwoDecimals($amount)) {
            return false;
        }
        $this->balance -= $amount;
        $this->save();
        return true;
    }

    public function transfer($amount, Account $toAccount)
    {
        if ($amount <= 0 || $amount > 3000 || $amount > $this->balance || $this->hasMoreThanTwoDecimals($amount)) {
            return false;
        }
        $this->balance -= $amount;
        $toAccount->balance += $amount;
        $this->save();
        $toAccount->save();
        return true;
    }

    private function hasMoreThanTwoDecimals($amount)
    {
        return preg_match('/\.\d{3,}$/', (string) $amount);
    }
}
