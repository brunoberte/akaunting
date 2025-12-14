<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
class Vendor extends AppModel
{
    use HasFactory;

    protected $table = 'vendors';

    protected $fillable = ['company_id', 'name', 'email', 'tax_number', 'phone', 'address', 'website', 'currency_code', 'reference', 'enabled'];

    public $sortable = ['name', 'email', 'phone', 'enabled'];

    protected $searchableColumns = [
        'name'    => 10,
        'email'   => 5,
        'phone'   => 2,
        'website' => 2,
        'address' => 1,
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
