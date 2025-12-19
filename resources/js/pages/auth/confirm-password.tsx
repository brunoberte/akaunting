import AuthLayout from '@/layouts/auth-layout';
import { useForm } from '@inertiajs/react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import FormLabel from '@mui/material/FormLabel';
import TextField from '@mui/material/TextField';
import { FormEventHandler } from 'react';

type ResetPasswordForm = {
    password: string;
};

export default function ConfirmPassword() {
    const { setData, post, errors, reset } = useForm<Required<ResetPasswordForm>>({
        password: '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.confirm.store'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Confirm password" description="Please confirm your password below">
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
                        autoFocus={true}
                        required
                        fullWidth
                        variant="outlined"
                        color={errors.password ? 'error' : 'primary'}
                    />
                </FormControl>
                <Button type="submit" fullWidth variant="contained">
                    Confirm password
                </Button>
            </Box>
        </AuthLayout>
    );
}
