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
import { SelectChangeEvent, SelectProps } from '@mui/material/Select';
import Stack from '@mui/material/Stack';
import Switch from '@mui/material/Switch';
import TextField from '@mui/material/TextField';
import * as React from 'react';
import { toast } from 'sonner';
import { Transition } from '@headlessui/react';
import NativeSelect from '@mui/material/NativeSelect';

type AccountModel = {
    id: number | null;
    name: string;
    number: string;
    currency_code: string;
    opening_balance: number;
    bank_name: string;
    bank_phone: string;
    bank_address: string;
    enabled: boolean;
};

export interface AccountFormState {
    values: Partial<Omit<AccountModel, 'id'>>;
    errors: Partial<Record<keyof AccountFormState['values'], string>>;
}

export default function AccountForm({ account }: { account: AccountModel }) {
    const { data, setData, patch, post, errors, processing, recentlySuccessful } = useForm<Required<AccountModel>>({
        id: account.id || null,
        name: account.name,
        number: account.number,
        currency_code: account.currency_code,
        opening_balance: account.opening_balance || 0,
        bank_name: account.bank_name,
        bank_phone: account.bank_phone,
        bank_address: account.bank_address,
        enabled: account.enabled == null ? true : account.enabled,
    });

    const [isSubmitting, setIsSubmitting] = React.useState(false);

    const handleSubmit = React.useCallback(
        async (event: React.FormEvent<HTMLFormElement>) => {
            event.preventDefault();

            setIsSubmitting(true);
            try {
                if (data.id === null) {
                    post(route('accounts.create'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Account created successfully');
                        },
                    });
                } else {
                    patch(route('accounts.update', data.id), {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Account updated successfully');
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
        (event: React.ChangeEvent<HTMLInputElement>) => {
            setData(event.target.name, event.target.value);
        },
        [setData],
    );

    const handleCheckboxFieldChange = React.useCallback(
        (event: React.ChangeEvent<HTMLInputElement>) => {
            console.log(event);
            setData(event.target.name, event.target.checked);
        },
        [setData],
    );

    const handleSelectFieldChange = React.useCallback(
        (event: SelectChangeEvent) => {
            setData(event.target.name, event.target.value);
        },
        [setData],
    );

    const handleReset = React.useCallback(() => {
        // if (onReset) {
        //     onReset(formValues);
        // }
    }, []);

    const handleBack = React.useCallback(() => {
        router.get(route('accounts.index'));
    }, []);

    const pageTitle = account.id ? 'Edit' : 'Add new';

    return (
        <AppLayout breadcrumbs={[{ title: 'Accounts', path: route('accounts.index') }, { title: pageTitle }]}>
            <Head title={pageTitle} />
            <PageContainer title={pageTitle}>
                <Box component="form" onSubmit={handleSubmit} noValidate autoComplete="off" onReset={handleReset} sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} sx={{ mb: 2, width: '100%' }}>
                            <Grid size={{ xs: 12, sm: 12 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.name ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="name"
                                    label="Name"
                                    error={!!errors.name}
                                    helperText={errors.name ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                           shrink: true
                                        }
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 12 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.number ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="number"
                                    label="Number"
                                    error={!!errors.number}
                                    helperText={errors.number ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                           shrink: true
                                        }
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <FormControl error={!!errors.currency_code} variant="standard" fullWidth>
                                    <InputLabel id="currency_code-label" shrink={true}>Currency Code</InputLabel>
                                    <NativeSelect
                                        value={data.currency_code ?? ''}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        labelId="currency_code-label"
                                        name="currency_code"
                                        label="Currency Code"
                                        variant="standard"
                                        defaultValue=""
                                        fullWidth
                                    >
                                        <option value="BRL">BRL</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </NativeSelect>
                                    <FormHelperText>{errors.currency_code ?? ' '}</FormHelperText>
                                </FormControl>
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.opening_balance ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="opening_balance"
                                    label="Opening Balance"
                                    error={!!errors.opening_balance}
                                    helperText={errors.opening_balance ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                           shrink: true
                                        }
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.bank_name ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="bank_name"
                                    label="Bank Name"
                                    error={!!errors.bank_name}
                                    helperText={errors.bank_name ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                           shrink: true
                                        }
                                    }}
                                />
                            </Grid>
                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.bank_phone ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="bank_phone"
                                    label="Bank Phone"
                                    error={!!errors.bank_phone}
                                    helperText={errors.bank_phone ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                           shrink: true
                                        }
                                    }}
                                />
                            </Grid>
                            <Grid size={{ xs: 12, sm: 12 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.bank_address ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="bank_address"
                                    label="Bank Address"
                                    error={!!errors.bank_address}
                                    helperText={errors.bank_address ?? ' '}
                                    variant="standard"
                                    fullWidth
                                    slotProps={{
                                        inputLabel: {
                                           shrink: true
                                        }
                                    }}
                                />
                            </Grid>
                            <Grid size={{ xs: 12, sm: 12 }} sx={{ display: 'flex' }}>
                                <FormGroup>
                                    <FormControlLabel
                                        control={<Switch defaultChecked />}
                                        name="enabled"
                                        label={data.enabled ? 'Enabled' : 'Disabled'}
                                        onChange={handleCheckboxFieldChange}
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
