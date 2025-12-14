import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
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
import * as React from 'react';
import { toast } from 'sonner';
import Chip from '@mui/material/Chip';

const handleDeleteRecord = (row: CompanyModel) => {
    if (window.confirm(`Are you sure you want to delete ${row.title}?`)) {
        try {
            router.delete(route('companies.delete', [row.id]), {
                preserveScroll: true,
                preserveState: true,
            });
            toast.success(`Record ${row.title} deleted`);
        } catch {
            toast.error('Failed to delete record');
        }
    }
};

type CompanyModel = {
    id: number | null;
    title: string;
    domain: string;
    enabled: boolean;
};

export default function Index({
    record_list: record_list,
    filter_text: filter_text = '',
}: {
    record_list: CompanyModel[];
    filter_text: string;
}) {
    const [filterText, setFilterText] = React.useState(filter_text || '');

    const handleRefresh = React.useCallback(() => {
        router.reload();
    }, []);

    React.useMemo(() => {
        // setIsLoading(true);
        router.visit(route('companies.index', { filter_text: filterText }), {
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
        router.get(route('companies.new'));
    }, []);

    const pageTitle = 'Companies';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('companies.index') }]}>
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
                        </Box>

                        <TableContainer component={Paper}>
                            <Table size="small">
                                <TableHead>
                                    <TableRow>
                                        <TableCell>Name</TableCell>
                                        <TableCell>Domain</TableCell>
                                        <TableCell>Status</TableCell>
                                        <TableCell sx={{minWidth: '120px'}}></TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {record_list.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.title}</TableCell>
                                            <TableCell>{row.domain}</TableCell>
                                            <TableCell>
                                                <Chip
                                                    label={row.enabled ? 'Active' : 'Inactive'}
                                                    color={row.enabled ? 'success' : 'default'}
                                                    size="small"
                                                />
                                            </TableCell>
                                            <TableCell align="right">
                                                <IconButton component={Link} size="small" href={route('companies.edit', { company: row.id })}>
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
