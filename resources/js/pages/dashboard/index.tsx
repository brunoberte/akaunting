import AppLayout from '@/layouts/app-layout';
import AccountsTable from '@/pages/dashboard/accounts-table';
import TopCategoriesChart from '@/pages/dashboard/top-categories-chart';
import ForecastChart from '@/pages/dashboard/forecast-chart';
import { Head } from '@inertiajs/react';
import Box from '@mui/material/Box';
import Grid from '@mui/material/Grid';
import CashflowChart from '@/pages/dashboard/cashflow-chart';

type accountType = {
    id: number,
    name: string,
    currency_code: string,
    balance: number,
}
type chartDataType = {
    color: string,
    name: string,
    value: number,
}

export default function Index({
    accounts: accounts,
    currencies: currencies,
    top_expense_categories: top_expense_categories,
    top_income_categories: top_income_categories,
}: {
    accounts: accountType[];
    currencies: string[];
    top_expense_categories: chartDataType[];
    top_income_categories: chartDataType[];
}) {
    return (
        <AppLayout>
            <Head title="Dashboard" />

            <Box sx={{ width: '100%', maxWidth: { sm: '100%', md: '1700px' } }}>
                <Grid container spacing={2}>
                    <Grid size={{ xs: 12, md: 8 }}>
                        <AccountsTable currencies={currencies} accounts={accounts}></AccountsTable>
                    </Grid>
                    <Grid size={{ xs: 12, md: 4 }}>
                        <Box mb={2}>
                            <TopCategoriesChart title="Expenses - last 90 days" chartData={top_expense_categories} />
                        </Box>
                        <Box mb={2}>
                            <TopCategoriesChart title="Income - last 90 days" chartData={top_income_categories} />
                        </Box>
                    </Grid>
                </Grid>
            </Box>

            <Box sx={{ width: '100%', maxWidth: { sm: '100%', md: '1700px' } }}>
                <Grid container spacing={2}>
                    <Grid size={{ xs: 12 }}>
                        <ForecastChart currencies={currencies} />
                    </Grid>
                </Grid>
            </Box>

            <Box sx={{ width: '100%', maxWidth: { sm: '100%', md: '1700px' } }}>
                <Grid container spacing={2}>
                    <Grid size={{ xs: 12 }}>
                        <CashflowChart currencies={currencies} />
                    </Grid>
                </Grid>
            </Box>
        </AppLayout>
    );
}
