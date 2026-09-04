import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormControlLabel from '@mui/material/FormControlLabel';
import FormGroup from '@mui/material/FormGroup';
import FormHelperText from '@mui/material/FormHelperText';
import Grid from '@mui/material/Grid';
import InputLabel from '@mui/material/InputLabel';
import NativeSelect from '@mui/material/NativeSelect';
import Stack from '@mui/material/Stack';
import Switch from '@mui/material/Switch';
import TextField from '@mui/material/TextField';
import * as React from 'react';
import { toast } from 'sonner';
import { Transition } from '@headlessui/react';

type CustomerModel = {
    id: number | null;
    name: string;
    email: string;
    phone: string | null;
    tax_number: string | null;
    currency_code: string;
    website: string | null;
    address: string | null;
    reference: string | null;
    enabled: boolean;
};

type CurrencyOption = {
    code: string;
    name: string;
};

export default function CustomerForm({
    customer,
    currencies = [],
}: {
    customer: CustomerModel;
    currencies?: CurrencyOption[];
}) {
    const { data, setData, patch, post, errors, processing, recentlySuccessful } = useForm<Required<CustomerModel>>({
        id: customer.id || null,
        name: customer.name || '',
        email: customer.email || '',
        phone: customer.phone || '',
        tax_number: customer.tax_number || '',
        currency_code: customer.currency_code || 'BRL',
        website: customer.website || '',
        address: customer.address || '',
        reference: customer.reference || '',
        enabled: customer.enabled == null ? true : !!customer.enabled,
    });

    const [isSubmitting, setIsSubmitting] = React.useState(false);

    const handleSubmit = React.useCallback(
        async (event: React.FormEvent<HTMLFormElement>) => {
            event.preventDefault();

            setIsSubmitting(true);
            try {
                if (data.id === null) {
                    post(route('customers.create'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Customer created successfully');
                        },
                    });
                } else {
                    patch(route('customers.update', data.id), {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Customer updated successfully');
                        },
                    });
                }
            } finally {
                setIsSubmitting(false);
            }
        },
        [setIsSubmitting, data, post, patch],
    );

    const handleTextFieldChange = React.useCallback(
        (event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
            setData(event.target.name as keyof CustomerModel, event.target.value);
        },
        [setData],
    );

    const handleCheckboxFieldChange = React.useCallback(
        (_event: React.SyntheticEvent<Element, Event>, checked: boolean) => {
            setData('enabled', checked);
        },
        [setData],
    );

    const handleSelectFieldChange = React.useCallback(
        (event: React.ChangeEvent<HTMLSelectElement>) => {
            setData(event.target.name as keyof CustomerModel, event.target.value);
        },
        [setData],
    );

    const handleBack = React.useCallback(() => {
        router.get(route('customers.index'));
    }, []);

    const pageTitle = customer.id ? 'Edit' : 'Add new';

    return (
        <AppLayout breadcrumbs={[{ title: 'Customers', path: route('customers.index') }, { title: pageTitle }]}>
            <Head title={pageTitle} />
            <PageContainer title={pageTitle}>
                <Box component="form" onSubmit={handleSubmit} noValidate autoComplete="off" sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} sx={{ mb: 2, width: '100%' }}>
                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.name ?? ''}
                                    onChange={handleTextFieldChange}
                                    autoFocus={true}
                                    name="name"
                                    label="Name"
                                    error={!!errors.name}
                                    helperText={errors.name ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                            shrink: true,
                                        },
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.email ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="email"
                                    label="Email"
                                    type="email"
                                    error={!!errors.email}
                                    helperText={errors.email ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                            shrink: true,
                                        },
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.phone ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="phone"
                                    label="Phone"
                                    error={!!errors.phone}
                                    helperText={errors.phone ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                            shrink: true,
                                        },
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.tax_number ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="tax_number"
                                    label="Tax Number"
                                    error={!!errors.tax_number}
                                    helperText={errors.tax_number ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                            shrink: true,
                                        },
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <FormControl error={!!errors.currency_code} variant="standard" fullWidth>
                                    <InputLabel id="currency_code-label" shrink={true}>
                                        Currency
                                    </InputLabel>
                                    <NativeSelect
                                        value={data.currency_code ?? ''}
                                        onChange={handleSelectFieldChange}
                                        name="currency_code"
                                        variant="standard"
                                        fullWidth
                                    >
                                        {currencies.length > 0 ? (
                                            currencies.map((curr) => (
                                                <option key={curr.code} value={curr.code}>
                                                    {curr.name ? `${curr.name} (${curr.code})` : curr.code}
                                                </option>
                                            ))
                                        ) : (
                                            <>
                                                <option value="BRL">BRL</option>
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                            </>
                                        )}
                                    </NativeSelect>
                                    <FormHelperText>{errors.currency_code ?? ' '}</FormHelperText>
                                </FormControl>
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.website ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="website"
                                    label="Website"
                                    error={!!errors.website}
                                    helperText={errors.website ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                            shrink: true,
                                        },
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.reference ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="reference"
                                    label="Reference"
                                    error={!!errors.reference}
                                    helperText={errors.reference ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                            shrink: true,
                                        },
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 12 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.address ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="address"
                                    label="Address"
                                    multiline
                                    rows={2}
                                    error={!!errors.address}
                                    helperText={errors.address ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                            shrink: true,
                                        },
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 12 }} sx={{ display: 'flex' }}>
                                <FormGroup>
                                    <FormControlLabel
                                        control={<Switch checked={!!data.enabled} onChange={handleCheckboxFieldChange} />}
                                        name="enabled"
                                        label={data.enabled ? 'Enabled' : 'Disabled'}
                                    />
                                </FormGroup>
                            </Grid>
                        </Grid>
                    </FormGroup>

                    <Stack direction="row" spacing={2} justifyContent="space-between">
                        <Button variant="contained" size="large" startIcon={<ArrowBackIcon />} onClick={handleBack}>
                            Back
                        </Button>
                        <Button disabled={processing} type="submit" variant="contained" size="large" loading={isSubmitting || processing}>
                            Save
                        </Button>

                        <Transition
                            show={recentlySuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-sm text-neutral-600">Saved</p>
                        </Transition>
                    </Stack>
                </Box>
            </PageContainer>
        </AppLayout>
    );
}
