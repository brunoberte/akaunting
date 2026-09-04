import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import FirstPageIcon from '@mui/icons-material/FirstPage';
import KeyboardArrowLeft from '@mui/icons-material/KeyboardArrowLeft';
import KeyboardArrowRight from '@mui/icons-material/KeyboardArrowRight';
import LastPageIcon from '@mui/icons-material/LastPage';
import RefreshIcon from '@mui/icons-material/Refresh';
import SearchIcon from '@mui/icons-material/Search';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Chip from '@mui/material/Chip';
import FormControl from '@mui/material/FormControl';
import Grid from '@mui/material/Grid';
import IconButton from '@mui/material/IconButton';
import Input from '@mui/material/Input';
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
import * as React from 'react';
import { toast } from 'sonner';
import { z } from 'zod';

export const schema = z.object({
    id: z.number(),
    name: z.string(),
    email: z.string(),
    phone: z.string().nullable().optional(),
    tax_number: z.string().nullable().optional(),
    currency_code: z.string(),
    address: z.string().nullable().optional(),
    website: z.string().nullable().optional(),
    reference: z.string().nullable().optional(),
    enabled: z.boolean(),
});

export type CustomerItem = z.infer<typeof schema>;

export const pagination_schema = z.object({
    data: z.array(schema),
    first_page_url: z.string(),
    next_page_url: z.string().nullable(),
    last_page_url: z.string(),
    prev_page_url: z.string().nullable(),
    path: z.string(),
    current_page: z.number(),
    from: z.number().nullable(),
    to: z.number().nullable(),
    total: z.number(),
    per_page: z.number(),
    last_page: z.number(),
});

type CustomerPagination = z.infer<typeof pagination_schema>;

const handleDeleteRecord = (row: CustomerItem) => {
    if (window.confirm(`Are you sure you want to delete ${row.name}?`)) {
        router.delete(route('customers.delete', [row.id]), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success(`Customer ${row.name} deleted`);
            },
            onError: (errors) => {
                if (errors.customer) {
                    toast.error(errors.customer);
                } else {
                    toast.error('Failed to delete record');
                }
            },
        });
    }
};

export default function Index({
    customers,
    filter_text = '',
    filter_enabled = '',
}: {
    customers: CustomerPagination;
    filter_text: string;
    filter_enabled: string;
}) {
    const [filterText, setFilterText] = React.useState(filter_text || '');
    const [filterEnabled, setFilterEnabled] = React.useState(filter_enabled || '');

    const handleRefresh = React.useCallback(() => {
        router.reload();
    }, []);

    React.useMemo(() => {
        router.visit(
            route('customers.index', {
                page: customers.current_page,
                filter_text: filterText,
                filter_enabled: filterEnabled,
            }),
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }, [filterText, filterEnabled, customers.current_page]);

    const handleCreateClick = React.useCallback(() => {
        router.get(route('customers.new'));
    }, []);

    const handleFirstPageButtonClick = () => {
        if (customers.first_page_url) {
            router.visit(customers.first_page_url);
        }
    };

    const handleBackButtonClick = () => {
        if (customers.prev_page_url) {
            router.visit(customers.prev_page_url);
        }
    };

    const handleNextButtonClick = () => {
        if (customers.next_page_url) {
            router.visit(customers.next_page_url);
        }
    };

    const handleLastPageButtonClick = () => {
        if (customers.last_page_url) {
            router.visit(customers.last_page_url);
        }
    };

    const pageTitle = 'Customers';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('customers.index') }]}>
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
                        <Button variant="contained" onClick={handleCreateClick} startIcon={<AddIcon />} size="small">
                            Create
                        </Button>
                    </Stack>
                }
            >
                <Grid spacing={2} columns={12}>
                    <Grid size={{ xs: 12, lg: 12 }}>
                        <Box
                            className="SearchAndFilters-tabletUp"
                            sx={{
                                borderRadius: 'sm',
                                py: 2,
                                display: { xs: 'none', sm: 'flex' },
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
                                    placeholder="Search name, email, phone..."
                                    value={filterText}
                                    onChange={(e) => setFilterText(e.target.value)}
                                />
                            </FormControl>
                            <FormControl size="small">
                                <NativeSelect name="filter_enabled" value={filterEnabled} onChange={(e) => setFilterEnabled(e.target.value)}>
                                    <option value="">All statuses</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </NativeSelect>
                            </FormControl>
                        </Box>

                        <TableContainer component={Paper}>
                            <Table size="small">
                                <TableHead>
                                    <TableRow>
                                        <TableCell>Name</TableCell>
                                        <TableCell>Email</TableCell>
                                        <TableCell>Phone</TableCell>
                                        <TableCell>Currency</TableCell>
                                        <TableCell align="center">Status</TableCell>
                                        <TableCell sx={{ minWidth: '120px' }}></TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {customers.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.name}</TableCell>
                                            <TableCell>{row.email}</TableCell>
                                            <TableCell>{row.phone || '-'}</TableCell>
                                            <TableCell>{row.currency_code}</TableCell>
                                            <TableCell align="center">
                                                <Chip
                                                    label={row.enabled ? 'Active' : 'Inactive'}
                                                    color={row.enabled ? 'success' : 'default'}
                                                    size="small"
                                                />
                                            </TableCell>
                                            <TableCell align="right">
                                                <IconButton size="small" href={route('customers.edit', { customer: row.id })}>
                                                    <EditIcon />
                                                </IconButton>
                                                <IconButton size="small" onClick={() => handleDeleteRecord(row)}>
                                                    <DeleteIcon />
                                                </IconButton>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {customers.data.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={6} align="center">
                                                No customers found.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                                <TableFooter>
                                    <TableRow>
                                        <TableCell colSpan={6}>
                                            <Box sx={{ flexShrink: 0, ml: 2.5 }}>
                                                <IconButton
                                                    onClick={handleFirstPageButtonClick}
                                                    disabled={customers.current_page === 1}
                                                    aria-label="first page"
                                                >
                                                    <FirstPageIcon />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleBackButtonClick}
                                                    disabled={customers.current_page === 1}
                                                    aria-label="previous page"
                                                >
                                                    <KeyboardArrowLeft />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleNextButtonClick}
                                                    disabled={customers.current_page >= customers.last_page}
                                                    aria-label="next page"
                                                >
                                                    <KeyboardArrowRight />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleLastPageButtonClick}
                                                    disabled={customers.current_page >= customers.last_page}
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
