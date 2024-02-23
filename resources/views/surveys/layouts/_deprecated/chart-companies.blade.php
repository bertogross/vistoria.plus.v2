<div class="row">
    <div class="col-sm-6 col-md-4 col-lg-4 mb-3">
        <div id="barCompaniesChart" class="rounded rounded-2 bg-light p-3 h-100"></div>
    </div>

    <div class="col-sm-6 col-md-4 col-lg-4 mb-3">
        <div id="mixedCompaniesChart" class="rounded rounded-2 bg-light p-3 h-100"></div>
    </div>

    <div class="col-sm-6 col-md-4 col-lg-4 mb-3">
        <div id="polarCompaniesAreaChart" class="rounded rounded-2 bg-light p-3 h-100"></div>
    </div>
</div>
<script>
    const rawCompaniesData = @json($analyticCompaniesData);
    console.log(JSON.stringify(rawCompaniesData, null, 2));

    const companies = @json($companies);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // #barCompaniesChart
    var seriesData = [];
    var categories = [];

    for (var date in rawCompaniesData) {
        for (var companyId in rawCompaniesData[date]) {
            var companyData = rawCompaniesData[date][companyId];
            var totalComplianceYes = companyData.filter(item => item.compliance_survey === 'yes').length;
            var totalComplianceNo = companyData.filter(item => item.compliance_survey === 'no').length;

            seriesData.push({
                x: companies[companyId]['name'] + ' (' + date + ')',
                y: totalComplianceYes - totalComplianceNo
            });

            categories.push(companies[companyId]['name'] + ' (' + date + ')');
        }
    }

    var optionsCompaniesChart = {
        series: [{
            name: 'Score',
            data: seriesData
        }],
        title: {
            //text: 'Compliance Bars'
        },
        chart: {
            type: 'bar',
            height: 400,
            toolbar: {
                show: false,
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                colors: {
                    ranges: [{
                        from: -1000,
                        to: 0,
                        color: '#DF5253'
                    }, {
                        from: 1,
                        to: 1000,
                        color: '#1FDC01'
                    }],
                },
                dataLabels: {
                    position: 'top',
                },
            },
        },
        xaxis: {
            categories: categories
        },
        fill: {
            opacity: 1
        },
        toolbar: {
            show: false,
        }
    };

    var barCompaniesChart = new ApexCharts(document.querySelector("#barCompaniesChart"), optionsCompaniesChart);
    barCompaniesChart.render();

    // #mixedCompaniesChart
    var columnSeriesData = [];
    var lineSeriesData = [];
    var categories = [];

    for (var date in rawCompaniesData) {
        for (var companyId in rawCompaniesData[date]) {
            var companyData = rawCompaniesData[date][companyId];
            var totalComplianceYes = companyData.filter(item => item.compliance_survey === 'yes').length;
            var totalComplianceNo = companyData.filter(item => item.compliance_survey === 'no').length;

            columnSeriesData.push(totalComplianceYes);
            lineSeriesData.push(totalComplianceNo);
            categories.push(companies[companyId]['name'] + ' (' + date + ')');
        }
    }

    var optionsCompaniesChart = {
        series: [{
            name: 'Conforme',
            type: 'column',
            data: columnSeriesData
        }, {
            name: 'Não Conforme',
            type: 'line',
            data: lineSeriesData
        }],
        chart: {
            height: 400,
            type: 'line',
            toolbar: {
                show: false,
            }
        },
        stroke: {
            width: [0, 4]
        },
        title: {
            //text: 'Compliance Trends'
        },
        dataLabels: {
            enabled: true,
            enabledOnSeries: [1]
        },
        labels: categories,
        xaxis: {
            type: 'category'
        },
        yaxis: [{
            title: {
                text: 'Conforme'
            }
        }, {
            opposite: true,
            title: {
                text: 'Não Conforme'
            }
        }],
        colors: ['#1FDC01', '#DF5253']
    };

    var mixedCompaniesChart = new ApexCharts(document.querySelector("#mixedCompaniesChart"), optionsCompaniesChart);
    mixedCompaniesChart.render();


    // #polarCompaniesAreaChart
    var seriesData = [];
    var labels = [];

    var companyMetrics = {};

    // Aggregate data for each company
    for (var date in rawCompaniesData) {
        for (var companyId in rawCompaniesData[date]) {
            var companyData = rawCompaniesData[date][companyId];
            var totalCompliance = companyData.filter(item => item.compliance_survey === 'yes').length;

            if (!companyMetrics[companyId]) {
                companyMetrics[companyId] = 0;
            }
            companyMetrics[companyId] += totalCompliance;
        }
    }

    // Prepare data for the chart
    for (var companyId in companyMetrics) {
        seriesData.push(companyMetrics[companyId]);
        labels.push(companies[companyId]['name']);
    }

    var optionsCompaniesAreaChart = {
        series: seriesData,
        chart: {
            type: 'polarArea',
            toolbar: {
                show: false,
            }
        },
        labels: labels,
        stroke: {
            colors: ['#fff']
        },
        fill: {
            opacity: 0.8
        },
        legend: {
            position: 'bottom' // Set legend position to 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                }
            }
        }]
    };

    var polarCompaniesAreaChart = new ApexCharts(document.querySelector("#polarCompaniesAreaChart"), optionsCompaniesAreaChart);
    polarCompaniesAreaChart.render();
});
</script>
