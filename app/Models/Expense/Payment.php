<?php

namespace App\Models\Expense;

use App\Models\Model;
use App\Models\Setting\Category;
use App\Traits\DateTime;
use App\Traits\Media;
use Bkwld\Cloner\Cloneable;
use Carbon\Carbon;
use Sofa\Eloquence\Eloquence;
use Date;

/**
 * @property int $id
 * @property int $company_id
 * @property int $account_id
 * @property Carbon $paid_at
 * @property float $amount
 * @property string $currency_code
 * @property float $currency_rate
 * @property int $vendor_id
 * @property string $description
 * @property int $category_id
 * @property string $reference
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property int $parent_id
 * @property bool $reconciled
 */
class Payment extends Model
{
    use Cloneable, DateTime, Eloquence, Media;

    protected $table = 'payments';

    protected $dates = ['deleted_at', 'paid_at'];

    protected $appends = ['type', 'is_transfer'];
    public function getTypeAttribute()
    {
        return 'Payment';
    }
    public function getIsTransferAttribute()
    {
        return $this->category->id == Category::transfer();
    }

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'account_id', 'paid_at', 'amount', 'currency_code', 'currency_rate', 'vendor_id', 'description', 'category_id', 'reference', 'parent_id'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['paid_at', 'amount', 'category.name', 'account.name'];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchableColumns = [
        'accounts.name',
        'categories.name',
        'vendors.name' ,
        'description'  ,
    ];

    /**
     * Clonable relationships.
     *
     * @var array
     */
    public $cloneable_relations = [];

    public function account()
    {
        return $this->belongsTo('App\Models\Banking\Account');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Setting\Category');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Setting\Currency', 'currency_code', 'code');
    }

//    public function transfers()
//    {
//        return $this->hasMany('App\Models\Banking\Transfer');
//    }

    public function transfer()
    {
        return $this->hasOne('App\Models\Banking\Transfer');
    }

    public function vendor()
    {
        return $this->belongsTo('App\Models\Expense\Vendor');
    }

    /**
     * Get only transfers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsTransfer($query)
    {
        return $query->where('category_id', '=', Category::transfer());
    }

    /**
     * Skip transfers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsNotTransfer($query)
    {
        return $query->where('category_id', '<>', Category::transfer());
    }

    public static function scopeLatest($query)
    {
        return $query->orderBy('paid_at', 'desc');
    }

    /**
     * Get the current balance.
     *
     * @return string
     */
    public function getAttachmentAttribute($value)
    {
        if (!empty($value) && !$this->hasMedia('attachment')) {
            return $value;
        } elseif (!$this->hasMedia('attachment')) {
            return false;
        }

        return $this->getMedia('attachment')->last();
    }
}
