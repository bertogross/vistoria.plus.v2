@php
    $companyId = $companyId ?? '';
    $tabMode = $tabMode ?? false;
    //$companiesAnalyticTermsData = $companiesAnalyticTermsData ?? null;

    //$classCols = $swapData && $companiesAnalyticTermsData && count($companiesAnalyticTermsData) == 2 ? 'col-12' : 'col-sm-12 col-md-6 col-lg-6';
    $classCols = $swapData ? 'col-12' : 'col-sm-12 col-md-6 col-lg-6';
    $classCols = $tabMode && $swapData ? 'col-sm-12 col-md-6 col-lg-6e' : $classCols;
    //$classCols = count($filterCompanies) == 1 ? 'col-sm-12 col-md-6 col-lg-6' : $classCols;
@endphp


<div class="{{ $classCols }} mb-3">
    <div id="barTermsChart{{$companyId}}" class="card p-3 h-100"></div>
</div>

<div class="{{ $classCols }} mb-3">
    <div id="mixedTermsChart{{$companyId}}" class="card p-3 h-100"></div>
</div>

<div class="{{ $classCols }} mb-3">
    <div id="polarTermsAreaChart{{$companyId}}" class="card p-3 h-100"></div>
</div>

<script>
    const rawTermsData{{$companyId}} = @json($analyticTermsData);
    const terms{{$companyId}} = @json($terms);

    document.addEventListener('DOMContentLoaded', function() {
        // START #barTermsChart
        var seriesData = [];
        var categories = [];

        for (var termId in rawTermsData{{$companyId}}) {
            var termData = rawTermsData{{$companyId}}[termId];
            var totalComplianceYes = 0;
            var totalComplianceNo = 0;

            for (var date in termData) {
                termData[date].forEach(function(item) {
                    if (item.compliance_survey === 'yes') {
                        totalComplianceYes++;
                    } else if (item.compliance_survey === 'no') {
                        totalComplianceNo++;
                    }
                });
            }

            var totalResponses = totalComplianceYes + totalComplianceNo;
            var complianceScore = totalResponses > 0 ? parseFloat(((totalComplianceYes / totalResponses) * 100).toFixed(0)) : 0;

            seriesData.push({
                x: terms{{$companyId}}[termId] && terms{{$companyId}}[termId]['name'] ? terms{{$companyId}}[termId]['name'] : "Term " + termId,
                y: complianceScore
            });

            categories.push(terms{{$companyId}}[termId] && terms{{$companyId}}[termId]['name'] ? terms{{$companyId}}[termId]['name'] : "Term " + termId);
        }


        var optionsTermsChart = {
            series: [{
                name: 'Score',
                data: seriesData
            }],
            title: {
                text: 'Dinâmica de Pontuação na Conformidade entre Termos'// Score
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
                            color: '#13c56b'
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
        };

        var barTermsChart = new ApexCharts(document.querySelector("#barTermsChart{{$companyId}}"), optionsTermsChart);
        barTermsChart.render();

        // END #barTermsChart

        // START #mixedTermsChart
        var columnSeriesData = [];
        var lineSeriesData = [];
        var categories = [];

        var termMetrics = {};

        for (var termId in rawTermsData{{$companyId}}) {
            var termData = rawTermsData{{$companyId}}[termId];
            var totalComplianceYes = 0;
            var totalComplianceNo = 0;

            for (var date in termData) {
                termData[date].forEach(function(item) {
                    if (item.compliance_survey === 'yes') {
                        totalComplianceYes++;
                    } else if (item.compliance_survey === 'no') {
                        totalComplianceNo++;
                    }
                });
            }

            var totalResponses = totalComplianceYes + totalComplianceNo;
            var complianceYesPercentage = totalResponses > 0 ? parseFloat(((totalComplianceYes / totalResponses) * 100).toFixed(0)) : 0;
            var complianceNoPercentage = totalResponses > 0 ? parseFloat(((totalComplianceNo / totalResponses) * 100).toFixed(0)) : 0;

            columnSeriesData.push(complianceYesPercentage);

            lineSeriesData.push(complianceNoPercentage);

            categories.push(terms{{$companyId}}[termId]['name']);
        }

        var optionsMixedTermsChart = {
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
                text: 'Insights Comparativos de Conformidade'// Compliance Overview by Term
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
            colors: ['#13c56b', '#DF5253']  // Assign custom colors to Compliance Yes and No
        };

        var mixedTermsChart = new ApexCharts(document.querySelector("#mixedTermsChart{{$companyId}}"), optionsMixedTermsChart);
        mixedTermsChart.render();
        // END #mixedTermsChart

        // START #polarTermsAreaChart
        var seriesData = [];
        var labels = [];

        var termMetrics = {};

        // Aggregate data for each term
        for (var termId in rawTermsData{{$companyId}}) {
            var termData = rawTermsData{{$companyId}}[termId];
            var totalComplianceYes = 0;
            var totalComplianceNo = 0;

            for (var date in termData) {
                termData[date].forEach(function(item) {
                    if (item.compliance_survey === 'yes') {
                        totalComplianceYes++;
                    } else if (item.compliance_survey === 'no') {
                        totalComplianceNo++;
                    }
                });
            }

            var totalResponses = totalComplianceYes + totalComplianceNo;
            var compliancePercentage = totalResponses > 0 ? parseFloat(((totalComplianceYes / totalResponses) * 100).toFixed(0)) : 0;

            seriesData.push(compliancePercentage);
            labels.push(terms{{$companyId}}[termId] && terms{{$companyId}}[termId]['name'] ? terms{{$companyId}}[termId]['name'] : "Term " + termId);
        }


        // Prepare data for the chart
        for (var termId in termMetrics) {
            seriesData.push(termMetrics[termId]);

            // Check if termId exists in terms object
            if (terms{{$companyId}}[termId] && terms{{$companyId}}[termId]['name']) {
                labels.push(terms{{$companyId}}[termId]['name']);
            } else {
                // Fallback if term name is not found
                labels.push("Term " + termId);
            }
        }

        var optionsTermsAreaChart = {
            series: seriesData,
            chart: {
                type: 'polarArea',
                toolbar: {
                    show: false,
                }
            },
            title: {
                text: 'Análise Polar de Conformidade'// Terms Compliance Polar Analysis
            },
            labels: labels,
            stroke: {
                colors: ['#fff']
            },
            fill: {
                opacity: 0.8
            },
            legend: {
                show: true,
                position: 'bottom'
            },
            yaxis: {
                show: false // Disable Y-axis labels
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    }
                }
            }],

        };

        var polarTermsAreaChart = new ApexCharts(document.querySelector("#polarTermsAreaChart{{$companyId}}"), optionsTermsAreaChart);
        polarTermsAreaChart.render();
        // END #polarTermsAreaChart

    });
</script>
