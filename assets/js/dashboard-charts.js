/**
 * Admin dashboard charts (docs/application/compliance.md §9), drawn with
 * Chart.js, and the Active Inductees Employment Type / Company switch.
 * Colours are read from the tokens, so the charts follow the Appearance
 * theme. See docs/rules/design-system.html#charts.
 */
var DashboardCharts = (function () {
    'use strict';

    function token(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    // quarters: [{label, count}]; unit names the figure in the tooltip ("issued").
    function barChart(canvas, quarters, unit, color) {
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: quarters.map(function (quarter) { return quarter.label; }),
                datasets: [{
                    data: quarters.map(function (quarter) { return quarter.count; }),
                    backgroundColor: color,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: 48
                }]
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (item) { return item.parsed.y + ' ' + unit; }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 0, autoSkipPadding: 16 }
                    },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: token('--color-border') },
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // Employment Type / Company: shows that grouping's table.
    function initSwitch() {
        var buttons = document.querySelectorAll('[data-breakdown-switch]');
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var field = button.getAttribute('data-breakdown-switch');
                buttons.forEach(function (other) {
                    var selected = other === button;
                    other.classList.toggle('active', selected);
                    other.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
                document.querySelectorAll('[data-breakdown]').forEach(function (panel) {
                    panel.hidden = panel.getAttribute('data-breakdown') !== field;
                });
            });
        });
    }

    function init(config) {
        initSwitch();

        // Without Chart.js the hidden tables in the chart cards still give the figures.
        if (!window.Chart) {
            return;
        }

        Chart.defaults.font.family = token('--font-family-base');
        Chart.defaults.color = token('--color-text-muted');
        Chart.defaults.plugins.tooltip.titleFont = { weight: 600 };
        Chart.defaults.plugins.tooltip.padding = 8;
        Chart.defaults.plugins.tooltip.displayColors = false;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            Chart.defaults.animation = false;
        }

        // A card with nothing to plot has no canvas.
        var issued = document.getElementById('compliance-issued-chart');
        if (issued) {
            barChart(issued, config.issued, 'issued', token('--color-primary-700'));
        }
        var expiring = document.getElementById('compliance-expiring-chart');
        if (expiring) {
            barChart(expiring, config.expiring, 'expiring', token('--bs-danger'));
        }
    }

    return { init: init };
})();
