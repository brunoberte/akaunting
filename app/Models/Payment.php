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
class Payment extends AppModel
{
    use HasFactory;

    protected $table = 'payments';

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    protected $dates = ['deleted_at', 'paid_at'];

    protected $appends = ['type', 'is_transfer'];

    public function getTypeAttribute()
    {
        return 'Payment';
    }

    public function getIsTransferAttribute()
    {
        return $this->category?->id == Category::transfer();
    }

    protected $fillable = ['company_id', 'account_id', 'paid_at', 'amount', 'currency_code', 'currency_rate', 'vendor_id', 'description', 'category_id', 'reference', 'parent_id'];

    public $sortable = ['paid_at', 'amount', 'category.name', 'account.name'];

    protected $searchableColumns = [
        'accounts.name',
        'categories.name',
        'vendors.name',
        'description',
    ];

    public $cloneable_relations = [];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

//    public function transfers()
//    {
//        return $this->hasMany('App\Models\Banking\Transfer');
//    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeIsTransfer($query)
    {
        return $query->where('category_id', '=', Category::transfer());
    }

    public function scopeIsNotTransfer($query)
    {
        return $query->where('category_id', '<>', Category::transfer());
    }

    public static function scopeLatest($query)
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
