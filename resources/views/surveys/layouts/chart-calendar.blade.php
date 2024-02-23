@php
    $avatars = \App\Models\Survey::extractUserIds($analyticTermsData);
    $avatarsJson = json_encode($avatars);

    $companyId = $companyId ?? '';

    //$classCols = $swapData ? 'col-sm-12 col-md-6 col-lg-6' : 'col-12';
    //$classCols = count($filterCompanies) > 1 ? 'col-sm-12 col-md-6 col-lg-6' : $classCols;

    $classCols = 'col-sm-12 col-md-6 col-lg-6';

    //appPrintR($analyticTermsData);
    //appPrintR($avatars);
@endphp
<div class="{{$classCols}} mb-3">
    <div id="calendar{{$companyId}}" class="card p-3 h-100"></div>
</div>

<div class="{{$classCols}} mb-3 d-none">
    <div id="usersChart{{$companyId}}" class="card p-3 h-100"></div>
</div>

<script>
    const rawTermsDataCalendar{{$companyId}} = @json($analyticTermsData);
    const avatars{{$companyId}} = @json($avatarsJson);

    document.addEventListener('DOMContentLoaded', function() {
        ///////////////////////////////////////////////////////////////
        // START #usersChart
        function getAvatarUrl(userId) {
            avatarsObj = JSON.parse(avatars{{$companyId}});
            return avatarsObj[userId].avatar;
        }

        function getUserName(userId) {
            avatarsObj = JSON.parse(avatars{{$companyId}});
            return avatarsObj[userId].name;
        }

        var scatterData = [];
        var avatarUrls = {};
        var legendNames = [];

        for (var termId in rawTermsDataCalendar{{$companyId}}) {
            for (var date in rawTermsDataCalendar{{$companyId}}[termId]) {
                rawTermsDataCalendar{{$companyId}}[termId][date].forEach(function(item) {
                    var x = item.surveyor_id;
                    var y = item.auditor_id || x;

                    // Avoid duplicate entries for the same user
                    if (!scatterData.some(point => point[0] === x && point[1] === y)) {
                        scatterData.push([x, y]);
                    }

                    // Fetch and store avatar URLs for each user
                    if (avatars{{$companyId}}[x]) {
                        avatarUrls[x] = getAvatarUrl(x);
                    }
                    if (avatars{{$companyId}}[y] && y !== x) {
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

        var usersChart = new ApexCharts(document.querySelector("#usersChart{{$companyId}}"), options);
        usersChart.render();
        // END #usersChart
        ///////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////
        // START #calendar
        var calendarEl = document.getElementById('calendar{{$companyId}}');

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
        for (var termId in rawTermsDataCalendar{{$companyId}}) {
            for (var date in rawTermsDataCalendar{{$companyId}}[termId]) {
                var formattedDate = convertDateFormat(date); // Convert date format
                rawTermsDataCalendar{{$companyId}}[termId][date].forEach(function(item) {
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
                url: '{{ route('surveysShowURL', $surveyId) }}?created_at=' + convertDateFormatWithBar(date),
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
        ///////////////////////////////////////////////////////////////


    });
</script>
