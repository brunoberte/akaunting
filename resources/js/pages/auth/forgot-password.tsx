import AuthLayout from '@/layouts/auth-layout';
import { Link, useForm } from '@inertiajs/react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormLabel from '@mui/material/FormLabel';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { FormEventHandler } from 'react';
import Alert from '@mui/material/Alert';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm<Required<{ email: string }>>({
        email: '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <AuthLayout title="Forgot password" description="Enter your email to receive a password reset link">
            <Box
                component="form"
                onSubmit={handleSubmit}
                noValidate
                sx={{
                    display: 'flex',
                    flexDirection: 'column',
                    width: '100%',
                    gap: 2,
                }}
            >
                <FormControl>
                    <FormLabel htmlFor="email">Email</FormLabel>
                    <TextField
                        error={!!errors.email}
                        helperText={errors.email}
                        id="email"
                        type="email"
                        name="email"
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
                <Button type="submit" fullWidth variant="contained" loading={processing} loadingIndicator={"Processing..."}>
                    Email password reset link
                </Button>

                {status && <Alert severity={"info"}>{status}</Alert>}

                <Typography sx={{ textAlign: 'center' }}>
                    Don&apos;t have an account?{' '}
                    <Link href={route('register')} variant="body2" sx={{ alignSelf: 'center' }}>
                        Sign up
                    </Link>
                </Typography>
                <Typography sx={{ textAlign: 'center' }}>
                    Or, go back to{' '}
                    <Link href={route('login')} variant="body2" sx={{ alignSelf: 'center' }}>
                        Log in
                    </Link>
                </Typography>
            </Box>


        </AuthLayout>
    );
}
