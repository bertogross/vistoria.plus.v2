<?php
    $avatars = \App\Models\Survey::extractUserIds($analyticTermsData);
    $avatarsJson = json_encode($avatars);

    $companyId = $companyId ?? '';

    //$classCols = $swapData ? 'col-sm-12 col-md-6 col-lg-6' : 'col-12';
    //$classCols = count($filterCompanies) > 1 ? 'col-sm-12 col-md-6 col-lg-6' : $classCols;

    $classCols = 'col-sm-12 col-md-6 col-lg-6';

    //appPrintR($analyticTermsData);
    //appPrintR($avatars);
?>
<div class="<?php echo e($classCols); ?> mb-3">
    <div id="calendar<?php echo e($companyId); ?>" class="card p-3 h-100"></div>
</div>

<div class="<?php echo e($classCols); ?> mb-3 d-none">
    <div id="usersChart<?php echo e($companyId); ?>" class="card p-3 h-100"></div>
</div>

<script>
    const rawTermsDataCalendar<?php echo e($companyId); ?> = <?php echo json_encode($analyticTermsData, 15, 512) ?>;
    const avatars<?php echo e($companyId); ?> = <?php echo json_encode($avatarsJson, 15, 512) ?>;

    document.addEventListener('DOMContentLoaded', function() {

        // START #usersChart
        function getAvatarUrl(userId) {
            avatarsObj = JSON.parse(avatars<?php echo e($companyId); ?>);
            return avatarsObj[userId].avatar;
        }

        function getUserName(userId) {
            avatarsObj = JSON.parse(avatars<?php echo e($companyId); ?>);
            return avatarsObj[userId].name;
        }

        var scatterData = [];
        var avatarUrls = {};
        var legendNames = [];

        for (var termId in rawTermsDataCalendar<?php echo e($companyId); ?>) {
            for (var date in rawTermsDataCalendar<?php echo e($companyId); ?>[termId]) {
                rawTermsDataCalendar<?php echo e($companyId); ?>[termId][date].forEach(function(item) {
                    var x = item.surveyor_id;
                    var y = item.auditor_id || x;

                    // Avoid duplicate entries for the same user
                    if (!scatterData.some(point => point[0] === x && point[1] === y)) {
                        scatterData.push([x, y]);
                    }

                    // Fetch and store avatar URLs for each user
                    if (avatars<?php echo e($companyId); ?>[x]) {
                        avatarUrls[x] = getAvatarUrl(x);
                    }
                    if (avatars<?php echo e($companyId); ?>[y] && y !== x) {
                        avatarUrls[y] = getAvatarUrl(y);
                    }
                });
            }
        }

        //console.log("Scatter Data:", scatterData);
        //console.log("Avatar URLs:", avatarUrls);

        var options = {
            series: [{
                name: 'Users',
                data: scatterData
            }],
            chart: {
                height: 350,
                type: 'scatter',
                animations: {
                    enabled: false,
                },
                zoom: {
                    enabled: false,
                },
                toolbar: {
                    show: false
                }
            },
            title: {
                text: 'Usuários Envolvidos'
            },
            colors: ['#056BF6'],
            xaxis: {
                tickAmount: 10,
                min: 0,
                max: 40
            },
            yaxis: {
                tickAmount: 7
            },
            /*
            markers: {
                size: 20,
                customHTML: function({ seriesIndex, dataPointIndex, w }) {
                    var id = w.config.series[seriesIndex].data[dataPointIndex][0];
                    if (avatarUrls[id]) {
                        return `<img src="${avatarUrls[id]}" width="40" height="40">`;
                    }
                    return ''; // Return an empty string if avatar URL is not found
                }
            },
            fill: {
                type: 'image',
                opacity: 1,
                image: {
                    src: Object.values(avatarUrls),
                    width: 40,
                    height: 40
                }
            },*/
            markers: {
                size: 20,
                customHTML: function({ seriesIndex, dataPointIndex, w }) {
                    var id = w.config.series[seriesIndex].data[dataPointIndex][0];
                    if (avatarUrls[id]) {
                        return `<img src="${avatarUrls[id]}" width="40" height="40">`;
                    }
                    return ''; // Return an empty string if avatar URL is not found
                }
            },
            fill: {
                type: 'image',
                opacity: 1,
                image: {
                    src: Object.values(avatarUrls),
                    width: 40,
                    height: 40
                }
            },


        };

        var usersChart = new ApexCharts(document.querySelector("#usersChart<?php echo e($companyId); ?>"), options);
        usersChart.render();
        // END #usersChart

        // START #calendar
        var calendarEl = document.getElementById('calendar<?php echo e($companyId); ?>');

        // Function to convert date format from DD-MM-YYYY to YYYY-MM-DD
        function convertDateFormat(dateStr) {
            var parts = dateStr.split('-');
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        function convertDateFormatWithBar(dateStr) {
            var parts = dateStr.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }

        var uniqueCompaniesByDate = {};
        for (var termId in rawTermsDataCalendar<?php echo e($companyId); ?>) {
            for (var date in rawTermsDataCalendar<?php echo e($companyId); ?>[termId]) {
                var formattedDate = convertDateFormat(date); // Convert date format
                rawTermsDataCalendar<?php echo e($companyId); ?>[termId][date].forEach(function(item) {
                    var companyId = item.id;
                    if (!uniqueCompaniesByDate[formattedDate]) {
                        uniqueCompaniesByDate[formattedDate] = new Set();
                    }
                    uniqueCompaniesByDate[formattedDate].add(companyId);
                });
            }
        }

        // Transform grouped data into FullCalendar event format
        var calendarEvents = [];
        for (var date in uniqueCompaniesByDate) {
            var companyCount = uniqueCompaniesByDate[date].size;
            //var eventTitle = 'Tasks: ' + companyCount;
            var eventTitle = companyCount;
            calendarEvents.push({
                title: eventTitle,
                start: date,
                overlap: false,
                display: 'background',
                textColor: '#000000',
                url: '<?php echo e(route('surveysShowURL', $surveyId)); ?>?created_at=' + convertDateFormatWithBar(date),
                className: 'cursor-pointer text-dark bg-success'
            });
        }

        // Initialize FullCalendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'pt-BR',
            defaultView: 'month',
            initialView: 'dayGridMonth',
            events: calendarEvents,
            selectable: false,
            eventClick: function(info) {
                info.jsEvent.preventDefault();

                if (info.event.url) {
                    window.location.href = info.event.url
                }
            },
            eventDidMount: function(info) {
                if (info.event.url) {
                    info.el.title = 'Clique para mais detalhes desta data';
                }
            },
            /*
            customButtons: {
                monthViewButton: {
                    text: 'Mês',
                    click: function() {
                        calendar.changeView('dayGridMonth');
                    }
                },
                listViewButton: {
                    text: 'Lista',
                    click: function() {
                        calendar.changeView('listMonth'); // You can choose 'listDay', 'listWeek', 'listMonth', or 'listYear'
                    }
                }
            },
            */
            headerToolbar: {
                left: 'title',
                center: '',// monthViewButton,listViewButton
                right: 'prev,next'
            }

        });

        //console.log(calendarEvents);
        calendar.render();
        // END #calendar


    });
</script>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/layouts/chart-calendar.blade.php ENDPATH**/ ?>