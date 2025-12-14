import AuthMaterialUi from '@/layouts/auth/auth-material-ui';
import { Head } from '@inertiajs/react';
import Typography from '@mui/material/Typography';

export default function AuthLayout({ children, title, description, ...props }: { children: React.ReactNode; title: string; description: string }) {
    return (
        <AuthMaterialUi {...props}>
            <Head title={title} />
            <Typography component="h1" variant="h1" sx={{ width: '100%', fontSize: 'clamp(2rem, 10vw, 2.15rem)' }}>
                {title}
            </Typography>
            <Typography component="p">
                {description}
            </Typography>
            {children}
        </AuthMaterialUi>
    );
}
