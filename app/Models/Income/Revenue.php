<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Models\Setting\Category;
use App\Traits\DateTime;
use App\Traits\Media;
use Bkwld\Cloner\Cloneable;
use Sofa\Eloquence\Eloquence;

class Revenue extends Model
{
    use Cloneable, DateTime, Eloquence, Media;

    protected $table = 'revenues';

    protected $dates = ['deleted_at', 'paid_at'];

    protected $appends = ['type', 'is_transfer'];
    public function getTypeAttribute()
    {
        return 'Revenue';
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
    protected $fillable = ['company_id', 'account_id', 'paid_at', 'amount', 'currency_code', 'currency_rate', 'customer_id', 'description', 'category_id', 'reference', 'parent_id'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['paid_at', 'amount','category_id', 'account'];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchableColumns = [
        'invoice_number' => 10,
        'order_number'   => 10,
        'customer_name'  => 10,
        'customer_email' => 5,
        'notes'          => 2,
    ];

    /**
     * Clonable relationships.
     *
     * @var array
     */
    public $cloneable_relations = [];

    public function user()
    {
        return $this->belongsTo('App\Models\Auth\User', 'customer_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Banking\Account');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Setting\Currency', 'currency_code', 'code');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Setting\Category');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function transfers()
    {
        return $this->hasMany('App\Models\Banking\Transfer');
    }

    public function transfer()
    {
        return $this->hasOne('App\Models\Banking\Transfer');
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

    public function scopeLatest($query)
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
