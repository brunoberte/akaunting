import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import AddIcon from '@mui/icons-material/Add';
import ArrowDownwardIcon from '@mui/icons-material/ArrowDownward';
import ArrowUpwardIcon from '@mui/icons-material/ArrowUpward';
import FirstPageIcon from '@mui/icons-material/FirstPage';
import KeyboardArrowLeft from '@mui/icons-material/KeyboardArrowLeft';
import KeyboardArrowRight from '@mui/icons-material/KeyboardArrowRight';
import LastPageIcon from '@mui/icons-material/LastPage';
import Autocomplete from '@mui/material/Autocomplete';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import FormControl from '@mui/material/FormControl';
import Grid from '@mui/material/Grid';
import IconButton from '@mui/material/IconButton';
import Paper from '@mui/material/Paper';
import Stack from '@mui/material/Stack';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableFooter from '@mui/material/TableFooter';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import TextField from '@mui/material/TextField';
import Chip from '@mui/material/Chip';
import Typography from '@mui/material/Typography';
import dayjs from 'dayjs';
import * as React from 'react';

type TransactionType = {
    id: number;
    account_id: number;
    category_id: number;
    customer_id: number | null;
    vendor_id: number | null;
    credit: number | null;
    debit: number | null;
    currency_code: string;
    description: string | null;
    is_transfer: boolean;
    transfer_account_id: number | null;
    paid_at: string;
    record_type: string;
};

type CategoryType = { id: number; name: string; type: string; color: string };

