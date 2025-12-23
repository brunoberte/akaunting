import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import FirstPageIcon from '@mui/icons-material/FirstPage';
import KeyboardArrowLeft from '@mui/icons-material/KeyboardArrowLeft';
import KeyboardArrowRight from '@mui/icons-material/KeyboardArrowRight';
import LastPageIcon from '@mui/icons-material/LastPage';
import RefreshIcon from '@mui/icons-material/Refresh';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import Grid from '@mui/material/Grid';
import IconButton from '@mui/material/IconButton';
import NativeSelect from '@mui/material/NativeSelect';
import Paper from '@mui/material/Paper';
import Stack from '@mui/material/Stack';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableFooter from '@mui/material/TableFooter';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Tooltip from '@mui/material/Tooltip';
import dayjs from 'dayjs';
import * as React from 'react';
import { toast } from 'sonner';
import { z } from 'zod';
import Chip from '@mui/material/Chip';
import { CardActions } from '@mui/material';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import Avatar from '@mui/material/Avatar';

export const schema = z.object({
    id: z.number(),
    account_id: z.number(),
    category_id: z.number(),
    customer_id: z.number().optional(),
    vendor_id: z.number().optional(),
    balance: z.number(),
    credit: z.number().optional(),
    debit: z.number().optional(),
    currency_code: z.number().optional(),
    description: z.string(),
    is_transfer: z.boolean(),
    transfer_account_id: z.number(),
    paid_at: z.string(),
    record_type: z.string(),
});

export const pagination_schema = z.object({
    data: z.array(schema),
    first_page_url: z.string(),
    next_page_url: z.string(),
    last_page_url: z.string(),
    prev_page_url: z.string(),
    path: z.string(),
    current_page: z.number(),
    from: z.number(),
    to: z.number(),
    total: z.number(),
    per_page: z.number(),
    last_page: z.number(),
    // links: z.array(),
});

type IdNameType = { id: string | number; name: string; type: string };
type IdNameCurrencyCode = { id: string | number; name: string; currency_code: string };

