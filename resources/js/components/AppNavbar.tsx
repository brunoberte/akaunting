import ColorModeIconDropdown from '@/components/ColorModeIconDropdown';
import MenuRoundedIcon from '@mui/icons-material/MenuRounded';
import { SvgIcon } from '@mui/material';
import AppBar from '@mui/material/AppBar';
import Box from '@mui/material/Box';
import Stack from '@mui/material/Stack';
import { styled } from '@mui/material/styles';
import { tabsClasses } from '@mui/material/Tabs';
import MuiToolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import * as React from 'react';
import MenuButton from './MenuButton';
import SideMenuMobile from './SideMenuMobile';

const Toolbar = styled(MuiToolbar)({
    width: '100%',
    padding: '12px',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'start',
    justifyContent: 'center',
    gap: '12px',
    flexShrink: 0,
    [`& ${tabsClasses.flexContainer}`]: {
        gap: '8px',
        p: '8px',
        pb: 0,
    },
});

export default function AppNavbar() {
    const [open, setOpen] = React.useState(false);

    const toggleDrawer = (newOpen: boolean) => () => {
        setOpen(newOpen);
    };

    return (
        <AppBar
            position="fixed"
            sx={{
                display: { xs: 'auto', md: 'none' },
                boxShadow: 0,
                bgcolor: 'background.paper',
                backgroundImage: 'none',
                borderBottom: '1px solid',
                borderColor: 'divider',
                top: 'var(--template-frame-height, 0px)',
            }}
        >
            <Toolbar variant="regular">
                <Stack
                    direction="row"
                    sx={{
                        alignItems: 'center',
                        flexGrow: 1,
                        width: '100%',
                        gap: 1,
                    }}
                >
                    <Stack direction="row" spacing={1} sx={{ justifyContent: 'center', mr: 'auto' }}>
                        <CustomIcon />
                        <Typography variant="h4" component="h1" sx={{ color: 'text.primary' }}>
                            Financeiro
                        </Typography>
                    </Stack>
                    <ColorModeIconDropdown />
                    <MenuButton aria-label="menu" onClick={toggleDrawer(true)}>
                        <MenuRoundedIcon />
                    </MenuButton>
                    <SideMenuMobile open={open} toggleDrawer={toggleDrawer} />
                </Stack>
            </Toolbar>
        </AppBar>
    );
}

export function CustomIcon() {
    return (
        <Box
            sx={{
                width: '1.5rem',
                height: '1.5rem',
                borderRadius: '999px',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                alignSelf: 'center',
                color: '#FFF',
            }}
        >
            <SvgIcon>
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="6" fill="#F8F9FA" />
                    <rect x="6" y="16" width="4" height="8" rx="1" fill="#FF7043" />
                    <rect x="12" y="12" width="4" height="12" rx="1" fill="#FFCA28" />
                    <rect x="18" y="8" width="4" height="16" rx="1" fill="#66BB6A" />
                    <circle cx="20" cy="5" r="2.5" fill="#FFD700" stroke="#DAA520" strokeWidth="0.5" />
                    <path
                        d="M6 26C6 25.4477 6.44772 25 7 25H25C25.5523 25 26 25.4477 26 26C26 26.5523 25.5523 27 25 27H7C6.44772 27 6 26.5523 6 26Z"
                        fill="#BDBDBD"
                    />
                </svg>
            </SvgIcon>
        </Box>
    );
}
