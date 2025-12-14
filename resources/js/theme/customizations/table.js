import { tableFooterClasses } from '@mui/material/TableFooter';
import { tableHeadClasses } from '@mui/material/TableHead';
import { gridClasses } from '@mui/x-data-grid';

/* eslint-disable import/prefer-default-export */
export const tableCustomizations = {
    MuiTable: {
        styleOverrides: {
            root: ({ theme }) => ({
                '--Table-overlayHeight': '300px',
                overflow: 'clip',
                border: '1px solid',
                borderColor: (theme.vars || theme).palette.divider,
                backgroundColor: (theme.vars || theme).palette.background.default,
                [`& .${gridClasses.columnHeader}`]: {
                    backgroundColor: (theme.vars || theme).palette.background.paper,
                },
                [`& .${gridClasses.footerContainer}`]: {
                    backgroundColor: (theme.vars || theme).palette.background.paper,
                },
                [`& .${tableHeadClasses.root}`]: {
                    backgroundColor: (theme.vars || theme).palette.background.paper,
                },
                [`& .${tableFooterClasses.root}`]: {
                    backgroundColor: (theme.vars || theme).palette.background.paper,
                    borderColor: (theme.vars || theme).palette.divider,
                    border: '1px solid',
                },
            }),
            columnHeaderTitleContainer: {
                flexGrow: 1,
                justifyContent: 'space-between',
            },
        },
    },
};
