import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, router, useRemember } from '@inertiajs/react';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import RefreshIcon from '@mui/icons-material/Refresh';
import SearchIcon from '@mui/icons-material/Search';
import FirstPageIcon from '@mui/icons-material/FirstPage';
import KeyboardArrowLeft from '@mui/icons-material/KeyboardArrowLeft';
import KeyboardArrowRight from '@mui/icons-material/KeyboardArrowRight';
import LastPageIcon from '@mui/icons-material/LastPage';
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

const handleDeleteRecord = (row) => {
    if (window.confirm(`Are you sure you want to delete ${row.name}?`)) {
        try {
            router.delete(route('accounts.delete', [row.id]), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    console.log(page);
                },
            });
            toast.success(`Account ${row.name} deleted`);
        } catch (error) {
            toast.error('Failed to delete account');
        }
    }
};

function renderActionsCell(params) {
    if (params.id === false) {
        return '';
    }
    return (
        <>
            <IconButton size="small" href={route('accounts.edit', { account: params.id })}>
                <EditIcon />
            </IconButton>
            <IconButton size="small" onClick={() => handleDeleteRecord(params)}>
                <DeleteIcon />
            </IconButton>
        </>
    );
}

export const schema = z.object({
    id: z.number(),
    name: z.string(),
    number: z.string(),
    currency_code: z.string(),
    opening_balance: z.number().optional(),
    current_balance: z.number().optional(),
    bank_name: z.string(),
    bank_phone: z.string(),
    bank_address: z.string(),
    enabled: z.boolean(),
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

function renderStatus(enabled: boolean) {
    return <Chip label={enabled ? 'Active' : 'Inactive'} color={enabled ? 'success' : 'default'} size="small" />;
}

export default function Index({
    accounts: accounts,
    filter_text: filter_text = '',
    filter_enabled: filter_enabled = '',
}: {
    accounts: typeof pagination_schema;
    filter_text: string;
    filter_enabled: string;
}) {
    const [filterText, setFilterText] = React.useState(filter_text || '');
    const [filterEnabled, setFilterEnabled] = React.useState(filter_enabled || '');
    // const [formState, setFormState] = useRemember(
    //     {
    //         filter_text: filter_text || '',
    //         filter_enabled: filter_enabled || '',
    //         page: 1,
    //     },
    //     'Accounts/Index',
    // );
    // function setFormStateField(field: string, value: any) {
    //     setFormState((prevState) => ({
    //         ...prevState,
    //         [field]: value,
    //         page: 1,
    //     }));
    // }

    // const dialogs = useDialogs();
    // const notifications = useNotifications();

    const handleRefresh = React.useCallback(() => {
        router.reload();
    }, []);

    React.useMemo(() => {
        // setIsLoading(true);
        router.visit(route('accounts.index', { page: accounts.current_page, filter_text: filterText, filter_enabled: filterEnabled }), {
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
    }, [filterText, filterEnabled, accounts.current_page]);

    const handleCreateClick = React.useCallback(() => {
        router.get(route('accounts.new'));
    }, []);

    // const handleRowDelete = React.useCallback(
    //     (employee: z.infer<typeof schema>) => async () => {
    //     const confirmed = await dialogs.confirm(`Do you wish to delete ${employee.name}?`, {
    //         title: `Delete employee?`,
    //         severity: 'error',
    //         okText: 'Delete',
    //         cancelText: 'Cancel',
    //     });
    //
    //     if (confirmed) {
    //         setIsLoading(true);
    //         try {
    //             await deleteEmployee(Number(employee.id));
    //
    //             notifications.show('Employee deleted successfully.', {
    //                 severity: 'success',
    //                 autoHideDuration: 3000,
    //             });
    //             loadData();
    //         } catch (deleteError) {
    //             notifications.show(`Failed to delete employee. Reason:' ${(deleteError as Error).message}`, {
    //                 severity: 'error',
    //                 autoHideDuration: 3000,
    //             });
    //         }
    //         setIsLoading(false);
    //     }
    // },
    // [dialogs, notifications, loadData],
    // );

    const handleFirstPageButtonClick = (event: React.MouseEvent<HTMLButtonElement>) => {
        router.visit(accounts.first_page_url);
    };

    const handleBackButtonClick = (event: React.MouseEvent<HTMLButtonElement>) => {
        router.visit(accounts.prev_page_url);
    };

    const handleNextButtonClick = (event: React.MouseEvent<HTMLButtonElement>) => {
        router.visit(accounts.next_page_url);
    };

    const handleLastPageButtonClick = (event: React.MouseEvent<HTMLButtonElement>) => {
        router.visit(accounts.last_page_url);
    };

    const pageTitle = 'Accounts';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('accounts.index') }]}>
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
                                    placeholder="Search..."
                                    value={filterText}
                                    onChange={(e) => setFilterText(e.target.value)}
                                />
                            </FormControl>
                            <FormControl size="small">
                                <NativeSelect
                                    name="filter_enabled"
                                    value={filterEnabled}
                                    onChange={(e) => setFilterEnabled(e.target.value)}
                                >
                                    <option value={''}>All statuses</option>
                                    <option value={'1'}>Active</option>
                                    <option value={'0'}>Inactive</option>
                                </NativeSelect>
                            </FormControl>
                        </Box>

                        <TableContainer component={Paper}>
                            <Table size="small">
                                <TableHead>
                                    <TableRow>
                                        <TableCell>Account name</TableCell>
                                        <TableCell>Currency</TableCell>
                                        <TableCell>Status</TableCell>
                                        <TableCell sx={{minWidth: '120px'}}></TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {accounts.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.name}</TableCell>
                                            <TableCell align="center">{row.currency_code}</TableCell>
                                            <TableCell align="center">{renderStatus(row.enabled)}</TableCell>
                                            <TableCell align="right">{renderActionsCell(row)}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                                <TableFooter>
                                    <TableRow>
                                        <TableCell colSpan={5}>
                                            <Box sx={{ flexShrink: 0, ml: 2.5 }}>
                                                <IconButton
                                                    onClick={handleFirstPageButtonClick}
                                                    disabled={accounts.current_page === 1}
                                                    aria-label="first page"
                                                >
                                                    <FirstPageIcon />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleBackButtonClick}
                                                    disabled={accounts.current_page === 1}
                                                    aria-label="previous page"
                                                >
                                                    <KeyboardArrowLeft />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleNextButtonClick}
                                                    disabled={accounts.current_page >= accounts.last_page}
                                                    aria-label="next page"
                                                >
                                                    <KeyboardArrowRight />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleLastPageButtonClick}
                                                    disabled={accounts.current_page >= accounts.last_page}
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