export default function Index({
    account_id: account_id,
    pagination_data: pagination_data,
    account_list: account_list,
    category_list: category_list,
}: {
    account_id: string;
    pagination_data: z.infer<typeof pagination_schema>;
    account_list: Array<IdNameCurrencyCode>;
    category_list: Array<IdNameType>;
}) {
    const [selectedAccount, setSelectedAccount] = React.useState<number>(account_id);

    const handleRefresh = React.useCallback(() => {
        router.reload();
    }, []);

    const handleAccountChange = (new_account_id: number) => {
        setSelectedAccount(new_account_id);
        router.visit(route('transactions.index', { account_id: new_account_id, page: 1 }), {
            preserveState: true,
            preserveScroll: true,
            onError: (errors) => {
                console.log({ OnError: errors });
            },
        });
    };

    const handleDeleteRecord = (item) => {
        if (window.confirm('Are you sure you want to delete this record?')) {
            try {
                let route_name = '';
                switch (item.record_type) {
                    case 'Payment':
                        route_name = 'transactions.payments.delete';
                        break;
                    case 'Revenue':
                        route_name = 'transactions.revenues.delete';
                        break;
                    default:
                        toast.error('Not implemented');
                        return;
                }
                router.delete(route(route_name, [item.id]), {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: (page) => {
                        toast.success(`Record deleted`);
                        console.log(page);
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

    const handleEditRecord = (row) => {
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
        } catch (error) {
            toast.error(error);
        }
    };

    const handleFirstPageButtonClick = () => {
        router.visit(pagination_data.first_page_url);
    };

    const handleBackButtonClick = () => {
        router.visit(pagination_data.prev_page_url);
    };

    const handleNextButtonClick = () => {
        router.visit(pagination_data.next_page_url);
    };

    const handleLastPageButtonClick = () => {
        router.visit(pagination_data.last_page_url);
    };

    const getCategoryName = (id) => {
        const category = category_list.find((x) => x.id == id);
        return category?.name || '';
    };
    const getAccountName = (id) => {
        const item = account_list.find((x) => x.id == id);
        return item?.name || '';
    };
    const formatType = (item) => {
        if (item.record_type == 'TransferPayment') {
            return 'Transfer to ' + getAccountName(item.transfer_account_id);
        }
        if (item.record_type == 'TransferRevenue') {
            return 'Transfer from ' + getAccountName(item.transfer_account_id);
        }
        return item.record_type;
    };
    const formatMobileDescription = (item: z.infer<typeof schema>) => {
        if (item.record_type == 'TransferPayment') {
            return 'Transfer to ' + getAccountName(item.transfer_account_id);
        }
        if (item.record_type == 'TransferRevenue') {
            return 'Transfer from ' + getAccountName(item.transfer_account_id);
        }
        return getCategoryName(item.category_id);
    };
    const formatDate = (date: string) => {
        return date ? dayjs(date).format('DD/MM/YYYY') : 'N/A';
    };
    const formatNumber = (value, currency) => {
        return value
            ? new Intl.NumberFormat('en-US', {
                  style: 'currency',
                  currency: currency,
                  currencyDisplay: 'code',
                  currencySign: 'accounting',
              }).format(value)
            : '';
    };

    const pageTitle = 'Transactions';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('transactions.index') }]}>
            <Head title={pageTitle} />
            <PageContainer
                title={pageTitle}
                breadcrumbs={[{ title: pageTitle }]}
                actions={
                    <Stack direction={{ xs: 'column', sm: 'row' }} alignItems={{ xs: 'stretch', sm: 'center' }} spacing={1}>
                        <Tooltip title="Reload data" placement="right" enterDelay={1000}>
                            <Box sx={{ display: { xs: 'none', sm: 'block' } }}>
                                <IconButton size="small" aria-label="refresh" onClick={handleRefresh}>
                                    <RefreshIcon />
                                </IconButton>
                            </Box>
                        </Tooltip>
                        <Button
                            component={Link}
                            variant="contained"
                            href={route('transactions.payments.new', { account_id: selectedAccount })}
                            startIcon={<AddIcon />}
                            size={'small'}
                        >
                            Payment
                        </Button>
                        <Button
                            component={Link}
                            variant="contained"
                            href={route('transactions.revenues.new', { account_id: selectedAccount })}
                            startIcon={<AddIcon />}
                            size={'small'}
                        >
                            Revenue
                        </Button>
                        <Button
                            component={Link}
                            variant="contained"
                            href={route('transactions.transfers.new', { account_id: selectedAccount })}
                            startIcon={<AddIcon />}
                            size={'small'}
                        >
                            Transfer
                        </Button>
                    </Stack>
                }
            >
                <Grid spacing={2} columns={12}>
                    <Grid size={{ xs: 12, lg: 9 }}>
                        <Box
                            className="SearchAndFilters-tabletUp"
                            sx={{
                                borderRadius: 'sm',
                                py: 2,
                                display: 'flex',
                                flexWrap: 'wrap',
                                gap: 1.5,
                                '& > *': {
                                    minWidth: { xs: '120px', md: '160px' },
                                },
                            }}
                        >
                            <FormControl size="small">
                                <NativeSelect name="account_id" value={selectedAccount} onChange={(e) => handleAccountChange(e.target.value)}>
                                    {account_list.map((row) => (
                                        <option key={row.id} value={row.id}>
                                            {row.name}
                                        </option>
                                    ))}
                                </NativeSelect>
                            </FormControl>
                        </Box>

                        <Stack sx={{ display: { xs: '', md: 'none' } }}>
                            {pagination_data.data.map((row) => (
                                <Card key={row.id} variant={'outlined'}>
                                    <CardContent>
                                        <Typography variant="h6" component="div">
                                            {formatMobileDescription(row)}
                                        </Typography>
                                        <Stack direction="row" spacing={1}>
                                            <Chip color="info" label={formatDate(row.paid_at)} variant="outlined" />
                                            {row.credit && (
                                                <Chip
                                                    color="info"
                                                    avatar={
                                                        <Avatar sx={{ bgcolor: '#FFF' }}>
                                                            <AddIcon />
                                                        </Avatar>
                                                    }
                                                    label={row.credit}
                                                    variant="outlined"
                                                />
                                            )}
                                            {row.debit && (
                                                <Chip
                                                    color="info"
                                                    avatar={
                                                        <Avatar sx={{ bgcolor: '#FFF' }}>
                                                            <RemoveIcon />
                                                        </Avatar>
                                                    }
                                                    label={row.debit}
                                                    variant="outlined"
                                                />
                                            )}
                                            {row.balance >= 0 && (
                                                <Chip color="success" label={formatNumber(row.balance, row.currency_code)} variant="outlined" />
                                            )}
                                            {row.balance < 0 && (
                                                <Chip color="error" label={formatNumber(row.balance, row.currency_code)} variant="outlined" />
                                            )}
                                        </Stack>
                                    </CardContent>
                                    <CardActions disableSpacing>
                                        <IconButton size="small" onClick={() => handleEditRecord(row)}>
                                            <EditIcon />
                                        </IconButton>
                                        <IconButton size="small" onClick={() => handleDeleteRecord(row)}>
                                            <DeleteIcon />
                                        </IconButton>
                                    </CardActions>
                                </Card>
                            ))}
                        </Stack>

                        <TableContainer component={Paper} sx={{ display: { xs: 'none', md: 'block' } }}>
                            <Table size="small">
                                <TableHead>
                                    <TableRow>
                                        <TableCell>Date</TableCell>
                                        <TableCell>Type</TableCell>
                                        <TableCell>Category</TableCell>
                                        <TableCell>Description</TableCell>
                                        <TableCell>Credit</TableCell>
                                        <TableCell>Debit</TableCell>
                                        <TableCell>Balance</TableCell>
                                        <TableCell sx={{ minWidth: '120px' }}></TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {pagination_data.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{formatDate(row.paid_at)}</TableCell>
                                            <TableCell>{formatType(row)}</TableCell>
                                            <TableCell>{getCategoryName(row.category_id)}</TableCell>
                                            <TableCell>{row.description}</TableCell>
                                            <TableCell align="right">{formatNumber(row.credit, row.currency_code)}</TableCell>
                                            <TableCell align="right">{formatNumber(row.debit, row.currency_code)}</TableCell>
                                            <TableCell align="right">{formatNumber(row.balance, row.currency_code)}</TableCell>
                                            <TableCell align="right">
                                                <IconButton size="small" onClick={() => handleEditRecord(row)}>
                                                    <EditIcon />
                                                </IconButton>
                                                <IconButton size="small" onClick={() => handleDeleteRecord(row)}>
                                                    <DeleteIcon />
                                                </IconButton>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                                <TableFooter>
                                    <TableRow>
                                        <TableCell colSpan={8}>
                                            <Box sx={{ flexShrink: 0, ml: 2.5 }}>
                                                <IconButton
                                                    onClick={handleFirstPageButtonClick}
                                                    disabled={pagination_data.current_page === 1}
                                                    aria-label="first page"
                                                >
                                                    <FirstPageIcon />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleBackButtonClick}
                                                    disabled={pagination_data.current_page === 1}
                                                    aria-label="previous page"
                                                >
                                                    <KeyboardArrowLeft />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleNextButtonClick}
                                                    disabled={pagination_data.current_page >= pagination_data.last_page}
                                                    aria-label="next page"
                                                >
                                                    <KeyboardArrowRight />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleLastPageButtonClick}
                                                    disabled={pagination_data.current_page >= pagination_data.last_page}
                                                    aria-label="last page"
                                                >
                                                    <LastPageIcon />
                                                </IconButton>
                                            </Box>
                                        </TableCell>
                                    </TableRow>
                                </TableFooter>
                            </Table>
                        </TableContainer>
                    </Grid>
                </Grid>
            </PageContainer>
        </AppLayout>
    );
}
