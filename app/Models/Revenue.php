<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $company_id
 * @property int $account_id
 * @property Carbon $paid_at
 * @property float $amount
 * @property string $currency_code
 * @property float $currency_rate
 * @property int $customer_id
 * @property string $description
 * @property int $category_id
 * @property string $reference
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property int $parent_id
 * @property bool $reconciled
 */
class Revenue extends AppModel
{
    use HasFactory;

    protected $table = 'revenues';

    protected $casts = [
        'paid_at' => 'datetime',
    ];
    protected $dates = ['deleted_at', 'paid_at'];

    protected $appends = ['type', 'is_transfer'];

    public function getTypeAttribute()
    {
        return 'Revenue';
    }

    public function getIsTransferAttribute()
    {
        return $this->category?->id == Category::transfer();
    }

    protected $fillable = ['company_id', 'account_id', 'paid_at', 'amount', 'currency_code', 'currency_rate', 'customer_id', 'description', 'category_id', 'reference', 'parent_id'];

    public $sortable = ['paid_at', 'amount', 'category_id', 'account'];

    protected $searchableColumns = [
        'invoice_number' => 10,
        'order_number'   => 10,
        'customer_name'  => 10,
        'customer_email' => 5,
        'notes'          => 2,
    ];

    public $cloneable_relations = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class);
    }

    public function scopeIsTransfer($query)
    {
        return $query->where('category_id', '=', Category::transfer());
    }

    public function scopeIsNotTransfer($query)
    {
        return $query->where('category_id', '<>', Category::transfer());
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('paid_at', 'desc');
    }

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
