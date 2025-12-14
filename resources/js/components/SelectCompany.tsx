import type { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import AddRoundedIcon from '@mui/icons-material/AddRounded';
import DevicesRoundedIcon from '@mui/icons-material/DevicesRounded';
import MuiAvatar from '@mui/material/Avatar';
import Divider from '@mui/material/Divider';
import MuiListItemAvatar from '@mui/material/ListItemAvatar';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import MenuItem from '@mui/material/MenuItem';
import Select, { SelectChangeEvent, selectClasses } from '@mui/material/Select';
import { styled } from '@mui/material/styles';
import * as React from 'react';

const Avatar = styled(MuiAvatar)(({ theme }) => ({
    width: 28,
    height: 28,
    backgroundColor: (theme.vars || theme).palette.background.paper,
    color: (theme.vars || theme).palette.text.secondary,
    border: `1px solid ${(theme.vars || theme).palette.divider}`,
}));

const ListItemAvatar = styled(MuiListItemAvatar)({
    minWidth: 0,
    marginRight: 12,
});

export default function SelectCompany() {
    const { auth } = usePage<SharedData>().props;

    const handleChange = (event: SelectChangeEvent) => {
        if (event.target.value === "manage-companies") {
            router.get(route('companies.index'));
            return;
        }
        router.post(route('set_active_company', { company_id: event.target.value }));
    };

    return (
        <Select
            labelId="company-select"
            id="company-simple-select"
            value={auth.active_company}
            onChange={handleChange}
            displayEmpty
            inputProps={{ 'aria-label': 'Select company' }}
            fullWidth
            sx={{
                maxHeight: 56,
                width: 215,
                p: '8px 2px',
                '&.MuiList-root': {
                    p: '8px',
                },
                [`& .${selectClasses.select}`]: {
                    display: 'flex',
                    alignItems: 'center',
                    gap: '2px',
                    pl: 1,
                },
            }}
        >
            {auth.companies.map((row) => (
                <MenuItem value={row.id}>
                    <ListItemAvatar>
                        <Avatar alt={row.name}>
                            <DevicesRoundedIcon sx={{ fontSize: '1rem' }} />
                        </Avatar>
                    </ListItemAvatar>
                    <ListItemText primary={row.name} />
                </MenuItem>
            ))}
            <Divider sx={{ mx: -1 }} />
            <MenuItem value={"manage-companies"}>
                <ListItemIcon>
                    <AddRoundedIcon />
                </ListItemIcon>
                <ListItemText primary="Manage companies" />
            </MenuItem>
        </Select>
    );
}
