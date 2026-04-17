import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import SkipNextIcon from '@mui/icons-material/SkipNext';
import RefreshIcon from '@mui/icons-material/Refresh';
import SearchIcon from '@mui/icons-material/Search';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import Grid from '@mui/material/Grid';
import IconButton from '@mui/material/IconButton';
import Input from '@mui/material/Input';
import Paper from '@mui/material/Paper';
import Stack from '@mui/material/Stack';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Tooltip from '@mui/material/Tooltip';
import Card from '@mui/material/Card';
import CardActions from '@mui/material/CardActions';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import Divider from '@mui/material/Divider';
import * as React from 'react';
import { toast } from 'sonner';
import dayjs from 'dayjs';

const handleDeleteRecord = (row: ReceivableModel) => {
    if (window.confirm(`Are you sure you want to delete ${row.title}?`)) {
        try {
            router.delete(route('receivables.delete', [row.id]), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    console.log(page);
                },
            });
            toast.success(`Record ${row.title} deleted`);
        } catch {
            toast.error('Failed to delete record');
        }
    }
};

type ReceivableModel = {
    id: number | null;
    account_id: number;
    due_at: string;
    currency_code: string;
    amount: number;
    title: string;
    customer_id: number;
    category_id: number;
    notes: string;
    recurring_frequency: string;
    recurring_count: number;
};
type IdNameSchema = {
    id: number;
    name: string;
};
type IdNameCurrencyCodeSchema = {
    id: number;
    name: string;
    currency_code: string;
};

