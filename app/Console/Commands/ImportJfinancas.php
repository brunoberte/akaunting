<?php

namespace App\Console\Commands;

use App\Models\Banking\Account;
use App\Models\Common\Company;
use Illuminate\Console\Command;
use Storage;
use Illuminate\Support\Debug\Dumper;

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
        $company = Company::query()->firstOrFail();

        $handle = fopen($this->argument('path'), 'r');

        $current_account_name = false;

        $row_type = '';
        while ($row = fgets($handle)) {
//            $this->info($row);

            $patterns_to_ignore = [
                '/^"\s?Extrato de Conta.*/',
                '/^"Lançamentos registrados no período de.*/',
                '/^"Data\s{3,}Número\s{3,}Favorecido.*/',
                '/^"Resumo\s{3}.*/',
                '/^"Total lançamentos: [\d.]{1,5}\s{3}.*/',
                '/^"Saldo Final: \s{3}.*/',
                '/^"jFinanças Pessoal\s{3}.*/',
                '/^"www\.jfinancas\.com\.br"/',
                '/^"Saldo em [0-9]{2}\/[0-9]{2}\/[0-9]{4} .*"$/',
                '/^""$/',
            ];
            foreach ($patterns_to_ignore as $pattern) {
                if (preg_match($pattern, $row, $matches)) {
                    continue 2;
                }
            }

            if (preg_match('/^"Conta: (.*)"$/', $row, $matches)) {
                $row_type = 'account_start';
                $current_account_name = $matches[1];
                $this->info('Account: ' . $current_account_name);

                //TODO: check if account exists
                Account::query()->firstOrCreate([
                    'name' => $current_account_name,
                    'company_id' => $company->id,
                    'currency_code' => 'BRL',
                ]);

                continue;
            }

            // "19/04/2017                       [De: Bradesco]           Transf erência                                    11.488,37      11.488,37   C"
            if (preg_match('/^"(\d{2}\/\d{2}\/\d{4})\s{3,}(.*)\s{3,}\(?([\d.]{1,10},[\d]{2})\)?\s*\(?([\d.]{1,10},[\d]{2})\)?\s*([CD])"$/', $row, $matches)) {
                [$skip, $date, $info, $amount, $balance, $credit_debit] = $matches;
                $info = $this->parseInfo($info);
                (new Dumper)->dump($matches);
                (new Dumper)->dump($info);

                $row_type = 'transaction';
                continue;
            }

            throw new \Exception('Invalid row: ' . $row);

        }

        return true;
    }

    private function parseInfo($info)
    {
        $info = array_filter(array_map('trim', explode("   ", $info)));

        $search = [
            'Transf erência',
            '(Div idido)',
            'Despesa:Serv iços:Internet',
            'Despesa:Serv iços:Telef one Celular',
            'Despesa:Banco:Pacote de Serv iços',
            'Despesa:Automóv el:IPVA',
            'Despesa:Automóv el:Financiamento',
            'Despesa:Automóv el:Acessórios',
            'Despesa:Automóv el:Combustív el',
        ];
        $replace = [
            'Transferência',
            '(Dividido)',
            'Despesa:Serviços:Internet',
            'Despesa:Serviços:Telefone Celular',
            'Despesa:Banco:Pacote de Serviços',
            'Despesa:Automóvel:IPVA',
            'Despesa:Automóvel:Financiamento',
            'Despesa:Automóvel:Acessórios',
            'Despesa:Automóvel:Combustível',
        ];
        return array_map(function($x) use($search, $replace) {
            return str_replace($search, $replace, $x);
        }, $info);
    }

}
