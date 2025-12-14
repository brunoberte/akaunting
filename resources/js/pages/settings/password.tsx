import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormGroup from '@mui/material/FormGroup';
import FormLabel from '@mui/material/FormLabel';
import Grid from '@mui/material/Grid';
import Stack from '@mui/material/Stack';
import TextField from '@mui/material/TextField';
import { FormEventHandler } from 'react';
import { toast } from 'sonner';
import { ErrorList } from '@/components/ui/error-list';
import * as React from 'react';

type PasswordForm = {
    current_password: string;
    password: string;
    password_confirmation: string;
};

export default function Password() {
    const { data, setData, put, errors, processing } = useForm<Required<PasswordForm>>({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('success');
                toast.success('Password updated successfully');
                setData({
                    current_password: '',
                    password: '',
                    password_confirmation: '',
                });
            },
        });
    };

    const pageTitle = 'Password update';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('profile.edit') }]}>
            <Head title={pageTitle} />
            <PageContainer title={pageTitle} breadcrumbs={[{ title: pageTitle }]}>
                <Box component="form" onSubmit={submit} noValidate autoComplete="off" sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} columns={12} sx={{ mb: 2, width: '100%' }}>
                            <FormControl fullWidth>
                                <FormLabel htmlFor="name">Current Password</FormLabel>
                                <TextField
                                    error={!!errors.current_password}
                                    helperText={errors.current_password}
                                    id="current_password"
                                    type="password"
                                    name="current_password"
                                    value={data.current_password}
                                    onChange={(e) => setData('current_password', e.target.value)}
                                    autoFocus
                                    required
                                    fullWidth
                                    variant="outlined"
                                    color={errors.current_password ? 'error' : 'primary'}
                                />
                            </FormControl>
                            <FormControl fullWidth>
                                <FormLabel htmlFor="name">New Password</FormLabel>
                                <TextField
                                    error={!!errors.password}
                                    helperText={errors.password}
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    autoComplete="password"
                                    required
                                    fullWidth
                                    variant="outlined"
                                    color={errors.password ? 'error' : 'primary'}
                                />
                            </FormControl>
                            <FormControl fullWidth>
                                <FormLabel htmlFor="name">Password Confirmation</FormLabel>
                                <TextField
                                    error={!!errors.password_confirmation}
                                    helperText={errors.password_confirmation}
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    required
                                    fullWidth
                                    variant="outlined"
                                    color={errors.password ? 'error' : 'primary'}
                                />
                            </FormControl>
                        </Grid>

                        <ErrorList errors={errors} />
                    </FormGroup>


                    <Stack direction="row" spacing={2} justifyContent="space-between">
                        <Button component={Link} variant="contained" size="large" startIcon={<ArrowBackIcon />} href={route('home')}>
                            Back
                        </Button>
                        <Button disabled={processing} type="submit" variant="contained" size="large" loading={processing}>
                            {processing ? "Saving..." : "Save"}
                        </Button>
                    </Stack>
                </Box>
            </PageContainer>
        </AppLayout>
    );
}
