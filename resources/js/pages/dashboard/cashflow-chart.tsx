import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import NativeSelect from '@mui/material/NativeSelect';
import Stack from '@mui/material/Stack';
import { useTheme } from '@mui/material/styles';
import Typography from '@mui/material/Typography';
import { LineChart } from '@mui/x-charts/LineChart';
import * as React from 'react';

function AreaGradient({ color, id }: { color: string; id: string }) {
    return (
        <defs>
            <linearGradient id={id} x1="50%" y1="0%" x2="50%" y2="100%">
                <stop offset="0%" stopColor={color} stopOpacity={0.5} />
                <stop offset="100%" stopColor={color} stopOpacity={0} />
            </linearGradient>
        </defs>
    );
}

export default function CashflowChart({currencies}: {currencies: string[]}) {
    const theme = useTheme();

    const [timeRange, setTimeRange] = React.useState('90');
    const [currency_code, setCurrencyCode] = React.useState('BRL');
    const [chartData, setChartData] = React.useState<{ date: string; balance: number }[]>([]);

    React.useEffect(() => {
        fetch(route('cashflow_chart', { currency_code: currency_code, timerange: timeRange }))
            .then((res) => res.json())
            .then((data) => setChartData(data))
            .catch(() => setChartData([]));
    }, [timeRange, currency_code]);

    const colorPalette = [theme.palette.success.main, theme.palette.error.main, theme.palette.grey['500']];

    return (
        <Card variant="outlined" sx={{ width: '100%' }}>
            <CardContent>
                <Stack sx={{ justifyContent: 'space-between' }}>
                    <Stack
                        direction={{ xs: 'column', sm: 'row' }}
                        sx={{
                            alignContent: { xs: 'center', sm: 'flex-start' },
                            alignItems: { xs: 'flex-start', sm: 'center' },
                            gap: 1,
                        }}
                    >
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, width: '100%', justifyContent: 'space-between' }}>
                            <Typography variant="h5" component="p" sx={{ fontWeight: 'bold' }}>
                                Cashflow
                            </Typography>
                        </Box>
                        <Stack direction="row" spacing={1} sx={{ width: { xs: '100%', sm: 'auto' } }}>
                            <NativeSelect
                                value={currency_code}
                                onChange={(e) => setCurrencyCode(e.target.value)}
                                sx={{ flexGrow: 1 }}
                            >
                                {currencies.map((currency) => (
                                    <option key={currency} value={currency}>{currency}</option>
                                ))}
                            </NativeSelect>
                            <NativeSelect
                                value={timeRange}
                                onChange={(e) => setTimeRange(e.target.value)}
                                sx={{ flexGrow: 1 }}
                            >
                                <option value="90">90 days</option>
                                <option value="180">180 days</option>
                                <option value="365">365 days</option>
                            </NativeSelect>
                        </Stack>
                    </Stack>
                </Stack>
                <LineChart
                    colors={colorPalette}
                    xAxis={[
                        {
                            scaleType: 'point',
                            dataKey: 'date',
                            tickInterval: (index, i) => (i + 1) % 5 === 0,
                            height: 24,
                        },
                    ]}
                    yAxis={[
                        { width: 50, domainLimit: "strict", position:"right", id:"balance" },
                        { width: 50, domainLimit: "strict", position:"left", id:"flow" }
                    ]}
                    series={[
                        {
                            id: 'income',
                            label: 'Income',
                            showMark: false,
                            curve: 'linear',
                            area: true,
                            stackOrder: 'ascending',
                            dataKey: 'income',
                            yAxisId: "flow",
                        },
                        {
                            id: 'expense',
                            label: 'Expense',
                            showMark: false,
                            curve: 'linear',
                            area: true,
                            stackOrder: 'ascending',
                            dataKey: 'expense',
                            yAxisId: "flow",
                        },
                        {
                            id: 'balance',
                            label: 'Balance',
                            showMark: false,
                            curve: 'linear',
                            area: true,
                            stackOrder: 'ascending',
                            dataKey: 'balance',
                            yAxisId: "balance",
                        },
                    ]}
                    dataset={chartData}
                    height={250}
                    margin={{ left: 0, right: 20, top: 20, bottom: 0 }}
                    grid={{ horizontal: true }}
                    sx={{
                        '& .MuiAreaElement-series-income': {
                            fill: "url('#income')",
                        },
                        '& .MuiAreaElement-series-expense': {
                            fill: "url('#expense')",
                        },
                        '& .MuiAreaElement-series-balance': {
                            fill: "url('#balance')",
                        },
                    }}
                    hideLegend
                >
                    <AreaGradient color={colorPalette[0]} id="income" />
                    <AreaGradient color={colorPalette[1]} id="expense" />
                    <AreaGradient color={colorPalette[2]} id="balance" />
                </LineChart>
            </CardContent>
        </Card>
    );
}
