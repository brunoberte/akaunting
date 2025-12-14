<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $number
 * @property string $currency_code
 * @property float $opening_balance
 * @property string $bank_name
 * @property string $bank_phone
 * @property string $bank_address
 * @property bool $enabled
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method \Illuminate\Database\Query\Builder enabled
 */
class Account extends AppModel
{
    use HasFactory;

    protected $table = 'accounts';

    protected $appends = ['balance'];

    protected $fillable = ['company_id', 'name', 'number', 'currency_code', 'opening_balance', 'bank_name', 'bank_phone', 'bank_address', 'enabled'];

    public function currency()
    {
        return $this->belongsTo('App\Models\Setting\Currency', 'currency_code', 'code');
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payables()
    {
        return $this->hasMany(Payable::class);
    }

    public function scopeEnabled($query) {
        return $query->where('enabled', true);
    }

    public function getBalanceAttribute(): float
    {
        //TODO: cache

        // Opening Balance
        $total = $this->opening_balance;

        // Sum revenues
        $total += Revenue::query()
            ->where('account_id', $this->id)
            ->sum('amount');

        // Subtract payments
        $total -= Payment::query()
            ->where('account_id', $this->id)
            ->sum('amount');

        return $total;
    }

    public function getBalanceOnDate(Carbon $date): float
    {
        // Opening Balance
        $total = $this->opening_balance;

        // Sum revenues
        $total += Revenue::query()
            ->where('account_id', $this->id)
            ->where('paid_at', '<=', $date->endOfDay()->format('Y-m-d H:i:s'))
            ->sum('amount');

        // Subtract payments
        $total -= Payment::query()
            ->where('account_id', $this->id)
            ->where('paid_at', '<=', $date->endOfDay()->format('Y-m-d H:i:s'))
            ->sum('amount');

        return $total;
    }
}
