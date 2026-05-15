/* global Chart, ccStatCharts */
/* Conseil de classe — graphiques de la page Statistiques (Chart.js 4) */
(function () {
    'use strict';

    var d = window.ccStatCharts;
    if (!d) { return; }

    var fields = ['fel', 'enc', 'comp', 'mgt', 'mgc'];

    // ── Helpers ────────────────────────────────────────────────────────────────

    function bgColors(fs) {
        return fs.map(function (f) { return d.colors[f].bg; });
    }
    function borderColors(fs) {
        return fs.map(function (f) { return d.colors[f].border; });
    }
    function labels(fs) {
        return fs.map(function (f) { return d.labels[f]; });
    }

    // ── Chart 1 : Appréciations par classe (barres verticales empilées) ───────

    var elApprec = document.getElementById('cc-stat-apprec-class');
    if (elApprec && d.apprecByClass && d.apprecByClass.classes.length) {
        new Chart(elApprec, {
            type: 'bar',
            data: {
                labels: d.apprecByClass.classes,
                datasets: fields.map(function (f) {
                    return {
                        label: d.labels[f],
                        data: d.apprecByClass[f],
                        backgroundColor: d.colors[f].bg,
                        borderColor: d.colors[f].border,
                        borderWidth: 1,
                        borderRadius: 0,
                        stack: 'apprec',
                    };
                }),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
                },
            },
        });
    }

    // ── Chart 2 : Camembert des moyennes (trimestre actif) ────────────────────

    var elAvg = document.getElementById('cc-stat-apprec-avg');
    if (elAvg && d.apprecAvg) {
        new Chart(elAvg, {
            type: 'doughnut',
            data: {
                labels: labels(fields),
                datasets: [{
                    data: fields.map(function (f) { return d.apprecAvg[f]; }),
                    backgroundColor: bgColors(fields),
                    borderColor: borderColors(fields),
                    borderWidth: 2,
                    hoverOffset: 8,
                }],
            },
            options: {
                cutout: '58%',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ' ' + ctx.label + ' : ' + ctx.parsed.toFixed(2) + ' moy.';
                            },
                        },
                    },
                },
            },
        });
    }

    // ── Chart 3 : Évolution des appréciations (courbe) + filtre classe ─────────

    var elEvol   = document.getElementById('cc-stat-apprec-evolution');
    var selectEl = document.getElementById('cc-stat-class-select');
    var evolChart = null;

    function buildEvolDatasets(key) {
        var src = (key === 'global')
            ? d.evolution.global
            : (d.evolution.byClass[key] || d.evolution.global);
        return fields.map(function (f) {
            return {
                label: d.labels[f],
                data: src[f] || [],
                borderColor: d.colors[f].border,
                backgroundColor: d.colors[f].bg,
                tension: 0.35,
                fill: false,
                pointRadius: 4,
                pointHoverRadius: 6,
            };
        });
    }

    if (elEvol && d.evolution && d.evolution.labels.length) {
        evolChart = new Chart(elEvol, {
            type: 'line',
            data: {
                labels: d.evolution.labels,
                datasets: buildEvolDatasets('global'),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
                },
            },
        });
    }

    if (selectEl && evolChart) {
        selectEl.addEventListener('change', function () {
            evolChart.data.datasets = buildEvolDatasets(selectEl.value);
            evolChart.update();
        });
    }

    // ── Chart 4 : Implication parents par trimestre (barres groupées) ──────────

    var elEng = document.getElementById('cc-stat-parents-engagement');
    if (elEng && d.parentsEngagement && d.parentsEngagement.labels.length) {
        new Chart(elEng, {
            type: 'bar',
            data: {
                labels: d.parentsEngagement.labels,
                datasets: [
                    {
                        label: 'Inscriptions',
                        data: d.parentsEngagement.nb_inscriptions,
                        backgroundColor: 'rgba(34,113,177,0.75)',
                        borderColor: '#135e96',
                        borderWidth: 1,
                        borderRadius: 3,
                    },
                    {
                        label: 'CR saisis',
                        data: d.parentsEngagement.nb_reports,
                        backgroundColor: 'rgba(34,169,92,0.75)',
                        borderColor: '#17883e',
                        borderWidth: 1,
                        borderRadius: 3,
                    },
                    {
                        label: 'En attente',
                        data: d.parentsEngagement.nb_pending,
                        backgroundColor: 'rgba(255,99,71,0.75)',
                        borderColor: '#c0392b',
                        borderWidth: 1,
                        borderRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
                },
            },
        });
    }

    // ── Chart 5 : Taux de couverture & CR (courbe %) ───────────────────────────

    var elRate = document.getElementById('cc-stat-parents-rate');
    if (elRate && d.parentsEngagement && d.parentsEngagement.labels.length) {
        var coverageRate = d.parentsEngagement.capacity.map(function (cap, i) {
            return cap > 0 ? Math.round(d.parentsEngagement.nb_inscriptions[i] / cap * 100) : 0;
        });
        var reportRate = d.parentsEngagement.nb_councils.map(function (nb, i) {
            return nb > 0 ? Math.round(d.parentsEngagement.nb_reports[i] / nb * 100) : 0;
        });
        new Chart(elRate, {
            type: 'line',
            data: {
                labels: d.parentsEngagement.labels,
                datasets: [
                    {
                        label: '% inscriptions / capacité',
                        data: coverageRate,
                        borderColor: '#135e96',
                        backgroundColor: 'rgba(34,113,177,0.15)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    },
                    {
                        label: '% CR saisis / conseils',
                        data: reportRate,
                        borderColor: '#17883e',
                        backgroundColor: 'rgba(34,169,92,0.15)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ' ' + ctx.dataset.label + ' : ' + ctx.parsed.y + '%';
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: function (v) { return v + '%'; } },
                        grid: { color: 'rgba(0,0,0,0.06)' },
                    },
                },
            },
        });
    }
}());
