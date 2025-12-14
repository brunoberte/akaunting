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
import { Dayjs } from 'dayjs';
import * as React from 'react';
import { toast } from 'sonner';
import { Transition } from '@headlessui/react';
import NativeSelect from '@mui/material/NativeSelect';

type CategoryModel = {
    id: number | null;
    name: string;
    type: string;
    color: string;
    enabled: boolean;
};

export interface CategoryFormState {
    values: Partial<Omit<CategoryModel, 'id'>>;
    errors: Partial<Record<keyof CategoryFormState['values'], string>>;
}

export default function CategoryForm({ category }: { category: CategoryModel }) {
    const { data, setData, patch, post, errors, processing, recentlySuccessful } = useForm<Required<CategoryModel>>({
        id: category.id || null,
        name: category.name,
        type: category.type,
        color: category.color,
        enabled: category.enabled == null ? true : category.enabled,
    });

    const [isSubmitting, setIsSubmitting] = React.useState(false);

    const handleSubmit = React.useCallback(
        async (event: React.FormEvent<HTMLFormElement>) => {
            event.preventDefault();

            setIsSubmitting(true);
            try {
                if (data.id === null) {
                    post(route('categories.create'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Category created successfully');
                        },
                    });
                } else {
                    patch(route('categories.update', data.id), {
                        preserveScroll: true,
                        onSuccess: () => {
                            toast.success('Category updated successfully');
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
        (event: React.ChangeEvent<HTMLInputElement>, checked: boolean) => {
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
        router.get(route('categories.index'));
    }, []);

    const pageTitle = category.id ? 'Edit' : 'Add new';

    return (
        <AppLayout breadcrumbs={[{ title: 'Categories', path: route('categories.index') }, { title: pageTitle }]}>
            <Head title={pageTitle} />
            <PageContainer title={pageTitle}>
                <Box component="form" onSubmit={handleSubmit} noValidate autoComplete="off" onReset={handleReset} sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} sx={{ mb: 2, width: '100%' }}>
                            <Grid size={{ xs: 12, sm: 12 }} sx={{ display: 'flex' }}>
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
                                           shrink: true
                                        }
                                    }}
                                />
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <FormControl error={!!errors.type} variant="standard" fullWidth>
                                    <InputLabel id="type-label" shrink={true}>Type</InputLabel>
                                    <NativeSelect
                                        value={data.type ?? ''}
                                        onChange={handleSelectFieldChange as SelectProps['onChange']}
                                        labelId="type-label"
                                        name="type"
                                        variant="standard"
                                        defaultValue=""
                                        fullWidth
                                    >
                                        <option value=""></option>
                                        <option value="income">Income</option>
                                        <option value="expense">Expense</option>
                                    </NativeSelect>
                                    <FormHelperText>{errors.type ?? ' '}</FormHelperText>
                                </FormControl>
                            </Grid>

                            <Grid size={{ xs: 12, sm: 6 }} sx={{ display: 'flex' }}>
                                <TextField
                                    value={data.color ?? ''}
                                    onChange={handleTextFieldChange}
                                    name="color"
                                    label="Color"
                                    error={!!errors.color}
                                    helperText={errors.color ?? ' '}
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
