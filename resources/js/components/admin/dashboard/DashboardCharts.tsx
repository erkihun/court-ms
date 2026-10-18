import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Filler, Legend, LinearScale, LineElement, PointElement, Tooltip } from 'chart.js';
import { useMemo } from 'react';
import { Bar, Line, Pie } from 'react-chartjs-2';

ChartJS.register(ArcElement, BarElement, CategoryScale, Filler, Legend, LinearScale, LineElement, PointElement, Tooltip);

const PALETTE = ['#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ef4444', '#84cc16'];

/** Accent colour and text/grid colours from the theme in effect when the chart mounts. */
function useChartColors() {
    return useMemo(() => {
        const root = document.documentElement;
        const accent = getComputedStyle(root).getPropertyValue('--ac').trim() || '37 99 235';
        const dark = root.classList.contains('dark');

        return {
            line: `rgb(${accent})`,
            fill: `rgb(${accent} / 0.15)`,
            text: dark ? '#cbd5e1' : '#475569',
            grid: dark ? 'rgba(148,163,184,0.15)' : 'rgba(148,163,184,0.25)',
        };
    }, []);
}

interface Series {
    labels: string[];
    values: number[];
}

export function CasesLineChart({ data, label }: { data: Series; label: string }) {
    const colors = useChartColors();

    return (
        <Line
            aria-label={label}
            role="img"
            data={{
                labels: data.labels,
                datasets: [{ label, data: data.values, borderColor: colors.line, backgroundColor: colors.fill, fill: true, tension: 0.35, pointRadius: 3 }],
            }}
            options={{
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: colors.text }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: colors.text, precision: 0 }, grid: { color: colors.grid } },
                },
            }}
        />
    );
}

export function GenderPieChart({ data, label }: { data: Series; label: string }) {
    const colors = useChartColors();

    return (
        <Pie
            aria-label={label}
            role="img"
            data={{ labels: data.labels, datasets: [{ data: data.values, backgroundColor: data.labels.map((_, index) => PALETTE[index % PALETTE.length]) }] }}
            options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: colors.text, padding: 16, boxWidth: 12 } } } }}
        />
    );
}

export function CaseTypeBarChart({ data, label }: { data: Series; label: string }) {
    const colors = useChartColors();

    return (
        <Bar
            aria-label={label}
            role="img"
            data={{
                labels: data.labels,
                datasets: [{ label, data: data.values, backgroundColor: colors.fill, borderColor: colors.line, borderWidth: 1.5, borderRadius: 6 }],
            }}
            options={{
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: colors.text }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: colors.text, precision: 0 }, grid: { color: colors.grid } },
                },
            }}
        />
    );
}
