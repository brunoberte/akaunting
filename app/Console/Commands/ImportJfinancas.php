<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class ImportJfinancas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:jfinancas {path}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $handle = fopen($this->argument('path'), 'r');

        $current_account_name = false;

        while ($row = fgets($handle)) {
//            $this->info($row);

            $row_type = '';
            if (preg_match('/^"Conta: (.*)"$/', $row, $matches)) {
                $row_type = 'account_start';
                $current_account_name = $matches[1];
                $this->info('Account: ' . $current_account_name);

                //TODO: check if account exists

                continue;
            }

            if (preg_match('/^"Saldo em [0-9]{2}\/[0-9]{2}\/[0-9]{4} .*"$/', $row, $matches)) {
                $row_type = 'account_initial_balance';
                continue;
            }

            // "19/04/2017                       [De: Bradesco]           Transf erência                                    11.488,37      11.488,37   C"
            if (preg_match('/^"(\d{2}\/\d{2}\/\d{4}).*()\s*([\d.]{1,10},[\d]{2})\s*([\d.]{1,10},[\d]{2})\s*([CD])"$/', $row, $matches)) {
                $row_type = 'transaction';
                dd($row, $matches);
                continue;
            }

        }

        return true;
    }

}
