<?php

namespace App\Console\Commands;

use App\Models\Banking\Account;
use App\Models\Banking\Transfer;
use App\Models\Common\Company;
use App\Models\Expense\Payment;
use App\Models\Expense\Vendor;
use App\Models\Income\Customer;
use App\Models\Income\Receivable;
use App\Models\Income\Revenue;
use App\Models\Setting\Category;
use Carbon\Carbon;
use function GuzzleHttp\Psr7\str;
use Illuminate\Console\Command;
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
        /** @var Company $company */
        $company = Company::query()->firstOrFail();

        $handle = fopen($this->argument('path'), 'r');

        /** @var Account $current_account */
        $current_account = false;
        $start_pos = false;

        while ($row = fgets($handle)) {
//            $this->info($row);

            $patterns_to_ignore = [
                '/^"\s?Extrato de Conta.*/',
                '/^"Lançamentos registrados no período de.*/',
                '/^"Resumo\s{3}.*/',
                '/^"Resumo Geral\s{3}.*/',
                '/^"Total lançamentos: [\d.]{1,5}\s{3}.*/',
                '/^"Saldo Final: \s{3}.*/',
                '/^"Total de Saídas:.*/',
                '/^"Total lançamentos:.*/',
                '/^"Saldo Final:.*/',
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
                $this->info('Account: ' . $matches[1]);

                //Get account or create account
                $current_account = Account::query()->firstOrCreate([
                    'name' => $matches[1],
                    'company_id' => $company->id,
                    'currency_code' => 'BRL',
                ]);

                continue;
            }

            if (preg_match('/^"Data\s{3,}Número\s{3,}Favorecido.*/', $row, $matches)) {
                $start_pos = [
                    strpos($row, 'Data'),
                    strpos($row, 'Favorecido') - 1,
                    strpos($row, 'Categoria') - 1,
                    strpos($row, 'Valor') - 7,
                ];
                continue;
            }


            // "19/04/2017                       [De: Bradesco]           Transf erência                                    11.488,37      11.488,37   C"
            if (preg_match('/^"(\d{2}\/\d{2}\/\d{4})\s{3,}(.*)\s{3,}\(?([\d.]{1,10},[\d]{2})\)?\s*\(?([\d.]{1,10},[\d]{2})\)?\s*([CD])"$/mU', $row, $matches)) {

                $date = Carbon::createFromFormat('!d/m/Y', trim(substr($row, $start_pos[0], 10)));
                $favorecido = $this->fix_name(trim(substr($row, $start_pos[1], $start_pos[2] - $start_pos[1] - 1)));
                $categoria_raw = substr($row, $start_pos[2], $start_pos[3] - $start_pos[2] - 1);
                $categoria = $this->fix_name(trim($categoria_raw));
                $credit_debit = trim(substr(trim($row), -2, 1));

                $row_1 = substr($row, $start_pos[2] + strlen($categoria_raw) + 1);
                $row_2 = substr(trim($row_1), 0, -2);
                $row_3 = explode(' ', preg_replace('/\s{2,}/', ' ', $row_2));

                $amount = $this->fix_amount($row_3[0]);
                $description = $row_3[1];

                if (strpos($row, ' ' . $row_3[0] . ' ') === false) {
                    dd($row, $row_1, $row_2, $row_3, $start_pos, $categoria_raw);
                    throw new \Exception('asdlkajsdlkasjd');
                }

//                (new Dumper)->dump($row);
//                (new Dumper)->dump($date);
//                (new Dumper)->dump($favorecido);
//                (new Dumper)->dump($categoria);
//                (new Dumper)->dump($categoria_raw);
//                (new Dumper)->dump($amount);
//                (new Dumper)->dump($description);
//                (new Dumper)->dump($credit_debit);

                switch ($categoria) {
                    case 'Transferência':
                        if ($credit_debit == 'C') {
                            $from_account = Account::query()
                                ->where('company_id', $company->id)
                                ->where('name', $this->fix_name(trim(substr(str_replace('[De: ', '', $favorecido), 0, -1))))
                                ->firstOrFail();

                            $payment = $this->createPayment($favorecido, $company, 'Transfer', $from_account, $date, $amount, $description);
                            $revenue = $this->createRevenue($favorecido, $company, 'Transfer', $current_account, $date, $amount, $description);

                            Transfer::query()->firstOrCreate([
                                'company_id' => $company->id,
                                'payment_id' => $payment->id,
                                'revenue_id' => $revenue->id,
                            ]);
                        }
                        break;
                    default:
                        switch ($credit_debit) {
                            case 'D':
                                $this->createPayment($favorecido, $company, $categoria, $current_account, $date, $amount, $description);
                                break;
                            case 'C':
                                $this->createRevenue($favorecido, $company, $categoria, $current_account, $date, $amount, $description);
                                break;
                        }
                }
                continue;
            }

            throw new \Exception('Invalid row: ' . $row);

        }

        return true;
    }

    private function fix_name($str)
    {
        if (empty($str)) {
            return '';
        }

        $search = [
            'Transf erência' => 'Transferência',
            '(Div idido)' => '(Dividido)',
            'HSBC - MasterCard' => 'HSBC - MasterCard',
            'HSBC - Inv .' => 'HSBC - Inv.',
            'HSBC - Inv . MM' => 'HSBC - Inv. MM',
            'Bradesco   - FIC DI Topázio' => 'Bradesco - FIC DI Topázio',
            'Bradesco - Prev idencia' => 'Bradesco - Previdencia',
            'Bradesco - FIC DI Hiperf undo' => 'Bradesco - FIC DI Hiperfundo',
            'Despesa:Serv iços:Internet' => 'Despesa:Serviços:Internet',
            'Despesa:Serv iços:Telef one Celular' => 'Despesa:Serviços:Telefone Celular',
            'Despesa:Banco:Pacote de Serv iços' => 'Despesa:Banco:Pacote de Serviços',
            'Despesa:Automóv el' => 'Despesa:Automóvel',
            'Despesa:Automóv el:Acidente' => 'Despesa:Automóvel:Acidente',
            'Despesa:Automóv el:Estacionamento' => 'Despesa:Automóvel:Estacionamento',
            'Despesa:Automóv el:LICENCIAMENTO/DPV' => 'Despesa:Automóvel:LICENCIAMENTO/DPVAT',
            'Despesa:Automóv el:Lav a-car' => 'Despesa:Automóvel:Lava-car',
            'Despesa:Automóvel:Lav a-car' => 'Despesa:Automóvel:Lava-car',
            'Despesa:Automóv el:Manutenção' => 'Despesa:Automóvel:Manutenção',
            'Despesa:Automóv el:Multa' => 'Despesa:Automóvel:Multa',
            'Despesa:Automóv el:Seguro' => 'Despesa:Automóvel:Seguro',
            'Despesa:Automóv el:IPVA' => 'Despesa:Automóvel:IPVA',
            'Despesa:Automóv el:Financiamento' => 'Despesa:Automóvel:Financiamento',
            'Despesa:Automóv el:Acessórios' => 'Despesa:Automóvel:Acessórios',
            'Despesa:Automóv el:Combustív el' => 'Despesa:Automóvel:Combustível',
            'Despesa:Automóvel:Combustív el' => 'Despesa:Automóvel:Combustível',
            'Despesa:Alimentação:Caf é da manhã' => 'Despesa:Alimentação:Café da manhã',
            'Despesa:EI - Bruno Stof f el' => 'Despesa:EI - Bruno Stoffel',
            'Alv orada autopeças' => 'Alvorada autopeças',
            'A2Y OU' => 'A2YOU',
            'Carref our' => 'Carrefour',
            'Carroreserv a' => 'Carroreserva',
            'Elev a BD' => 'Eleva BD',
            'Elev a BD - Luciana Correa' => 'Eleva BD - Luciana Correa',
            'ForLif e Assessoria' => 'ForLife Assessoria',
            'Jef erson Luis Scarpim' => 'Jeferson Luis Scarpim',
            'Posto Positiv o' => 'Posto Positivo',
            'Despesa:Educação:Liv ro' => 'Despesa:Educação:Livro',
            'Receita:Outros:Reembolso   Mãe' => 'Receita:Outros:Reembolso Mãe',
            'Despesa:Outras   Despesas' => 'Despesa:Outras Despesas',
            'Despesa:Outras   Despesas:Roupas' => 'Despesa:Outras Despesas:Roupas',
            'Despesa:Outras   Despesas:Empréstimo -' => 'Despesa:Outras Despesas:Empréstimo -',
            'Despesa:Outras   Despesas:Presente' => 'Despesa:Outras Despesas:Presente',
            'Despesa:Outras   Despesas:Empréstimo' => 'Despesa:Outras Despesas:Empréstimo',
            'Despesa:Cartão   de crédito' => 'Despesa:Cartão de crédito',
            'Despesa:Cartão   de crédito:Anuidade' => 'Despesa:Cartão de crédito:Anuidade',
            'Despesa:Serviços:Telefone Celular:Serv iços' => 'Despesa:Serviços:Telefone Celular:Serviços',
            'Despesa:Serviços:Telefone Celular:Serv iç' => 'Despesa:Serviços:Telefone Celular:Serviços',
            'Despesa:Casa:Móv eis' => 'Despesa:Casa:Móveis',
            'Despesa:Serv iços' => 'Despesa:Serviços',
            'Despesa:Serv iços:Hospedagem de site' => 'Despesa:Serviços:Hospedagem de site',
            'Despesa:Serv iços:Registro de dominio' => 'Despesa:Serviços:Registro de dominio',
            'Despesa:Serv iços:Telef one Celular:Conta' => 'Despesa:Serviços:Telefone Celular:Conta',
            'Despesa:Serv iços:Telef one Celular:Aparelho' => 'Despesa:Serviços:Telefone Celular:Aparelho',
            'Despesa:Serv iços:Telef one Celular:Serv iç' => 'Despesa:Serviços:Telef one Celular:Serviços',
            'Despesa:Serv iços:Telef one Celular:Serv iços' => 'Despesa:Serviços:Telef one Celular:Serviços',
            'Receita:Folha de Pagamento:Salário Líquid' => 'Receita:Folha de Pagamento:Salário Líquido',
            'Alan Silv a dos Santos' => 'Alan Silva dos Santos',
            'Easy nv est' => 'Easynvest',
            'Fabio Alv es' => 'Fabio Alves',
            'HAZE   CC' => 'HAZE CC',
            'Hy undai Caoa' => 'Hyundai Caoa',
            'Herbalif e' => 'Herbalife',
            'Hav an' => 'Havan',
            'Liv rarias Curitiba' => 'Livrarias Curitiba',
            'Maf agaf os' => 'Mafagafos',
            'Nado Liv re' => 'Nado Livre',
            'Posto   Top   Gas' => 'Posto Top Gas',
            'Mercado Liv re' => 'Mercado Livre',
            'Prata f ina' => 'Prata fina',
            'Saraiv a' => 'Saraiva',
            'Via Av entura' => 'Via Aventura',
            'Viv o' => 'Vivo',
            'Sky pe' => 'Skype',
            'pontof rio.com.br' => 'pontofrio.com.br',
            '<Div ersos>' => '<Diversos>',
        ];

        return $search[$str] ?? $str;
    }

    private function fix_amount(string $val)
    {
        $new_val = str_replace(['.', '(', ')'], '', $val);
        return floatval(str_replace(',', '.', $new_val));
    }

    private function getCustomer(string $name, Company $company): Customer
    {
        if (empty($name)) {
            $name = '<Diversos>';
        }
        return Customer::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => $name,
                'currency_code' => 'BRL',
            ]);
    }

    private function getVendor(string $name, Company $company): Vendor
    {
        if (empty($name)) {
            $name = '<Diversos>';
        }
        return Vendor::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => $name,
                'currency_code' => 'BRL',
            ]);
    }

    private function getCategory(string $name, string $type, Company $company): Category
    {
        if (empty($name)) {
            $name = '<Diversos>';
        }
        if ($name == 'Transfer') {
            $type = 'other';
        }
        return Category::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => $name,
                'type' => $type,
            ],
            [
                'color' => '#f39c12',
                'enabled' => 1,
            ]);
    }

    private function createPayment(string $favorecido, Company $company, string $categoria, Account $current_account, Carbon $date, float $amount, string $description): Payment
    {
        $vendor = $this->getVendor($favorecido, $company);
        $category = $this->getCategory($categoria, 'expense', $company);

        $attributes = [
            'company_id' => $company->id,
            'account_id' => $current_account->id,
            'paid_at' => $date,
            'currency_code' => 'BRL',
            'currency_rate' => 1,
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'description' => $description,
        ];

        $r = Payment::query()->where($attributes)
            ->whereRaw("amount = {$amount}")
            ->first();

        if ($r) {
            return $r;
        }
        $r = Payment::query()->create($attributes + ['amount' => $amount]);

        $r->created_at = $date->copy()->setTime(12, 0, 0);
        $r->save();
        return $r;
    }

    private function createRevenue(string $favorecido, Company $company, string $categoria, Account $current_account, Carbon $date, float $amount, string $description): Revenue
    {
        $customer = $this->getCustomer($favorecido, $company);
        $category = $this->getCategory($categoria, 'income', $company);

        $attributes = [
            'company_id' => $company->id,
            'account_id' => $current_account->id,
            'paid_at' => $date->format('Y-m-d H:i:s'),
            'currency_code' => 'BRL',
            'currency_rate' => 1,
            'customer_id' => $customer->id,
            'category_id' => $category->id,
            'description' => $description,
        ];
        $r = Revenue::query()->where($attributes)
            ->whereRaw("amount = {$amount}")
            ->first();

        if ($r) {
            return $r;
        }
        $r = Revenue::query()->create($attributes + ['amount' => $amount]);

        $r->created_at = $date->copy()->setTime(12, 0, 0);
        $r->save();
        return $r;
    }

}
