<?php

namespace App\Traits;

use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;

trait Recurring
{

    public function createRecurring(array $data)
    {
        $frequency = ($data['recurring_frequency'] ?? 'no');

        if ($frequency == 'no') {
            return;
        }

        $interval = ($frequency != 'custom') ? 1 : (int)($data['recurring_interval'] ?? 1);
        $frequency = ($frequency != 'custom') ? $frequency : ($data['recurring_custom_frequency'] ?? 'monthly');
        $started_at = $data['paid_at'] ?? ($data['invoiced_at'] ?? ($data['billed_at'] ?? ($data['due_at'] ?? now())));

        $this->recurring()->create([
            'company_id' => session('company_id', 1),
            'frequency'  => $frequency,
            'interval'   => $interval,
            'started_at' => $started_at,
            'count'      => (int)($data['recurring_count'] ?? 0),
        ]);
    }

    public function updateRecurring(array $data)
    {
        $frequency = ($data['recurring_frequency'] ?? 'no');

        if ($frequency == 'no') {
            $this->recurring()->forceDelete();
            return;
        }

        $interval = ($frequency != 'custom') ? 1 : (int)($data['recurring_interval'] ?? 1);
        $frequency = ($frequency != 'custom') ? $frequency : ($data['recurring_custom_frequency'] ?? 'monthly');
        $started_at = $data['paid_at'] ?? ($data['invoiced_at'] ?? ($data['billed_at'] ?? ($data['due_at'] ?? now())));

        $recurring = $this->recurring();

        $recurringData = [
            'company_id' => session('company_id', 1),
            'frequency'  => $frequency,
            'interval'   => $interval,
            'started_at' => $started_at,
            'count'      => (int)($data['recurring_count'] ?? 0),
        ];

        if ($recurring->exists()) {
            $recurring->update($recurringData);
        } else {
            $recurring->create($recurringData);
        }
    }

    public function current()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return $schedule->current()->getStart();
    }

    public function next()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        if (!$next = $schedule->next()) {
            return false;
        }

        return $next->getStart();
    }

    public function first()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return $schedule->first()->getStart();
    }

    public function last()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return $schedule->last()->getStart();
    }

    public function schedule()
    {
        $config = new ArrayTransformerConfig();
        $config->enableLastDayOfMonthFix();

        $transformer = new ArrayTransformer();
        $transformer->setConfig($config);

        return $transformer->transform($this->getRule());
    }

    public function getRule()
    {
        $rule = (new Rule())
            ->setStartDate($this->getRuleStartDate())
            ->setTimezone($this->getRuleTimeZone())
            ->setFreq($this->getRuleFrequency())
            ->setInterval($this->interval);

        // 0 means infinite
        if ($this->count != 0) {
            $rule->setCount($this->getRuleCount());
        }

        return $rule;
    }

    public function getRuleStartDate()
    {
        return new \DateTime($this->started_at, new \DateTimeZone($this->getRuleTimeZone()));
    }

    public function getRuleTimeZone()
    {
        return setting('general.timezone');
    }

    public function getRuleCount()
    {
        // Fix for humans
        return $this->count + 1;
    }

    public function getRuleFrequency()
    {
        return strtoupper($this->frequency);
    }

    public function skipNext()
    {
        $recurring = $this->recurring;
        if (!$recurring) {
            return;
        }

        switch ($recurring->frequency) {
            case 'daily':
                $this->due_at = $this->due_at->addDays($recurring->interval);
                break;
            case 'weekly':
                $this->due_at = $this->due_at->addWeeks($recurring->interval);
                break;
            case 'monthly':
                $this->due_at = $this->due_at->addMonths($recurring->interval);
                break;
            case 'yearly':
                $this->due_at = $this->due_at->addYears($recurring->interval);
                break;
        }

        $this->save();

        if ($recurring->count > 0) {
            $recurring->count--;
            $recurring->save();
        }
    }
}
