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

type PaymentModel = {
    id: number | null;
    account_id: number | null;
    paid_at: string | null;
    amount: number | null;
    currency_code: string | null;
    vendor_id: number | null;
    description: string | null;
    category_id: number | null;
    reference: string | null;
};
type AccountBasicType = { id: string | number; name: string; currency_code: string; balance: number };
type IdNameType = { id: string | number; name: string };
type IdNameTypeType = { id: string | number; name: string; type: string };

export interface PaymentFormState {
    values: Partial<Omit<PaymentModel, 'id'>>;
    errors: Partial<Record<keyof PaymentFormState['values'], string>>;
}

export default function PaymentForm({
    payment,
    account_list,
    category_list,
    vendor_list,
}: {
    payment: PaymentModel;
    account_list: Array<AccountBasicType>;
    category_list: Array<IdNameTypeType>;
    vendor_list: Array<IdNameType>;
}) {
    const { data, setData, patch, post, errors, processing, recentlySuccessful } = useForm<Required<PaymentModel>>({
        id: payment.id || null,
        account_id: payment.account_id || null,
        paid_at: payment.paid_at || null,
        amount: payment.amount || null,
        currency_code: payment.currency_code || null,
        vendor_id: payment.vendor_id || null,
        description: payment.description || null,
        category_id: payment.category_id || null,
        reference: payment.reference || null,
    });

    const [paidAt, setPaidAt] = React.useState<Dayjs | null>(dayjs(data.paid_at || null, 'YYYY-MM-DD HH:mm:ss'));
    const [amount, setAmount] = React.useState(AutoNumeric.format(data.amount || '0'));

    const [isSubmitting, setIsSubmitting] = React.useState(false);

    React.useEffect(() => {
        setData('amount', AutoNumeric.unformat(amount));
    }, [amount]);

    React.useEffect(() => {
        setData('currency_code', currentAccountCurrency());
    }, [data.account_id]);

    React.useEffect(() => {
        setData('paid_at', paidAt?.format('YYYY-MM-DD') ?? null);
    }, [paidAt]);

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        setIsSubmitting(true);
        try {
            if (data.id === null) {
                post(route('transactions.payments.create'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Payment created successfully');
                    },
                });
            } else {
                patch(route('transactions.payments.update', data.id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Payment updated successfully');
                    },
                });
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    function currentAccountCurrency() {
        const account = account_list.find((x) => x.id == data.account_id);
        return account?.currency_code || 'USD';
    }

    function showCurrentBalance() {
        const account = account_list.find((x) => x.id == data.account_id);
        return (account?.currency_code || 'USD') + ' ' + (account?.balance.toFixed(2) || '0.00');
    }

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

    const pageTitle = data.id ? 'Edit Payment' : 'Add new Payment';

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Transactions', path: route('transactions.index', { account_id: data.account_id || null }) },
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
                                    <StaticDatePicker value={paidAt} onChange={(newValue) => setPaidAt(newValue)} />
                                </LocalizationProvider>
                            </Grid>

                            <Grid size="grow">
                                <FormControl error={!!errors.account_id} variant="standard" fullWidth>
                                    <InputLabel id="currency_code-label" shrink={true}>
                                        Account
                                    </InputLabel>
                                    <NativeSelect
                                        value={data.account_id ?? ''}
                                        autoFocus={true}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        name="account_id"
                                        fullWidth
                                    >
                                        <option value=""></option>
                                        {account_list.map((account) => (
                                            <option key={account.id} value={account.id}>
                                                {account.name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <FormHelperText>Current balance: {showCurrentBalance()}</FormHelperText>
                                    <FormHelperText>{errors.account_id ?? ' '}</FormHelperText>
                                </FormControl>

                                <FormControl error={!!errors.category_id} variant="standard" fullWidth>
                                    <InputLabel id="currency_code-label" shrink={true}>
                                        Category
                                    </InputLabel>
                                    <NativeSelect
                                        value={data.category_id ?? ''}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        name="category_id"
                                        fullWidth
                                    >
                                        <option value=""></option>
                                        {category_list.map((item) => (
                                            <option key={item.id} value={item.id}>
                                                {item.name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <FormHelperText>{errors.category_id ?? ' '}</FormHelperText>
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

                                <FormControl error={!!errors.vendor_id} variant="standard" fullWidth>
                                    <InputLabel shrink={true}>Vendor</InputLabel>
                                    <NativeSelect
                                        value={data.vendor_id ?? ''}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        name="vendor_id"
                                        fullWidth
                                    >
                                        <option value=""></option>
                                        {vendor_list.map((item) => (
                                            <option key={item.id} value={item.id}>
                                                {item.name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <FormHelperText>{errors.vendor_id ?? ' '}</FormHelperText>
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
                            href={route('transactions.index', { account_id: data.account_id || null })}
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
