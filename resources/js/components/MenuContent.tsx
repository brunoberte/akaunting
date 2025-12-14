import { Link } from '@inertiajs/react';
import AssignmentRoundedIcon from '@mui/icons-material/AssignmentRounded';
import HomeRoundedIcon from '@mui/icons-material/HomeRounded';
import InfoRoundedIcon from '@mui/icons-material/InfoRounded';
import SettingsRoundedIcon from '@mui/icons-material/SettingsRounded';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import Stack from '@mui/material/Stack';

const mainListItems = [
    { text: 'Dashboard', icon: <HomeRoundedIcon />, path: route('dashboard') },
    { text: 'Transactions', icon: <AssignmentRoundedIcon />, path: route('transactions.index') },
    { text: 'Payables', icon: <AssignmentRoundedIcon />, path: route('payables.index') },
    { text: 'Receivables', icon: <AssignmentRoundedIcon />, path: route('receivables.index') },
    { text: 'Accounts', icon: <SettingsRoundedIcon />, path: route('accounts.index') },
    { text: 'Categories', icon: <InfoRoundedIcon />, path: route('categories.index') },
];

const secondaryListItems = [
    { text: 'Profile', icon: <SettingsRoundedIcon />, path: route('profile.edit') },
    { text: 'Settings', icon: <SettingsRoundedIcon />, path: route('profile.edit') },
];

export default function MenuContent() {
    return (
        <Stack sx={{ flexGrow: 1, p: 1, justifyContent: 'space-between' }}>
            <List dense>
                {mainListItems.map((item, index) => (
                    <ListItem key={index} disablePadding sx={{ display: 'block' }}>
                        <ListItemButton component={Link} href={item.path}>
                            <ListItemIcon>{item.icon}</ListItemIcon>
                            <ListItemText primary={item.text} />
                        </ListItemButton>
                    </ListItem>
                ))}
            </List>
            <List dense>
                {secondaryListItems.map((item, index) => (
                    <ListItem key={index} disablePadding sx={{ display: 'block' }}>
                        <ListItemButton component={Link} href={item.path}>
                            <ListItemIcon>{item.icon}</ListItemIcon>
                            <ListItemText primary={item.text} />
                        </ListItemButton>
                    </ListItem>
                ))}
            </List>
        </Stack>
    );
}