export default function Index({
    record_list: record_list,
    account_list: account_list,
    category_list: category_list,
    customer_list: customer_list,
    filter_text: filter_text = '',
}: {
    record_list: ReceivableModel[];
    account_list: IdNameCurrencyCodeSchema[];
    category_list: IdNameSchema[];
    customer_list: IdNameSchema[];
    filter_text: string;
}) {
    const [filterText, setFilterText] = React.useState(filter_text || '');

    const handleRefresh = React.useCallback(() => {
        router.reload();
    }, []);

    React.useMemo(() => {
        // setIsLoading(true);
        router.visit(route('receivables.index', { filter_text: filterText }), {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                // setIsLoading(false);
            },
            onSuccess: (page) => {
                console.log({ onSuccess: page });
                // const parsedPage = pagination_schema.parse(page);
                // setPaginationModel({
                //     page: parsedPage.current_page - 1,
                //     pageSize: parsedPage.per_page,
                // });
            },
            onError: (errors) => {
                console.log({ OnError: errors });
            },
        });
    }, [filterText]);

    const handleCreateClick = React.useCallback(() => {
        router.get(route('receivables.new'));
    }, []);

    const handleSkipNext = (row: ReceivableModel) => {
        if (window.confirm(`Move ${row.title} to the next date?`)) {
            router.post(route('receivables.skip-next', [row.id]), {}, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => toast.success(`${row.title} date updated`),
                onError: () => toast.error('Failed to update date'),
            });
        }
    };

    const getAccountName = (id: number) => {
        const account = account_list.find((x) => x.id == id);
        return account?.name || '';
    };
    const getCustomerName = (id: number) => {
        const customer = customer_list.find((x) => x.id == id);
        return customer?.name || '';
    };
    const getCategoryName = (id: number) => {
        const category = category_list.find((x) => x.id == id);
        return category?.name || '';
    };
    const formatDate = (date: string) =>{
        return date ? dayjs(date).format('DD/MM/YYYY') : 'N/A';
    }

    const pageTitle = 'Receivables';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('receivables.index') }]}>
            <Head title={pageTitle} />
            <PageContainer
                title={pageTitle}
                breadcrumbs={[{ title: pageTitle }]}
                actions={
                    <Stack direction="row" alignItems="center" spacing={1}>
                        <Tooltip title="Reload data" placement="right" enterDelay={1000}>
                            <div>
                                <IconButton size="small" aria-label="refresh" onClick={handleRefresh}>
                                    <RefreshIcon />
                                </IconButton>
                            </div>
                        </Tooltip>
                        <Button variant="contained" onClick={handleCreateClick} startIcon={<AddIcon />} size={'small'}>
                            Create
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
                            <FormControl sx={{ flex: 1 }} size="small">
                                <Input
                                    name="filter_text"
                                    startAdornment={<SearchIcon />}
                                    placeholder="Search..."
                                    value={filterText}
                                    onChange={(e) => setFilterText(e.target.value)}
                                />
                            </FormControl>
                        </Box>

                        <Box
                            sx={{
                                display: { xs: 'flex', sm: 'none' },
                                flexDirection: 'column',
                                gap: 2,
                                mt: 2,
                            }}
                        >
                            {record_list.map((row) => (
                                <Card key={row.id} variant="outlined">
                                    <CardContent sx={{ pb: 1 }}>
                                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 1 }}>
                                            <Typography variant="subtitle2" color="text.secondary">
                                                {formatDate(row.due_at)}
                                            </Typography>
                                            <Typography variant="body1" fontWeight="bold">
                                                {row.currency_code} {row.amount}
                                            </Typography>
                                        </Box>
                                        <Typography variant="h6" component="div" gutterBottom sx={{ fontSize: '1.1rem' }}>
                                            {row.title}
                                        </Typography>
                                        <Grid container spacing={1}>
                                            <Grid item xs={6}>
                                                <Typography variant="caption" color="text.secondary" display="block">
                                                    Account
                                                </Typography>
                                                <Typography variant="body2">{getAccountName(row.account_id)}</Typography>
                                            </Grid>
                                            <Grid item xs={6}>
                                                <Typography variant="caption" color="text.secondary" display="block">
                                                    Customer
                                                </Typography>
                                                <Typography variant="body2">{getCustomerName(row.customer_id)}</Typography>
                                            </Grid>
                                            <Grid item xs={6}>
                                                <Typography variant="caption" color="text.secondary" display="block">
                                                    Category
                                                </Typography>
                                                <Typography variant="body2">{getCategoryName(row.category_id)}</Typography>
                                            </Grid>
                                            {row.recurring_frequency && (
                                                <Grid item xs={6}>
                                                    <Typography variant="caption" color="text.secondary" display="block">
                                                        Recurring
                                                    </Typography>
                                                    <Typography variant="body2">
                                                        {row.recurring_frequency}{row.recurring_count > 0 ? ` (${row.recurring_count})` : ''}
                                                    </Typography>
                                                </Grid>
                                            )}
                                        </Grid>
                                    </CardContent>
                                    <Divider />
                                    <CardActions sx={{ justifyContent: 'flex-end', py: 1 }}>
                                        {row.recurring_frequency && (
                                            <Tooltip title="Move to next date">
                                                <IconButton size="small" onClick={() => handleSkipNext(row)}>
                                                    <SkipNextIcon />
                                                </IconButton>
                                            </Tooltip>
                                        )}
                                        <IconButton component={Link} size="small" href={route('receivables.edit', { receivable: row.id })} color="primary">
                                            <EditIcon />
                                        </IconButton>
                                        <IconButton size="small" onClick={() => handleDeleteRecord(row)} color="error">
                                            <DeleteIcon />
                                        </IconButton>
                                    </CardActions>
                                </Card>
                            ))}
                        </Box>

                        <TableContainer component={Paper} sx={{ display: { xs: 'none', sm: 'block' } }}>
                            <Table size="small">
                                <TableHead>
                                    <TableRow>
                                        <TableCell>Due at</TableCell>
                                        <TableCell>Title</TableCell>
                                        <TableCell>Account</TableCell>
                                        <TableCell>Customer</TableCell>
                                        <TableCell>Category</TableCell>
                                        <TableCell>Amount</TableCell>
                                        <TableCell>Recurring</TableCell>
                                        <TableCell sx={{minWidth: '120px'}}></TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {record_list.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell sx={{whiteSpace: 'nowrap'}}>{formatDate(row.due_at)}</TableCell>
                                            <TableCell>{row.title}</TableCell>
                                            <TableCell>{getAccountName(row.account_id)}</TableCell>
                                            <TableCell>{getCustomerName(row.customer_id)}</TableCell>
                                            <TableCell>{getCategoryName(row.category_id)}</TableCell>
                                            <TableCell sx={{whiteSpace: 'nowrap'}}>{row.currency_code} {row.amount}</TableCell>
                                            <TableCell>{row.recurring_frequency}{row.recurring_count > 0 ? ' (' + row.recurring_count + ')' : ''}</TableCell>
                                            <TableCell align="right">
                                                {row.recurring_frequency && (
                                                    <Tooltip title="Mover para próxima data">
                                                        <IconButton size="small" onClick={() => handleSkipNext(row)}>
                                                            <SkipNextIcon />
                                                        </IconButton>
                                                    </Tooltip>
                                                )}
                                                <IconButton component={Link} size="small" href={route('receivables.edit', { receivable: row.id })}>
                                                    <EditIcon />
                                                </IconButton>
                                                <IconButton size="small" onClick={() => handleDeleteRecord(row)}>
                                                    <DeleteIcon />
                                                </IconButton>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </TableContainer>
                    </Grid>
                </Grid>
            </PageContainer>
        </AppLayout>
    );
}
