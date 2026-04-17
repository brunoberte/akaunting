import { router } from '@inertiajs/react';
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import * as React from 'react';
import TableFooter from '@mui/material/TableFooter';

function renderMoneyColumn(currency: string, account: accountType) {
    if (account.currency_code != currency) {
        return '';
    }
    return formatNumber(account.balance);
}

function formatNumber(value: number) {
    return new Intl.NumberFormat('en-US', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        currencySign: 'accounting',
    }).format(value);
}

type accountType = {
    id: number,
    name: string,
    currency_code: string,
    balance: number,
}

export default function AccountsTable({
    accounts: accounts,
    currencies: currencies,
}: {
    currencies: string[];
    accounts: accountType[];
}) {
    const totals = accounts.reduce<Record<string, number>>((acc, account) => {
        acc[account.currency_code] = (acc[account.currency_code] || 0) + account.balance;
        return acc;
    }, {});
    return (
        <>
            {/* Desktop and Tablet view */}
            <TableContainer component={Paper} sx={{ display: { xs: 'none', sm: 'block' } }}>
                <Table size="small">
                    <TableHead>
                        <TableRow>
                            <TableCell>Account Name</TableCell>
                            {currencies.map((currency) => (
                                <TableCell key={currency}>{currency}</TableCell>
                            ))}
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {accounts.map((row) => (
                            <TableRow key={row.id} sx={{cursor: "pointer"}} onClick={() => router.visit(route('transactions.index', { account_id: row.id }))} hover>
                                <TableCell>{row.name}</TableCell>
                                {currencies.map((currency) => (
                                    <TableCell key={currency}>{renderMoneyColumn(currency, row)}</TableCell>
                                ))}
                            </TableRow>
                        ))}
                    </TableBody>
                    <TableFooter>
                        <TableRow>
                            <TableCell>Total</TableCell>
                            {currencies.map((currency) => (
                                <TableCell key={currency}>{formatNumber(totals[currency])}</TableCell>
                            ))}
                        </TableRow>
                    </TableFooter>
                </Table>
            </TableContainer>

            {/* Mobile view */}
            <Box sx={{ display: { xs: 'block', sm: 'none' } }}>
                <Typography variant="h6" sx={{ mb: 2, fontWeight: 'bold' }}>
                    Accounts
                </Typography>
                {accounts.map((row) => (
                    <Card
                        key={row.id}
                        sx={{
                            mb: 2,
                            cursor: 'pointer',
                            '&:hover': { boxShadow: 4 }
                        }}
                        onClick={() => router.visit(route('transactions.index', { account_id: row.id }))}
                    >
                        <CardContent>
                            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                                <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                                    {row.name}
                                </Typography>
                                <Typography variant="caption" color="text.secondary">
                                    {row.currency_code}
                                </Typography>
                            </Box>
                            <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
                                <Typography
                                    variant="h6"
                                    sx={{
                                        color: row.balance >= 0 ? 'success.main' : 'error.main',
                                        fontWeight: 'medium'
                                    }}
                                >
                                    {formatNumber(row.balance)}
                                </Typography>
                            </Box>
                        </CardContent>
                    </Card>
                ))}

                {/* Totals Section for Mobile */}
                <Paper sx={{ p: 2, mt: 2, backgroundColor: (theme) => theme.palette.action.hover }}>
                    <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                        Total Balances
                    </Typography>
                    {currencies.map((currency) => (
                        <Box key={currency} sx={{ display: 'flex', justifyContent: 'space-between', mb: 0.5 }}>
                            <Typography variant="body2">{currency}:</Typography>
                            <Typography
                                variant="body2"
                                sx={{
                                    fontWeight: 'bold',
                                    color: (totals[currency] || 0) >= 0 ? 'success.main' : 'error.main'
                                }}
                            >
                                {formatNumber(totals[currency] || 0)}
                            </Typography>
                        </Box>
                    ))}
                </Paper>
            </Box>
        </>
    );
}
