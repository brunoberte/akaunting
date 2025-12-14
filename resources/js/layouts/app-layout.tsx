import AppMaterialUi from '@/layouts/app/app-material-ui';
import { type ReactNode } from 'react';
import { Breadcrumb } from '@/components/NavbarBreadcrumbs';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: Breadcrumb[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <AppMaterialUi breadcrumbs={breadcrumbs} {...props}>
        {children}
    </AppMaterialUi>
);