export default function ByCategory({
    category_id,
    category,
    pagination_data,
    category_list,
}: {
    category_id: number;
    category: CategoryType | null;
    pagination_data: {
        data: TransactionType[];
        first_page_url: string;
        next_page_url: string | null;
        last_page_url: string;
        prev_page_url: string | null;
        path: string;
        current_page: number;
        from: number | null;
        to: number | null;
        total: number;
        per_page: number;
        last_page: number;
    } | null;
    category_list: Array<CategoryType>;
}) {
    const [selectedCategoryObj, setSelectedCategoryObj] = React.useState<CategoryType | null>(
        category_list.find((c) => c.id === category_id) || null,
    );

    const handleCategoryChange = (new_category_id: number | null) => {
        router.visit(route('transactions.index_by_category', { category_id: new_category_id, page: 1 }), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const formatDate = (dateString: string) => {
        return dayjs(dateString).format('DD MMM YYYY');
    };

    const formatNumber = (amount: number | null, currencyCode: string) => {
        if (amount === null) return '-';
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currencyCode || 'USD',
        }).format(amount);
    };

    const totalAmount = pagination_data?.data.reduce((acc, item) => {
        return acc + (item.credit || 0) - (item.debit || 0);
    }, 0) || 0;

    return (
        <AppLayout>
            <Head title="Transactions by Category" />
            <PageContainer
                title="Transactions"
                breadcrumbs={[{ label: 'Transactions', href: route('transactions.index') }, { label: 'By Category' }]}
                actions={
                    <Stack direction="row" spacing={1}>
                        <Button
                            component={Link}
                            variant="contained"
                            href={route('transactions.payments.new')}
                            startIcon={<AddIcon />}
                            size={'small'}
                        >
                            Payment
                        </Button>
                        <Button
                            component={Link}
                            variant="contained"
                            href={route('transactions.revenues.new')}
                            startIcon={<AddIcon />}
                            size={'small'}
                        >
                            Revenue
                        </Button>
                        <Button
                            component={Link}
                            variant="contained"
                            href={route('transactions.transfers.new')}
                            startIcon={<AddIcon />}
                            size={'small'}
                        >
                            Transfer
                        </Button>
                    </Stack>
                }
            >
                <Stack spacing={3}>
                    <Grid spacing={2} columns={12}>
                        <Grid size={{ xs: 12, lg: 9 }}>
                            <Box
                                className="SearchAndFilters-tabletUp"
                                sx={{
                                    borderRadius: 'sm',
                                    py: 2,
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    alignItems: 'center',
                                    gap: 1.5,
                                    '& > *': {
                                        minWidth: { xs: '120px', md: '320px' },
                                    },
                                }}
                            >
                                <FormControl size="small" sx={{ flexGrow: 1 }}>
                                    <Autocomplete
                                        options={category_list}
                                        getOptionLabel={(option: CategoryType) => option.name}
                                        getOptionKey={(option: CategoryType) => option.id}
                                        renderOption={(props, option) => (
                                            <Box component="li" {...props} sx={{ display: 'flex', justifyContent: 'space-between', width: '100%' }}>
                                                <Typography>{option.name}</Typography>
                                                <Typography variant="caption" color="text.secondary" sx={{ ml: 1, textTransform: 'capitalize' }}>
                                                    ({option.type})
                                                </Typography>
                                            </Box>
                                        )}
                                        value={selectedCategoryObj}
                                        onChange={(event, newValue: CategoryType | null) => {
                                            handleCategoryChange(newValue?.id || null);
                                            setSelectedCategoryObj(newValue);
                                        }}
                                        renderInput={(params) => <TextField {...params} label={false} variant="outlined" placeholder="Select Category" />}
                                        fullWidth
                                    />
                                </FormControl>

                                <Button
                                    variant="outlined"
                                    component={Link}
                                    href={route('transactions.index')}
                                    sx={{ borderRadius: 2, textTransform: 'none', minWidth: 'fit-content' }}
                                >
                                    View by Account
                                </Button>
                            </Box>

                            {category && (
                                <Box sx={{ mb: 2 }}>
                                    <Card variant="outlined">
                                        <CardContent sx={{ p: 2, '&:last-child': { pb: 2 } }}>
                                            <Box
                                                sx={{
                                                    display: 'flex',
                                                    justifyContent: 'space-between',
                                                    alignItems: 'center',
                                                }}
                                            >
                                                <Stack direction="row" spacing={2} alignItems="center">
                                                    <Box
                                                        sx={{
                                                            width: 12,
                                                            height: 12,
                                                            borderRadius: '50%',
                                                            bgcolor: category.color || 'grey.400',
                                                        }}
                                                    />
                                                    <Typography variant="h6">{category.name}</Typography>
                                                    <Chip
                                                        size="small"
                                                        label={category.type.charAt(0).toUpperCase() + category.type.slice(1)}
                                                        icon={category.type === 'income' ? <ArrowUpwardIcon /> : <ArrowDownwardIcon />}
                                                        color={category.type === 'income' ? 'success' : 'error'}
                                                        variant="outlined"
                                                        sx={{ ml: 1, textTransform: 'capitalize' }}
                                                    />
                                                </Stack>
                                                <Typography variant="h6" fontWeight="bold">
                                                    Total: {formatNumber(totalAmount, pagination_data?.data[0]?.currency_code || 'BRL')}
                                                </Typography>
                                            </Box>
                                        </CardContent>
                                    </Card>
                                </Box>
                            )}

                            {pagination_data ? (
                                <>
                                    <Box
                                        sx={{
                                            display: { xs: 'flex', sm: 'none' },
                                            flexDirection: 'column',
                                            gap: 2,
                                            mt: 2,
                                        }}
                                    >
                                        {pagination_data.data.map((row) => (
                                            <Card key={`${row.record_type}-${row.id}`} variant="outlined">
                                                <CardContent sx={{ pb: 1 }}>
                                                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1.5 }}>
                                                        <Typography variant="subtitle2" color="text.secondary">
                                                            {formatDate(row.paid_at)}
                                                        </Typography>
                                                        <Typography variant="body2" color="text.secondary" fontWeight="bold">
                                                            {row.currency_code} {row.credit || row.debit}
                                                        </Typography>
                                                    </Box>
                                                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                                                        <Typography variant="body2" fontWeight="medium" color="text.primary">
                                                            {row.description || '-'}
                                                        </Typography>
                                                    </Box>
                                                    <Grid container spacing={1}>
                                                        <Grid item xs={6}>
                                                            <Typography variant="caption" color="text.secondary" display="block">
                                                                Type
                                                            </Typography>
                                                            <Typography variant="body2">{row.record_type}</Typography>
                                                        </Grid>
                                                        <Grid item xs={6}>
                                                            <Typography variant="caption" color="text.secondary" display="block">
                                                                Amount
                                                            </Typography>
                                                            <Typography
                                                                variant="body2"
                                                                color={row.credit ? 'success.main' : 'error.main'}
                                                                fontWeight="bold"
                                                            >
                                                                {formatNumber(row.credit || row.debit, row.currency_code)}
                                                            </Typography>
                                                        </Grid>
                                                    </Grid>
                                                </CardContent>
                                            </Card>
                                        ))}
                                    </Box>

                                    <TableContainer component={Paper} sx={{ display: { xs: 'none', sm: 'block' } }}>
                                        <Table size="small">
                                            <TableHead>
                                                <TableRow>
                                                    <TableCell>Date</TableCell>
                                                    <TableCell>Type</TableCell>
                                                    <TableCell>Description</TableCell>
                                                    <TableCell align="right">Credit</TableCell>
                                                    <TableCell align="right">Debit</TableCell>
                                                </TableRow>
                                            </TableHead>
                                            <TableBody>
                                                {pagination_data.data.map((row) => (
                                                    <TableRow key={`${row.record_type}-${row.id}`}>
                                                        <TableCell>{formatDate(row.paid_at)}</TableCell>
                                                        <TableCell>{row.record_type}</TableCell>
                                                        <TableCell>{row.description || '-'}</TableCell>
                                                        <TableCell align="right" sx={{ color: 'success.main' }}>
                                                            {formatNumber(row.credit, row.currency_code)}
                                                        </TableCell>
                                                        <TableCell align="right" sx={{ color: 'error.main' }}>
                                                            {formatNumber(row.debit, row.currency_code)}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                                {pagination_data.data.length === 0 && (
                                                    <TableRow>
                                                        <TableCell colSpan={5} align="center" sx={{ py: 3 }}>
                                                            No transactions found for this category.
                                                        </TableCell>
                                                    </TableRow>
                                                )}
                                            </TableBody>
                                            <TableFooter>
                                                <TableRow>
                                                    <TableCell colSpan={5}>
                                                        <Box display="flex" justifyContent="center" alignItems="center" p={2}>
                                                            <IconButton
                                                                onClick={() => router.visit(pagination_data.first_page_url)}
                                                                disabled={pagination_data.current_page === 1}
                                                            >
                                                                <FirstPageIcon />
                                                            </IconButton>
                                                            <IconButton
                                                                onClick={() => pagination_data.prev_page_url && router.visit(pagination_data.prev_page_url)}
                                                                disabled={!pagination_data.prev_page_url}
                                                            >
                                                                <KeyboardArrowLeft />
                                                            </IconButton>
                                                            <Typography sx={{ mx: 2 }}>
                                                                Page {pagination_data.current_page} of {pagination_data.last_page}
                                                            </Typography>
                                                            <IconButton
                                                                onClick={() => pagination_data.next_page_url && router.visit(pagination_data.next_page_url)}
                                                                disabled={!pagination_data.next_page_url}
                                                            >
                                                                <KeyboardArrowRight />
                                                            </IconButton>
                                                            <IconButton
                                                                onClick={() => router.visit(pagination_data.last_page_url)}
                                                                disabled={pagination_data.current_page === pagination_data.last_page}
                                                            >
                                                                <LastPageIcon />
                                                            </IconButton>
                                                        </Box>
                                                    </TableCell>
                                                </TableRow>
                                            </TableFooter>
                                        </Table>
                                    </TableContainer>
                                </>
                            ) : (
                                <Box p={5} textAlign="center" component={Paper} variant="outlined">
                                    <Typography variant="h6" color="textSecondary">
                                        Select a category to view transactions.
                                    </Typography>
                                </Box>
                            )}
                        </Grid>
                    </Grid>
                </Stack>
            </PageContainer>
        </AppLayout>
    );
}
