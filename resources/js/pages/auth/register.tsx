import AuthLayout from '@/layouts/auth-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormLabel from '@mui/material/FormLabel';
import TextField from '@mui/material/TextField';
import Typography from '@mui/material/Typography';
import { FormEventHandler, useState } from 'react';

type RegisterForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const [open, setOpen] = useState(false);
    const handleClickOpen = () => {
        setOpen(true);
    };
    const handleClose = () => {
        setOpen(false);
    };

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };


    return (
        <AuthLayout title="Create an account" description="Enter your details below to create your account">
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
                    <FormLabel htmlFor="name">Name</FormLabel>
                    <TextField
                        error={!!errors.name}
                        helperText={errors.name}
                        id="name"
                        type="name"
                        name="name"
                        onChange={(e) => setData('name', e.target.value)}
                        autoComplete="name"
                        autoFocus
                        required
                        fullWidth
                        variant="outlined"
                        color={errors.name ? 'error' : 'primary'}
                    />
                </FormControl>
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
                <FormControl>
                    <FormLabel htmlFor="password_confirmation">Confirm Password</FormLabel>
                    <TextField
                        error={!!errors.password_confirmation}
                        helperText={errors.password_confirmation}
                        name="password_confirmation"
                        placeholder="••••••"
                        type="password"
                        id="password_confirmation"
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                        fullWidth
                        variant="outlined"
                        color={errors.password_confirmation ? 'error' : 'primary'}
                    />
                </FormControl>
                {/*<ForgotPassword open={open} handleClose={handleClose} />*/}
                <Button type="submit" fullWidth variant="contained">
                    Create account
                </Button>
                <Typography sx={{ textAlign: 'center' }}>
                    Already have an account?{' '}
                    <Link href={route('login')} variant="body2" sx={{ alignSelf: 'center' }}>
                        Sign in
                    </Link>
                </Typography>
            </Box>
        </AuthLayout>
    );
}
