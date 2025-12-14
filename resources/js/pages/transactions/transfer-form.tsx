import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm } from '@inertiajs/react';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormGroup from '@mui/material/FormGroup';
import FormHelperText from '@mui/material/FormHelperText';
import Grid from '@mui/material/Grid';
import InputLabel from '@mui/material/InputLabel';
import NativeSelect from '@mui/material/NativeSelect';
import { SelectProps } from '@mui/material/Select';
import Stack from '@mui/material/Stack';
import TextField from '@mui/material/TextField';
import { StaticDatePicker } from '@mui/x-date-pickers';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import AutoNumeric from 'autonumeric';
import dayjs, { Dayjs } from 'dayjs';
import 'dayjs/locale/pt-br';
import { AutoNumericMaterialUIInput } from 'material-ui-autonumeric';
import * as React from 'react';
import { toast } from 'sonner';
import { ErrorList } from '@/components/ui/error-list';

type TransferModel = {
    id: number | null;
    from_account_id: number | null;
    to_account_id: number | null;
    transferred_at: string | null;
    amount: number | null;
    description: string | null;
    reference: string | null;
};
type AccountBasicType = { id: string | number; name: string; currency_code: string; balance: number };

export interface PaymentFormState {
    values: Partial<Omit<TransferModel, 'id'>>;
    errors: Partial<Record<keyof PaymentFormState['values'], string>>;
}

export default function PaymentForm({
    record,
    account_list,
}: {
    record: TransferModel;
    account_list: Array<AccountBasicType>;
}) {
    const { data, setData, patch, post, errors, processing, recentlySuccessful } = useForm<Required<TransferModel>>({
        id: record.id || null,
        from_account_id: record.from_account_id || null,
        to_account_id: record.to_account_id || null,
        transferred_at: record.transferred_at || null,
        amount: record.amount || null,
        description: record.description || null,
        reference: record.reference || null,
    });

    const [transferredAt, setTransferredAt] = React.useState<Dayjs | null>(dayjs(data.transferred_at || null, 'YYYY-MM-DD HH:mm:ss'));
    const [amount, setAmount] = React.useState(AutoNumeric.format(data.amount || '0'));

    const [isSubmitting, setIsSubmitting] = React.useState(false);

    React.useEffect(() => {
        setData('amount', AutoNumeric.unformat(amount));
    }, [amount]);

    React.useEffect(() => {
        setData('transferred_at', transferredAt?.format('YYYY-MM-DD') ?? null);
    }, [transferredAt]);

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        setIsSubmitting(true);
        try {
            if (data.id === null) {
                post(route('transactions.transfers.create'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Transfer created successfully');
                    },
                });
            } else {
                patch(route('transactions.transfers.update', data.id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Transfer updated successfully');
                    },
                });
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleTextFieldChange = React.useCallback(
        (event: React.ChangeEvent<HTMLInputElement>) => {
            setData(event.target.name, event.target.value);
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

    const pageTitle = data.id ? 'Edit Transfer' : 'New Transfer';

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Transactions', path: route('transactions.index', { account_id: data.from_account_id || null }) },
                { title: pageTitle },
            ]}
        >
            <Head title={pageTitle} />
            <PageContainer title={pageTitle}>
                <Box component="form" onSubmit={handleSubmit} noValidate autoComplete="off" onReset={handleReset} sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} columns={12} sx={{ mb: 2, width: '100%' }}>
                            <Grid size="auto">
                                <LocalizationProvider dateAdapter={AdapterDayjs} adapterLocale={'pt-br'}>
                                    <StaticDatePicker value={transferredAt} onChange={(newValue) => setTransferredAt(newValue)} />
                                </LocalizationProvider>
                            </Grid>

                            <Grid size="grow">
                                <FormControl error={!!errors.from_account_id} variant="standard" fullWidth>
                                    <InputLabel shrink={true}>From Account</InputLabel>
                                    <NativeSelect
                                        value={data.from_account_id ?? ''}
                                        autoFocus={true}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        name="from_account_id"
                                        fullWidth
                                    >
                                        <option value=""></option>
                                        {account_list.map((account) => (
                                            <option key={account.id} value={account.id}>
                                                {account.name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <FormHelperText>{errors.from_account_id ?? ' '}</FormHelperText>
                                </FormControl>

                                <FormControl error={!!errors.to_account_id} variant="standard" fullWidth>
                                    <InputLabel shrink={true}>To Account</InputLabel>
                                    <NativeSelect
                                        value={data.to_account_id ?? ''}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        name="to_account_id"
                                        fullWidth
                                    >
                                        <option value=""></option>
                                        {account_list.map((account) => (
                                            <option key={account.id} value={account.id}>
                                                {account.name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <FormHelperText>{errors.to_account_id ?? ' '}</FormHelperText>
                                </FormControl>

                                <FormControl error={!!errors.amount} variant="standard" fullWidth>
                                    <InputLabel shrink={true}>Amount</InputLabel>
                                    <AutoNumericMaterialUIInput
                                        valueState={{
                                            state: amount,
                                            stateSetter: setAmount,
                                        }}
                                        autoNumericOptions={{
                                            // currencySymbol: currentAccountCurrency(),
                                            outputFormat: "string",
                                            decimalPlacesRawValue: 2, emptyInputBehavior: 'zero', modifyValueOnWheel: false }}
                                    />
                                    <FormHelperText>{errors.amount ?? ' '}</FormHelperText>
                                </FormControl>

                                <FormControl error={!!errors.description} variant="standard" fullWidth>
                                    <TextField
                                        value={data.description ?? ''}
                                        onChange={handleTextFieldChange}
                                        multiline={true}
                                        name="description"
                                        label="Description"
                                        error={!!errors.description}
                                        helperText={errors.description ?? ' '}
                                        variant="standard"
                                        fullWidth
                                        slotProps={{
                                            inputLabel: {
                                                shrink: true,
                                            },
                                        }}
                                    />
                                </FormControl>

                                <ErrorList errors={errors} />
                            </Grid>
                        </Grid>
                    </FormGroup>

                    <Stack direction="row" spacing={2} justifyContent="space-between">
                        <Button
                            component={Link}
                            variant="contained"
                            size="large"
                            startIcon={<ArrowBackIcon />}
                            href={route('transactions.index', { account_id: data.from_account_id || null })}
                        >
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
