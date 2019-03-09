<?php

namespace App\Console\Commands;

use App\Models\Common\Company;
use App\Utilities\Overrider;
use Date;
use Illuminate\Console\Command;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;

class RecurringCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for recurring';
    
    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->today = Date::today();

        // Get all companies
        $companies = Company::all();

        foreach ($companies as $company) {
            // Set company id
            session(['company_id' => $company->id]);

            // Override settings and currencies
            Overrider::load('settings');
            Overrider::load('currencies');

            $company->setSettings();

            foreach ($company->recurring as $recurring) {
                foreach ($recurring->schedule() as $recur) {
                    $recur_date = Date::parse($recur->getStart()->format('Y-m-d'));

                    // Check if should recur today
                    if ($this->today->ne($recur_date)) {
                        continue;
                    }

                    $model = $recurring->recurable;

                    if (!$model) {
                        continue;
                    }

                    switch ($recurring->recurable_type) {
                        case 'App\Models\Expense\Payment':
                        case 'App\Models\Income\Revenue':
                            $model->cloneable_relations = [];

                            // Create new record
                            $clone = $model->duplicate();

                            $clone->parent_id = $model->id;
                            $clone->paid_at = $this->today->format('Y-m-d');
                            $clone->save();

                            break;
                    }
                }
            }
        }

        // Unset company_id
        session()->forget('company_id');
    }
}
