import AppNavbar from '@/components/AppNavbar';
import AppTheme from '@/components/AppTheme';
import Header from '@/components/Header';
import { Breadcrumb } from '@/components/NavbarBreadcrumbs';
import SideMenu from '@/components/SideMenu';
import DialogsProvider from '@/hooks/useDialogs/DialogsProvider';
import NotificationsProvider from '@/hooks/useNotifications/NotificationsProvider';
import { chartsCustomizations, dataGridCustomizations, datePickersCustomizations, treeViewCustomizations, tableCustomizations } from '@/theme/customizations';
import Box from '@mui/material/Box';
import CssBaseline from '@mui/material/CssBaseline';
import Stack from '@mui/material/Stack';
import { alpha } from '@mui/material/styles';
import type { PropsWithChildren } from 'react';
import { Toaster } from 'sonner';

const xThemeComponents = {
    ...chartsCustomizations,
    ...dataGridCustomizations,
    ...tableCustomizations,
    ...datePickersCustomizations,
    ...treeViewCustomizations,
};

export default function AppMaterialUi({
    children,
    breadcrumbs,
    disableCustomTheme,
}: PropsWithChildren<{ breadcrumbs?: Breadcrumb[]; disableCustomTheme?: boolean }>) {
    return (
        <AppTheme disableCustomTheme={disableCustomTheme} themeComponents={xThemeComponents}>
            <CssBaseline enableColorScheme />
            <Toaster position={'top-right'}></Toaster>
            <NotificationsProvider>
                <DialogsProvider>
                    <Box sx={{ display: 'flex' }}>
                        <SideMenu />
                        <AppNavbar />
                        <Box
                            component="main"
                            sx={(theme) => ({
                                flexGrow: 1,
                                backgroundColor: theme.vars
                                    ? `rgba(${theme.vars.palette.background.defaultChannel} / 1)`
                                    : alpha(theme.palette.background.default, 1),
                                overflow: 'auto',
                            })}
                        >
                            <Stack
                                spacing={2}
                                sx={{
                                    alignItems: 'center',
                                    mx: 3,
                                    pb: 5,
                                    mt: { xs: 8, md: 0 },
                                }}
                            >
                                <Header breadcrumbs={breadcrumbs} />
                                {children}
                            </Stack>
                        </Box>
                    </Box>
                </DialogsProvider>
            </NotificationsProvider>
        </AppTheme>
    );
}
