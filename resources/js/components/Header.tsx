import Stack from '@mui/material/Stack';
import NavbarBreadcrumbs, { Breadcrumb } from './NavbarBreadcrumbs';
import ColorModeIconDropdown from '@/components/ColorModeIconDropdown';

import Search from './Search';

export default function Header({breadcrumbs}: {breadcrumbs?: Breadcrumb[]}) {
  return (
    <Stack
      direction="row"
      sx={{
        display: { xs: 'none', md: 'flex' },
        width: '100%',
        alignItems: { xs: 'flex-start', md: 'center' },
        justifyContent: 'space-between',
        maxWidth: { sm: '100%', md: '1700px' },
        pt: 1.5,
      }}
      spacing={2}
    >
      <NavbarBreadcrumbs breadcrumbs={breadcrumbs} />
      <Stack direction="row" sx={{ gap: 1 }}>
        <Search />
        <ColorModeIconDropdown />
      </Stack>
    </Stack>
  );
}
