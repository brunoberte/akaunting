<?php

namespace App\Models\Common;

use App\Models\Model;
use App\Traits\Recurring as RecurringTrait;
use Carbon\Carbon;

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
        $return = '';

        if ($this->interval !== 1) {
            $frequency_translate = [
                'daily' => trans('recurring.days'),
                'weekly' => trans('recurring.weeks'),
                'monthly' => trans('recurring.months'),
                'yearly' => trans('recurring.years'),
            ];
            $frequency_label = $frequency_translate[$this->frequency];
            $every_label = trans('recurring.every');

            $return .= "{$every_label} {$this->interval} {$frequency_label}";
        } else {
            $return .= trans('recurring.' . $this->frequency);
        }
        if ($this->count > 0) {
            $return .= " - {$this->count}x";
        }

        return $return;
    }

    public function getNextDate() {

        if (!isset($this->temp_count)) {
            $this->temp_count = $this->count;
        }
        if (!isset($this->temp_current_date)) {
            $this->temp_current_date = Carbon::parse($this->started_at)->startOfDay();
        }

        if ($this->count > 1) {
            // check
            if ($this->temp_count === 1) {
                return false;
            }
            $this->temp_count--;
        }

        switch ($this->frequency) {
            case 'daily':
                $this->temp_current_date->addDays($this->interval);
                break;
            case 'weekly':
                $this->temp_current_date->addWeeks($this->interval);
                break;
            case 'monthly':
                $this->temp_current_date->addMonths($this->interval);
                break;
            case 'yearly':
                $this->temp_current_date->addYears($this->interval);
                break;
        }

        return $this->temp_current_date;
    }
}
