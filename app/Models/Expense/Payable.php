<?php

namespace App\Models\Expense;

use App\Models\Banking\Account;
use App\Models\Model;
use App\Models\Setting\Category;
use App\Traits\DateTime;
use App\Traits\Media;
use App\Traits\Recurring;
use Bkwld\Cloner\Cloneable;
use Sofa\Eloquence\Eloquence;

class Payable extends Model
{
    use Cloneable, DateTime, Eloquence, Media, Recurring;

    protected $table = 'payables';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['attachment'];

    protected $dates = ['deleted_at', 'due_at'];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'account_id', 'due_at', 'amount', 'title', 'vendor_id', 'notes', 'category_id', 'currency_code'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['due_at', 'amount', 'title'];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchableColumns = [
        'title'     => 10,
        'notes'     => 2,
    ];

    protected $reconciled_amount = [];

    /**
     * Clonable relationships.
     *
     * @var array
     */
    public $cloneable_relations = ['recurring'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function recurring()
    {
        return $this->morphOne('App\Models\Common\Recurring', 'recurable');
    }

    public function scopeDue($query, $date)
    {
        return $query->where('due_at', '=', $date);
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
