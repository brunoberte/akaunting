<?php

namespace App\Models\Setting;

use App\Models\Model;
use Carbon\Carbon;
use Sofa\Eloquence\Eloquence;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $type
 * @property string $color
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Category extends Model
{
    use Eloquence;

    protected $searchableColumns = [
        'name' => 10,
    ];

    protected $table = 'categories';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'name', 'type', 'color', 'enabled'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['name', 'type', 'enabled'];

    public function items()
    {
        return $this->hasMany('App\Models\Common\Item');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Expense\Payment');
    }

    public function payments_last90days()
    {
        return $this->hasMany('App\Models\Expense\Payment')
            ->where('paid_at', '>=', Carbon::now()->startOfDay()->addDays(-90));
    }

    public function revenues()
    {
        return $this->hasMany('App\Models\Income\Revenue');
    }

    public function revenues_last90days()
    {
        return $this->hasMany('App\Models\Income\Revenue')
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

    /**
     * Scope transfer category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTransfer($query)
    {
        return $query->where('type', 'other')->pluck('id')->first();
    }
}
