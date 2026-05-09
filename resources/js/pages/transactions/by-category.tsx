import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
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
import Divider from '@mui/material/Divider';
import CardActions from '@mui/material/CardActions';
import dayjs from 'dayjs';
import { toast } from 'sonner';
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
    transfer_id?: number;
};

type CategoryType = { id: number; name: string; type: string; color: string };
type AccountType = { id: number; name: string; currency_code: string };

export default function ByCategory({
    category_id,
    category,
    pagination_data,
    category_list,
    account_list,
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
    account_list: Array<AccountType>;
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
            currencyDisplay: 'code',
            currencySign: 'accounting',
        }).format(amount);
    };

    const handleDeleteRecord = (item: TransactionType) => {
        if (window.confirm('Are you sure you want to delete this record?')) {
            try {
                let route_name = '';
                switch (item.record_type) {
                    case 'Payment':
                    case 'TransferPayment':
                        route_name = 'transactions.payments.delete';
                        break;
                    case 'Revenue':
                    case 'TransferRevenue':
                        route_name = 'transactions.revenues.delete';
                        break;
                    default:
                        toast.error('Not implemented');
                        return;
                }
                router.delete(route(route_name, [item.id]), {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        toast.success(`Record deleted`);
                    },
                    onError: (errors) => {
                        console.log(errors);
                    },
                });
            } catch {
                toast.error('Failed to delete record');
            }
        }
    };

    const handleEditRecord = (row: TransactionType) => {
        try {
            switch (row.record_type) {
                case 'Payment':
                    router.visit(route('transactions.payments.edit', [row.id]));
                    break;
                case 'Revenue':
                    router.visit(route('transactions.revenues.edit', [row.id]));
                    break;
                case 'TransferPayment':
                case 'TransferRevenue':
                    router.visit(route('transactions.transfers.edit', [row.transfer_id]));
                    break;
                default:
                    toast.error('Not implemented');
                    return;
            }
        } catch (error: unknown) {
            if (error instanceof Error) {
                toast.error(error.message);
            } else {
                toast.error(String(error));
            }
        }
    };

    const getAccountName = (id: number) => {
        const item = account_list.find((x) => x.id == id);
        return item?.name || '';
    };

    const formatType = (item: TransactionType) => {
        if (item.record_type === 'TransferPayment') {
            return 'Transfer to ' + getAccountName(item.transfer_account_id as number);
        }
        if (item.record_type === 'TransferRevenue') {
            return 'Transfer from ' + getAccountName(item.transfer_account_id as number);
        }
        return item.record_type;
    };

    const totalsByCurrency = pagination_data?.data.reduce((acc, item) => {
        const currency = item.currency_code || 'USD';
        const credit = item.credit !== null ? Number(item.credit) : 0;
        const debit = item.debit !== null ? Number(item.debit) : 0;
        const amount = credit > 0 ? credit : -debit;

        if (!acc[currency]) {
            acc[currency] = 0;
        }
        acc[currency] += amount;
        return acc;
    }, {} as Record<string, number>) || {};

    const pageTitle = 'Transactions By Category';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('transactions.index') }]}>
            <Head title={pageTitle} />
            <PageContainer
                title={pageTitle}
                actions={
                    <Stack direction={{ xs: 'column', sm: 'row' }} alignItems={{ xs: 'stretch', sm: 'center' }} spacing={1}>
                        <Button component={Link} variant="contained" href={route('transactions.payments.new')} startIcon={<AddIcon />} size={'small'}>
                            Payment
                        </Button>
                        <Button component={Link} variant="contained" href={route('transactions.revenues.new')} startIcon={<AddIcon />} size={'small'}>
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
                                        renderInput={(params) => (
                                            <TextField {...params} label={undefined} variant="outlined" placeholder="Select Category" />
                                        )}
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
                                                    flexWrap: 'wrap',
                                                    gap: 2,
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
                                                <Stack direction="column" alignItems="flex-end" spacing={0.5}>
                                                    {Object.entries(totalsByCurrency).length > 0 ? (
                                                        Object.entries(totalsByCurrency).map(([currency, amount]) => (
                                                            <Typography key={currency} variant="h6" fontWeight="bold">
                                                                Total ({currency}): {formatNumber(Math.abs(amount), currency)}
                                                            </Typography>
                                                        ))
                                                    ) : (
                                                        <Typography variant="h6" fontWeight="bold">
                                                            Total: {formatNumber(0, 'USD')}
                                                        </Typography>
                                                    )}
                                                </Stack>
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
                                                            {row.currency_code} {row.credit !== null ? row.credit : row.debit}
                                                        </Typography>
                                                    </Box>
                                                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                                                        <Typography variant="body2" fontWeight="medium" color="text.primary">
                                                            {row.description || '-'}
                                                        </Typography>
                                                    </Box>
                                                    <Grid container spacing={1}>
                                                        <Grid size={6}>
                                                            <Typography variant="caption" color="text.secondary" display="block">
                                                                Type
                                                            </Typography>
                                                            <Typography variant="body2">{formatType(row)}</Typography>
                                                        </Grid>
                                                        <Grid size={6}>
                                                            <Typography variant="caption" color="text.secondary" display="block">
                                                                Account
                                                            </Typography>
                                                            <Typography variant="body2">{getAccountName(row.account_id)}</Typography>
                                                        </Grid>
                                                        <Grid size={12}>
                                                            <Typography variant="caption" color="text.secondary" display="block">
                                                                Amount
                                                            </Typography>
                                                            <Typography
                                                                variant="body2"
                                                                color={row.credit !== null ? 'success.main' : 'error.main'}
                                                                fontWeight="bold"
                                                            >
                                                                {formatNumber(row.credit !== null ? row.credit : row.debit, row.currency_code)}
                                                            </Typography>
                                                        </Grid>
                                                    </Grid>
                                                </CardContent>
                                                <Divider />
                                                <CardActions sx={{ justifyContent: 'flex-end', gap: 1, p: 1.5 }}>
                                                    <Button
                                                        size="small"
                                                        variant="outlined"
                                                        startIcon={<EditIcon />}
                                                        onClick={() => handleEditRecord(row)}
                                                        sx={{ borderRadius: 2, textTransform: 'none' }}
                                                    >
                                                        Edit
                                                    </Button>
                                                    <Button
                                                        size="small"
                                                        variant="outlined"
                                                        color="error"
                                                        startIcon={<DeleteIcon />}
                                                        onClick={() => handleDeleteRecord(row)}
                                                        sx={{ borderRadius: 2, textTransform: 'none' }}
                                                    >
                                                        Delete
                                                    </Button>
                                                </CardActions>
                                            </Card>
                                        ))}
                                    </Box>

                                    <TableContainer component={Paper} sx={{ display: { xs: 'none', sm: 'block' } }}>
                                        <Table size="small">
                                            <TableHead>
                                                <TableRow>
                                                    <TableCell>Date</TableCell>
                                                    <TableCell>Type</TableCell>
                                                    <TableCell>Account</TableCell>
                                                    <TableCell>Description</TableCell>
                                                    <TableCell align="right">Amount</TableCell>
                                                    <TableCell sx={{ minWidth: '120px' }}></TableCell>
                                                </TableRow>
                                            </TableHead>
                                            <TableBody>
                                                {pagination_data.data.map((row) => (
                                                    <TableRow key={`${row.record_type}-${row.id}`}>
                                                        <TableCell>{formatDate(row.paid_at)}</TableCell>
                                                        <TableCell>{formatType(row)}</TableCell>
                                                        <TableCell>{getAccountName(row.account_id)}</TableCell>
                                                        <TableCell>{row.description || '-'}</TableCell>
                                                        <TableCell align="right" sx={{ color: row.credit !== null ? 'success.main' : 'error.main' }}>
                                                            {formatNumber(row.credit !== null ? row.credit : row.debit, row.currency_code)}
                                                        </TableCell>
                                                        <TableCell align="right">
                                                            <IconButton size="small" onClick={() => handleEditRecord(row)}>
                                                                <EditIcon aria-hidden="true" />
                                                            </IconButton>
                                                            <IconButton size="small" onClick={() => handleDeleteRecord(row)}>
                                                                <DeleteIcon aria-hidden="true" />
                                                            </IconButton>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                                {pagination_data.data.length === 0 && (
                                                    <TableRow>
                                                        <TableCell colSpan={6} align="center" sx={{ py: 3 }}>
                                                            No transactions found for this category.
                                                        </TableCell>
                                                    </TableRow>
                                                )}
                                            </TableBody>
                                            <TableFooter>
                                                <TableRow>
                                                    <TableCell colSpan={6}>
                                                        <Box display="flex" justifyContent="center" alignItems="center" p={2}>
                                                            <IconButton
                                                                onClick={() => router.visit(pagination_data.first_page_url)}
                                                                disabled={pagination_data.current_page === 1}
                                                            >
                                                                <FirstPageIcon />
                                                            </IconButton>
                                                            <IconButton
                                                                onClick={() =>
                                                                    pagination_data.prev_page_url && router.visit(pagination_data.prev_page_url)
                                                                }
                                                                disabled={!pagination_data.prev_page_url}
                                                            >
                                                                <KeyboardArrowLeft />
                                                            </IconButton>
                                                            <Typography sx={{ mx: 2 }}>
                                                                Page {pagination_data.current_page} of {pagination_data.last_page}
                                                            </Typography>
                                                            <IconButton
                                                                onClick={() =>
                                                                    pagination_data.next_page_url && router.visit(pagination_data.next_page_url)
                                                                }
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
