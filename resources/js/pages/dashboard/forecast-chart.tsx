import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Chip from '@mui/material/Chip';
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

export default function ForecastChart({currencies}: {currencies: string[]}) {
    const theme = useTheme();

    const [timeRange, setTimeRange] = React.useState('90');
    const [currency_code, setCurrencyCode] = React.useState('BRL');
    const [chartData, setChartData] = React.useState<{ date: string; balance: number }[]>([]);

    React.useEffect(() => {
        fetch(route('forecast_chart', { currency_code: currency_code, timerange: timeRange }))
            .then((res) => res.json())
            .then((data) => setChartData(data))
            .catch(() => setChartData([]));
    }, [timeRange, currency_code]);

    const minBalance = Math.min(...chartData.map((d) => d.balance)) * 0.98;
    const maxBalance = Math.max(...chartData.map((d) => d.balance)) * 1.02;

    const start = chartData[0]?.balance || 0;
    const finish = chartData[chartData.length - 1]?.balance || 0;

    const diff = ((((finish - start) / start) * 100)).toFixed(2);

    const colorPalette = [theme.palette.primary.light, theme.palette.primary.main, theme.palette.primary.dark];

    return (
        <Card variant="outlined" sx={{ width: '100%' }}>
            <CardContent>
                <Stack sx={{ justifyContent: 'space-between' }}>
                    <Stack
                        direction="row"
                        sx={{
                            alignContent: { xs: 'center', sm: 'flex-start' },
                            alignItems: 'center',
                            gap: 1,
                        }}
                    >
                        <Typography variant="h4" component="p">
                            Forecast Chart
                        </Typography>
                        <Chip size="small" color="success" label={`${diff}%`} />
                        <NativeSelect value={currency_code} onChange={(e) => setCurrencyCode(e.target.value)}>
                            {currencies.map((currency) => (
                                <option key={currency} value={currency}>{currency}</option>
                            ))}
                        </NativeSelect>
                        <NativeSelect value={timeRange} onChange={(e) => setTimeRange(e.target.value)}>
                            <option value="90">Next 90 days</option>
                            <option value="180">Next 180 days</option>
                            <option value="365">Next 365 days</option>
                        </NativeSelect>
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
                        { width: 50, domainLimit: () => ({ min: minBalance, max: maxBalance }), position:"right" },
                        { width: 50, domainLimit: () => ({ min: minBalance, max: maxBalance }), position:"left" },
                    ]}
                    series={[
                        {
                            id: 'balance',
                            label: 'Balance',
                            showMark: false,
                            curve: 'linear',
                            area: true,
                            stackOrder: 'ascending',
                            dataKey: 'balance',
                        },
                    ]}
                    dataset={chartData}
                    height={250}
                    margin={{ left: 0, right: 20, top: 20, bottom: 0 }}
                    grid={{ horizontal: true }}
                    sx={{
                        '& .MuiAreaElement-series-organic': {
                            fill: "url('#organic')",
                        },
                        '& .MuiAreaElement-series-referral': {
                            fill: "url('#referral')",
                        },
                        '& .MuiAreaElement-series-balance': {
                            fill: "url('#balance')",
                        },
                    }}
                    hideLegend
                >
                    <AreaGradient color={theme.palette.primary.light} id="balance" />
                </LineChart>
            </CardContent>
        </Card>
    );
}
