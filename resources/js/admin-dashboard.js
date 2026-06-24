import Chart from 'chart.js/auto';

const BRAND = {
    primary: '#10498C',
    primaryLight: '#3d6fa8',
    secondary: '#A66B3B',
    secondaryLight: '#c4895c',
    success: '#059669',
    info: '#0ea5e9',
};

const PIE_COLORS = [
    BRAND.primary,
    BRAND.secondary,
    BRAND.primaryLight,
    BRAND.secondaryLight,
    BRAND.success,
    BRAND.info,
];

function isDarkTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

function chartTheme() {
    const dark = isDarkTheme();

    return {
        text: dark ? '#e2e8f0' : '#64748b',
        grid: dark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.35)',
        border: dark ? '#334155' : '#e2e8f0',
    };
}

function formatRwf(value) {
    return `${Number(value).toLocaleString()} RWF`;
}

function initDashboardCharts() {
    const payload = window.adminDashboardCharts;
    const barCanvas = document.getElementById('admin-chart-bar');
    const pieCanvas = document.getElementById('admin-chart-pie');

    if (!payload || (!barCanvas && !pieCanvas)) {
        return [];
    }

    const theme = chartTheme();
    const charts = [];

    if (barCanvas && payload.bar) {
        const bar = new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: payload.bar.labels,
                datasets: payload.bar.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    backgroundColor: index === 0 ? BRAND.primary : BRAND.secondary,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 48,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            color: theme.text,
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${formatRwf(context.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: theme.text },
                        border: { color: theme.border },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: theme.grid },
                        ticks: {
                            color: theme.text,
                            callback: (value) => Number(value).toLocaleString(),
                        },
                        border: { color: theme.border },
                    },
                },
            },
        });
        charts.push(bar);
    }

    if (pieCanvas && payload.pie) {
        const pie = new Chart(pieCanvas, {
            type: 'pie',
            data: {
                labels: payload.pie.labels,
                datasets: [{
                    data: payload.pie.data,
                    backgroundColor: PIE_COLORS.slice(0, payload.pie.labels.length),
                    borderWidth: 2,
                    borderColor: theme.border,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: theme.text,
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 16,
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                                const value = context.parsed;
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;

                                return `${context.label}: ${Number(value).toLocaleString()} kg (${percent}%)`;
                            },
                        },
                    },
                },
            },
        });
        charts.push(pie);
    }

    const refreshTheme = () => {
        const nextTheme = chartTheme();
        charts.forEach((chart) => {
            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = nextTheme.text;
            }
            if (chart.options.scales?.x) {
                chart.options.scales.x.ticks.color = nextTheme.text;
                chart.options.scales.x.border.color = nextTheme.border;
            }
            if (chart.options.scales?.y) {
                chart.options.scales.y.ticks.color = nextTheme.text;
                chart.options.scales.y.grid.color = nextTheme.grid;
                chart.options.scales.y.border.color = nextTheme.border;
            }
            if (chart.config.type === 'pie' && chart.data.datasets[0]) {
                chart.data.datasets[0].borderColor = nextTheme.border;
            }
            chart.update('none');
        });
    };

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            window.setTimeout(refreshTheme, 0);
        });
    });

    return charts;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}
