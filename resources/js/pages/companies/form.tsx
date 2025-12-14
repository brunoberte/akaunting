import PageContainer from '@/components/PageContainer';
import { ErrorList } from '@/components/ui/error-list';
import AppLayout from '@/layouts/app-layout';
import { Transition } from '@headlessui/react';
import { Head, router, useForm } from '@inertiajs/react';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormControlLabel from '@mui/material/FormControlLabel';
import FormGroup from '@mui/material/FormGroup';
import Grid from '@mui/material/Grid';
import Stack from '@mui/material/Stack';
import Switch from '@mui/material/Switch';
import TextField from '@mui/material/TextField';
import * as React from 'react';
import { toast } from 'sonner';

type CompanyModel = {
    id: number | null;
    domain: string;
    company_name: string;
    enabled: boolean;
};

export interface AccountFormState {
    values: Partial<Omit<CompanyModel, 'id'>>;
    errors: Partial<Record<keyof AccountFormState['values'], string>>;
}

export default function CompanyForm({ company }: { company: CompanyModel }) {
    const { data, setData, patch, post, errors, processing, recentlySuccessful } = useForm<Required<CompanyModel>>({
        id: company.id || null,
        domain: company.domain,
        company_name: company.company_name,
        enabled: company.enabled == null ? true : company.enabled,
    });

    const [isSubmitting, setIsSubmitting] = React.useState(false);

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        setIsSubmitting(true);
        try {
            if (data.id === null) {
                post(route('companies.create'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('Record created successfully');
                    },
                });
            } else {
                patch(route('companies.update', data.id), {
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
    const handleCheckboxFieldChange = React.useCallback(
        (event: React.ChangeEvent<HTMLInputElement>) => {
            console.log(event);
            setData(event.target.name, event.target.checked);
        },
        [setData],
    );

    const handleReset = React.useCallback(() => {
        // if (onReset) {
        //     onReset(formValues);
        // }
    }, []);

    const handleBack = React.useCallback(() => {
        router.get(route('companies.index'));
    }, []);

    const pageTitle = company.id ? 'Edit company' : 'Add new company';

    return (
        <AppLayout breadcrumbs={[{ title: 'Companies', path: route('companies.index') }, { title: pageTitle }]}>
            <Head title={pageTitle} />
            <PageContainer title={pageTitle}>
                <Box component="form" onSubmit={handleSubmit} noValidate autoComplete="off" onReset={handleReset} sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} columns={12} sx={{ mb: 2, width: '100%' }}>
                            <Grid size={{ xs: 12, sm: 8 }}>
                                <FormControl error={!!errors.company_name} variant="standard" fullWidth>
                                    <TextField
                                        value={data.company_name ?? ''}
                                        autoFocus={true}
                                        onChange={handleTextFieldChange}
                                        name="company_name"
                                        label="Company Name"
                                        error={!!errors.company_name}
                                        helperText={errors.company_name ?? ' '}
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

                            <Grid size={{ xs: 12, sm: 8 }}>
                                <FormControl error={!!errors.domain} variant="standard" fullWidth>
                                    <TextField
                                        value={data.domain ?? ''}
                                        autoFocus={true}
                                        onChange={handleTextFieldChange}
                                        name="domain"
                                        label="Domain"
                                        error={!!errors.domain}
                                        helperText={errors.domain ?? ' '}
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
