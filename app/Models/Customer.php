<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property string $name
 * @property string $email
 * @property string $tax_number
 * @property string $phone
 * @property string $address
 * @property string $website
 * @property string $currency_code
 * @property bool $enabled
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string $reference
 *
 * @method \Illuminate\Database\Query\Builder enabled
 */
class Customer extends AppModel
{
    protected $table = 'customers';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'user_id', 'name', 'email', 'tax_number', 'phone', 'address', 'website', 'currency_code', 'reference', 'enabled'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['name', 'email', 'phone', 'enabled'];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchableColumns = [
        'name'    => 10,
        'email'   => 5,
        'phone'   => 2,
        'website' => 2,
        'address' => 1,
    ];

    public function revenues()
    {
        return $this->hasMany('App\Models\Income\Revenue');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Setting\Currency', 'currency_code', 'code');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Auth\User', 'user_id', 'id');
    }

    public function onCloning($src, $child = null)
    {
        $this->user_id = null;
    }

    public function getUnpaidAttribute()
    {
        $amount = 0;

        return $amount;
    }
}
