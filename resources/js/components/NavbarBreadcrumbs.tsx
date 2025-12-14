import { Link } from '@inertiajs/react';
import NavigateNextRoundedIcon from '@mui/icons-material/NavigateNextRounded';
import Breadcrumbs, { breadcrumbsClasses } from '@mui/material/Breadcrumbs';
import MuiLink from '@mui/material/Link';
import { styled } from '@mui/material/styles';
import Typography from '@mui/material/Typography';

const StyledBreadcrumbs = styled(Breadcrumbs)(({ theme }) => ({
    margin: theme.spacing(1, 0),
    [`& .${breadcrumbsClasses.separator}`]: {
        color: (theme.vars || theme).palette.action.disabled,
        margin: 1,
    },
    [`& .${breadcrumbsClasses.ol}`]: {
        alignItems: 'center',
    },
}));

export interface Breadcrumb {
    title: string;
    path?: string;
}

export default function NavbarBreadcrumbs({ breadcrumbs }: { breadcrumbs?: Breadcrumb[] }) {
    return (
        <StyledBreadcrumbs aria-label="breadcrumb" separator={<NavigateNextRoundedIcon fontSize="small" />}>
            <MuiLink component={Link} underline="hover" color="inherit" href={route('dashboard')}>
                Home
            </MuiLink>
            {breadcrumbs
                ? breadcrumbs.map((breadcrumb, index) => {
                      return breadcrumb.path ? (
                          <MuiLink key={index} component={Link} underline="hover" color="inherit" href={breadcrumb.path}>
                              {breadcrumb.title}
                          </MuiLink>
                      ) : (
                          <Typography key={index} sx={{ color: 'text.primary', fontWeight: 600 }}>
                              {breadcrumb.title}
                          </Typography>
                      );
                  })
                : null}
        </StyledBreadcrumbs>
    );
}
