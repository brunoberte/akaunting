<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $type
 * @property string $color
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method \Illuminate\Database\Query\Builder enabled
 */
class Category extends AppModel
{
    use HasFactory;

    protected $searchableColumns = [
        'name' => 10,
    ];

    protected $table = 'categories';

    protected $fillable = ['company_id', 'name', 'type', 'color', 'enabled'];

    public $sortable = ['name', 'type', 'enabled'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payables()
    {
        return $this->hasMany(Payable::class);
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    public function payments_last90days()
    {
        return $this->hasMany(Payment::class)
            ->where('paid_at', '>=', Carbon::now()->startOfDay()->addDays(-90));
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function revenues_last90days()
    {
        return $this->hasMany(Revenue::class)
            ->where('paid_at', '>=', Carbon::now()->startOfDay()->addDays(-90));
    }

    /**
     * Scope to only include categories of a given type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, $type)
    {
        return $query->whereIn('type', (array) $type);
    }

    public function scopeTransfer($query)
    {
        return $query->where('type', 'other')->pluck('id')->first();
    }
}
