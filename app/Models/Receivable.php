<?php
namespace App\Models;

use App\Traits\Recurring as RecurringTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receivable extends AppModel
{
//    use Cloneable, DateTime, Eloquence, Media,
    use RecurringTrait;
    use HasFactory;

    protected $table = 'receivables';

//    protected $appends = ['attachment'];

    protected $dates = ['deleted_at', 'due_at'];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    protected $fillable = ['company_id', 'account_id', 'due_at', 'amount', 'title', 'customer_id', 'notes', 'category_id', 'currency_code'];

    public $sortable = ['due_at', 'amount', 'title'];


    protected $searchableColumns = [
        'title' => 10,
        'notes' => 2,
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function recurring()
    {
        return $this->morphOne(Recurring::class, 'recurable');
    }

    public function scopeDue($query, $date)
    {
        return $query->where('due_at', '=', $date);
    }

//    public function getAttachmentAttribute($value)
//    {
//        if (!empty($value) && !$this->hasMedia('attachment')) {
//            return $value;
//        } elseif (!$this->hasMedia('attachment')) {
//            return false;
//        }
//
//        return $this->getMedia('attachment')->last();
//    }

    public function scopeFilter($query, $filter)
    {
        return $query->when($filter, function ($query, $value) {
            return $query->where('title', 'like', "%{$value}%");
        });
    }

    public function toArrayResponse(): array
    {
        return [
            'id'                  => $this->id,
            'account_id'          => $this->account_id,
            'due_at'              => $this->due_at->format('Y-m-d'),
            'currency_code'       => $this->currency_code,
            'amount'              => $this->amount,
            'title'               => $this->title,
            'customer_id'         => $this->customer_id,
            'category_id'         => $this->category_id,
            'notes'               => $this->notes,
            'recurring_frequency' => $this->recurring?->frequency,
            'recurring_interval'  => $this->recurring?->interval,
            'recurring_count'     => $this->recurring?->count,
        ];
    }
}
