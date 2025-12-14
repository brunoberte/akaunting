import { router } from '@inertiajs/react';
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
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
        <TableContainer component={Paper}>
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
    );
}
