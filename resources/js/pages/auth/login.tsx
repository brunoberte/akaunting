import AuthLayout from '@/layouts/auth-layout';
import { Link, useForm } from '@inertiajs/react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Checkbox from '@mui/material/Checkbox';
import FormControl from '@mui/material/FormControl';
import FormControlLabel from '@mui/material/FormControlLabel';
import FormLabel from '@mui/material/FormLabel';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { FormEventHandler } from 'react';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { setData, post, errors, reset } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Log in" description="Enter your email and password below to log in">
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
                <FormControl>
                    <FormLabel htmlFor="password">Password</FormLabel>
                    <TextField
                        error={!!errors.password}
                        helperText={errors.password}
                        name="password"
                        placeholder="••••••"
                        type="password"
                        id="password"
                        onChange={(e) => setData('password', e.target.value)}
                        autoComplete="current-password"
                        required
                        fullWidth
                        variant="outlined"
                        color={errors.password ? 'error' : 'primary'}
                    />
                </FormControl>
                <FormControlLabel control={<Checkbox value="remember"
                                                     onChange={(e) => setData('remember', e.target.checked)}
                                                     color="primary" />} label="Remember me" />
                <Button type="submit" fullWidth variant="contained">
                    Sign in
                </Button>
                {canResetPassword && (
                <Link component={Link} type="button" href={route('password.request')} variant="body2" sx={{ alignSelf: 'center' }}>
                    Forgot your password?
                </Link>
                )}
                <Typography sx={{ textAlign: 'center' }}>
                    Don&apos;t have an account?{' '}
                    <Link href={route('register')} variant="body2" sx={{ alignSelf: 'center' }}>
                        Sign up
                    </Link>
                </Typography>
            </Box>

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}
        </AuthLayout>
    );
}
