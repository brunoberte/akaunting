import AuthLayout from '@/layouts/auth-layout';
import { useForm, Link } from '@inertiajs/react';
import Button from '@mui/material/Button';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            )}

            <form onSubmit={submit} className="space-y-6 text-center">
                <Button type="submit" fullWidth variant="contained">
                    Resend verification email
                </Button>

                <Button component={Link} href={route('logout')} method="post">
                    Log out
                </Button>
            </form>
        </AuthLayout>
    );
}
