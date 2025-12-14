import PageContainer from '@/components/PageContainer';
import { ErrorList } from '@/components/ui/error-list';
import AppLayout from '@/layouts/app-layout';
import { Transition } from '@headlessui/react';
import { Head, router, useForm } from '@inertiajs/react';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormGroup from '@mui/material/FormGroup';
import FormHelperText from '@mui/material/FormHelperText';
import Grid from '@mui/material/Grid';
import InputLabel from '@mui/material/InputLabel';
import NativeSelect from '@mui/material/NativeSelect';
import { SelectChangeEvent, SelectProps } from '@mui/material/Select';
import Stack from '@mui/material/Stack';
import TextField from '@mui/material/TextField';
import { StaticDatePicker } from '@mui/x-date-pickers';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import AutoNumeric from 'autonumeric';
import dayjs, { Dayjs } from 'dayjs';
import { AutoNumericMaterialUIInput } from 'material-ui-autonumeric';
import * as React from 'react';
import { toast } from 'sonner';

type PayableModel = {
    id: number | null;
    account_id: number;
    due_at: string;
    currency_code: string;
    amount: number;
    title: string;
    vendor_id: number;
    category_id: number;
    notes: string;
    recurring_frequency: string;
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

export interface AccountFormState {
    values: Partial<Omit<PayableModel, 'id'>>;
    errors: Partial<Record<keyof AccountFormState['values'], string>>;
}

export default function PayableForm({
    payable,
    account_list,
    vendor_list,
    category_list,
}: {
    payable: PayableModel;
    account_list: IdNameCurrencyCodeSchema[];
    vendor_list: IdNameSchema[];
    category_list: IdNameSchema[];
}) {
    const { data, setData, patch, post, errors, processing, recentlySuccessful } = useForm<Required<PayableModel>>({
        id: payable.id || null,
        account_id: payable.account_id || 0,
        due_at: payable.due_at || '',
        currency_code: payable.currency_code || '',
        amount: payable.amount || 0,
        title: payable.title || '',
        vendor_id: payable.vendor_id || 0,
        category_id: payable.category_id || 0,
        notes: payable.notes || '',
        recurring_frequency: payable.recurring_frequency || 'no',
    });

    const [dueAt, setDueAt] = React.useState<Dayjs | null>(dayjs(data.due_at || null, 'YYYY-MM-DD HH:mm:ss'));
    const [amount, setAmount] = React.useState(AutoNumeric.format(data.amount || '0'));
    const [isSubmitting, setIsSubmitting] = React.useState(false);

    const recurring_options = [
        { value: 'no', label: 'No' },
        { value: 'weekly', label: 'Weekly' },
        { value: 'monthly', label: 'Monthly' },
        { value: 'yearly', label: 'Yearly' },
    ];

    const currentAccountCurrency = () => {
        const account = account_list.find((x) => x.id == data.account_id);
        return account?.currency_code || 'USD';
    }

    React.useEffect(() => {
        setData('amount', AutoNumeric.unformat(amount));
    }, [amount]);

    React.useEffect(() => {
        setData('currency_code', currentAccountCurrency());
    }, [data.account_id]);

    React.useEffect(() => {
        setData('due_at', dueAt?.format('YYYY-MM-DD') ?? null);
    }, [dueAt]);

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        setIsSubmitting(true);
        try {
            if (data.id === null) {
                post(route('payables.create'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Record created successfully');
                    },
                });
            } else {
                patch(route('payables.update', data.id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Record updated successfully');
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

    const handleBack = React.useCallback(() => {
        router.get(route('payables.index'));
    }, []);

    const pageTitle = payable.id ? 'Edit payable' : 'Add new payable';

    return (
        <AppLayout breadcrumbs={[{ title: 'Payables', path: route('payables.index') }, { title: pageTitle }]}>
            <Head title={pageTitle} />
            <PageContainer title={pageTitle}>
                <Box component="form" onSubmit={handleSubmit} noValidate autoComplete="off" onReset={handleReset} sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} columns={12} sx={{ mb: 2, width: '100%' }}>
                            <Grid size={{ xs: 12, sm: 4 }}>
                                <LocalizationProvider dateAdapter={AdapterDayjs} adapterLocale={'pt-br'}>
                                    <StaticDatePicker value={dueAt} onChange={(newValue) => setDueAt(newValue)} />
                                </LocalizationProvider>
                            </Grid>

                            <Grid size={{ xs: 12, sm: 8 }}>
                                <FormControl error={!!errors.title} variant="standard" fullWidth>
                                    <TextField
                                        value={data.title ?? ''}
                                        autoFocus={true}
                                        onChange={handleTextFieldChange}
                                        name="title"
                                        label="Title"
                                        error={!!errors.title}
                                        helperText={errors.title ?? ' '}
                                        variant="standard"
                                        fullWidth
                                        slotProps={{
                                            inputLabel: {
                                                shrink: true,
                                            },
                                        }}
                                    />
                                </FormControl>

                                <FormControl error={!!errors.account_id} variant="standard" fullWidth>
                                    <InputLabel id="currency_code-label" shrink={true}>
                                        Account
                                    </InputLabel>
                                    <NativeSelect
                                        value={data.account_id ?? ''}
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
                                    <FormHelperText>{errors.account_id ?? ' '}</FormHelperText>
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
                                            outputFormat: 'string',
                                            decimalPlacesRawValue: 2,
                                            emptyInputBehavior: 'zero',
                                            modifyValueOnWheel: false,
                                        }}
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

                                <FormControl error={!!errors.recurring_frequency} variant="standard" fullWidth>
                                    <InputLabel id="currency_code-label" shrink={true}>
                                        Recurring
                                    </InputLabel>
                                    <NativeSelect
                                        value={data.recurring_frequency ?? ''}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        name="recurring_frequency"
                                        fullWidth
                                    >
                                        <option value=""></option>
                                        {recurring_options.map((item) => (
                                            <option key={item.value} value={item.value}>
                                                {item.label}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <FormHelperText>{errors.recurring_frequency ?? ' '}</FormHelperText>
                                </FormControl>

                                <ErrorList errors={errors} />
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
