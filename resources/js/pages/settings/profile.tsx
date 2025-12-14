import PageContainer from '@/components/PageContainer';
import AppLayout from '@/layouts/app-layout';
import { type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
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

type ProfileForm = {
    name: string;
    email: string;
};

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        name: auth.user.name,
        email: auth.user.email,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Profile updated successfully');
            },
        });
    };

    const pageTitle = 'Profile';

    return (
        <AppLayout breadcrumbs={[{ title: pageTitle, path: route('profile.edit') }]}>
            <Head title={pageTitle} />
            <PageContainer title={pageTitle} breadcrumbs={[{ title: pageTitle }]}>
                <Box component="form" onSubmit={submit} noValidate autoComplete="off" sx={{ width: '100%' }}>
                    <FormGroup>
                        <Grid container spacing={2} columns={12} sx={{ mb: 2, width: '100%' }}>
                            <FormControl fullWidth>
                                <FormLabel htmlFor="name">Name</FormLabel>
                                <TextField
                                    error={!!errors.name}
                                    helperText={errors.name}
                                    id="name"
                                    type="name"
                                    name="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    autoComplete="name"
                                    autoFocus
                                    required
                                    fullWidth
                                    variant="outlined"
                                    color={errors.name ? 'error' : 'primary'}
                                />
                            </FormControl>
                            <FormControl fullWidth>
                                <FormLabel htmlFor="email">Email</FormLabel>
                                <TextField
                                    error={!!errors.email}
                                    helperText={errors.email}
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="your@email.com"
                                    autoComplete="email"
                                    autoFocus
                                    required
                                    fullWidth
                                    variant="outlined"
                                    color={errors.email ? 'error' : 'primary'}
                                />
                            </FormControl>

                            {mustVerifyEmail && auth.user.email_verified_at === null && (
                                <div>
                                    <p className="text-muted-foreground -mt-4 text-sm">
                                        Your email address is unverified.{' '}
                                        <Link
                                            href={route('verification.send')}
                                            method="post"
                                            as="button"
                                            className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                        >
                                            Click here to resend the verification email.
                                        </Link>
                                    </p>

                                    {status === 'verification-link-sent' && (
                                        <div className="mt-2 text-sm font-medium text-green-600">
                                            A new verification link has been sent to your email address.
                                        </div>
                                    )}
                                </div>
                            )}
                        </Grid>
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
