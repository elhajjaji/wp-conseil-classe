/* global ccDashCharts, Chart */
(function () {
    'use strict';

    if (typeof ccDashCharts === 'undefined' || typeof Chart === 'undefined') {
        return;
    }

    var d = ccDashCharts;

    // ── Palette de couleurs ──────────────────────────────────────────────────
    var C = {
        ok:        { bg: 'rgba(34, 169, 92, 0.82)',   border: '#17883e' },
        warning:   { bg: 'rgba(240, 160,   0, 0.82)', border: '#c27d00' },
        scheduled: { bg: 'rgba(34, 113, 177, 0.82)',  border: '#135e96' },
        pending:   { bg: 'rgba(199, 206, 212, 0.80)', border: '#8a9aa8' },
    };

    var baseFont = { family: '-apple-system,"Segoe UI",Roboto,sans-serif', size: 12 };

    // ── 1. Donut — État des conseils ─────────────────────────────────────────
    var elCouncils = document.getElementById('cc-chart-councils');
    if (elCouncils && d.councilStatus) {
        new Chart(elCouncils, {
            type: 'doughnut',
            data: {
                labels: d.councilStatus.labels,
                datasets: [{
                    data: d.councilStatus.data,
                    backgroundColor: [C.ok.bg, C.scheduled.bg, C.warning.bg, C.pending.bg],
                    borderColor:     [C.ok.border, C.scheduled.border, C.warning.border, C.pending.border],
                    borderWidth: 1.5,
                    hoverOffset: 10,
                }]
            },
            options: {
                responsive: true,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 11, font: baseFont, padding: 10 } },
                    tooltip: { mode: 'index' }
                }
            }
        });
    }

    // ── 2. Donut — Occupation des places ────────────────────────────────────
    var elOccupation = document.getElementById('cc-chart-occupation');
    if (elOccupation && d.occupation) {
        new Chart(elOccupation, {
            type: 'doughnut',
            data: {
                labels: d.occupation.labels,
                datasets: [{
                    data: d.occupation.data,
                    backgroundColor: [C.ok.bg, C.pending.bg, C.warning.bg],
                    borderColor:     [C.ok.border, C.pending.border, C.warning.border],
                    borderWidth: 1.5,
                    hoverOffset: 10,
                }]
            },
            options: {
                responsive: true,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 11, font: baseFont, padding: 10 } },
                    tooltip: { mode: 'index' }
                }
            }
        });
    }

    // ── 3. Histogramme — Comptes-rendus ─────────────────────────────────────
    var elReports = document.getElementById('cc-chart-reports');
    if (elReports && d.reports) {
        new Chart(elReports, {
            type: 'bar',
            data: {
                labels: d.reports.labels,
                datasets: [{
                    data: d.reports.data,
                    backgroundColor: [C.ok.bg, C.scheduled.bg, C.warning.bg],
                    borderColor:     [C.ok.border, C.scheduled.border, C.warning.border],
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: baseFont },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { ticks: { font: baseFont }, grid: { display: false } }
                }
            }
        });
    }

    // ── 4. Histogramme — Couverture des classes ──────────────────────────────
    var elCoverage = document.getElementById('cc-chart-coverage');
    if (elCoverage && d.coverage) {
        new Chart(elCoverage, {
            type: 'bar',
            data: {
                labels: d.coverage.labels,
                datasets: [{
                    data: d.coverage.data,
                    backgroundColor: [C.scheduled.bg, C.warning.bg],
                    borderColor:     [C.scheduled.border, C.warning.border],
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: baseFont },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { ticks: { font: baseFont }, grid: { display: false } }
                }
            }
        });
    }

    // ── 5. Histogramme horizontal — Inscrits par classe ──────────────────────
    var elClasses = document.getElementById('cc-chart-classes');
    if (elClasses && d.classInscrits && d.classInscrits.labels.length) {
        new Chart(elClasses, {
            type: 'bar',
            data: {
                labels: d.classInscrits.labels,
                datasets: [{
                    label: d.classInscrits.legend || 'Inscrits',
                    data: d.classInscrits.data,
                    backgroundColor: d.classInscrits.colors,
                    borderColor:     d.classInscrits.borderColors,
                    borderWidth: 1.5,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index' }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: baseFont },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y: { ticks: { font: baseFont }, grid: { display: false } }
                }
            }
        });
    }

})();
