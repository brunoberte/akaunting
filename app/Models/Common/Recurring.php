<?php

namespace App\Models\Common;

use App\Models\Model;
use App\Traits\Recurring as RecurringTrait;

class Recurring extends Model
{
    use RecurringTrait;

    protected $table = 'recurring';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'recurable_id', 'recurable_type', 'frequency', 'interval', 'started_at', 'count'];


    /**
     * Get all of the owning recurable models.
     */
    public function recurable()
    {
        return $this->morphTo();
    }

    public function toString() {

        if ($this->interval !== 1) {
            $frequency_translate = [
                'daily' => trans('recurring.days'),
                'weekly' => trans('recurring.weeks'),
                'monthly' => trans('recurring.months'),
                'yearly' => trans('recurring.years'),
            ];
            $frequency_label = $frequency_translate[$this->frequency];
            $every_label = trans('recurring.every');

            return "{$every_label} {$this->interval} {$frequency_label} - {$this->count}x";
        }
        return trans('recurring.' . $this->frequency) . " - {$this->count}x";
    }
}
