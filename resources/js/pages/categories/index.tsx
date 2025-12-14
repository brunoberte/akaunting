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

const handleDeleteRecord = (row) => {
    if (window.confirm(`Are you sure you want to delete ${row.name}?`)) {
        try {
            router.delete(route('categories.delete', [row.id]), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    console.log(page);
                },
            });
            toast.success(`Category ${row.name} deleted`);
        } catch {
            toast.error('Failed to delete record');
        }
    }
};

export const schema = z.object({
    id: z.number(),
    name: z.string(),
    type: z.string(),
    color: z.string(),
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

export default function Index({
    categories: categories,
    filter_text: filter_text = '',
    filter_type: filter_type = '',
    filter_enabled: filter_enabled = '',
}: {
    categories: typeof pagination_schema;
    filter_text: string;
    filter_type: string;
    filter_enabled: string;
}) {
    const [filterText, setFilterText] = React.useState(filter_text || '');
    const [filterType, setFilterType] = React.useState(filter_type || '');
    const [filterEnabled, setFilterEnabled] = React.useState(filter_enabled || '');

    const handleRefresh = React.useCallback(() => {
        router.reload();
    }, []);

    React.useMemo(() => {
        // setIsLoading(true);
        router.visit(
            route('categories.index', {
                page: categories.current_page,
                filter_text: filterText,
                filter_type: filterType,
                filter_enabled: filterEnabled,
            }),
            {
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
            },
        );
    }, [filterText, filterType, filterEnabled, categories.current_page]);

    const handleCreateClick = React.useCallback(() => {
        router.get(route('categories.new'));
    }, []);

    const handleFirstPageButtonClick = () => {
        router.visit(categories.first_page_url);
    };

    const handleBackButtonClick = () => {
        router.visit(categories.prev_page_url);
    };

    const handleNextButtonClick = () => {
        router.visit(categories.next_page_url);
    };

    const handleLastPageButtonClick = () => {
        router.visit(categories.last_page_url);
    };

    const pageTitle = 'Categories';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('categories.index') }]}>
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
                                <NativeSelect name="filter_type" value={filterType} onChange={(e) => setFilterType(e.target.value)}>
                                    <option value={''}>All types</option>
                                    <option value={'income'}>Income</option>
                                    <option value={'expense'}>Expense</option>
                                </NativeSelect>
                            </FormControl>
                            <FormControl size="small">
                                <NativeSelect name="filter_enabled" value={filterEnabled} onChange={(e) => setFilterEnabled(e.target.value)}>
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
                                        <TableCell>Name</TableCell>
                                        <TableCell>Type</TableCell>
                                        <TableCell>Status</TableCell>
                                        <TableCell>Color</TableCell>
                                        <TableCell sx={{ minWidth: '120px' }}></TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {categories.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.name}</TableCell>
                                            <TableCell align="center">{row.type}</TableCell>
                                            <TableCell align="center">
                                                <Chip
                                                    label={row.enabled ? 'Active' : 'Inactive'}
                                                    color={row.enabled ? 'success' : 'default'}
                                                    size="small"
                                                />
                                            </TableCell>
                                            <TableCell align="center">
                                                <Chip label={row.color} sx={{ backgroundColor: row.color }} size="small" />
                                            </TableCell>
                                            <TableCell align="right">
                                                <IconButton size="small" href={route('categories.edit', { category: row.id })}>
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
                                        <TableCell colSpan={5}>
                                            <Box sx={{ flexShrink: 0, ml: 2.5 }}>
                                                <IconButton
                                                    onClick={handleFirstPageButtonClick}
                                                    disabled={categories.current_page === 1}
                                                    aria-label="first page"
                                                >
                                                    <FirstPageIcon />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleBackButtonClick}
                                                    disabled={categories.current_page === 1}
                                                    aria-label="previous page"
                                                >
                                                    <KeyboardArrowLeft />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleNextButtonClick}
                                                    disabled={categories.current_page >= categories.last_page}
                                                    aria-label="next page"
                                                >
                                                    <KeyboardArrowRight />
                                                </IconButton>
                                                <IconButton
                                                    onClick={handleLastPageButtonClick}
                                                    disabled={categories.current_page >= categories.last_page}
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
