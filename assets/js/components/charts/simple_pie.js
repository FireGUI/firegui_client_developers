function drawPieCharts() {
    $(function () {
        'use strict';

        var titles = [];
        var subtitles = [];
        var series = [];

        $('.simple-pie').each(function () {

            var this_element = $(this);
            var chartId = this_element.attr('id');

            titles.chartId = JSON.parse(atob(this_element.data('title')));
            subtitles.chartId = JSON.parse(atob(this_element.data('subtitle')));
            series.chartId = JSON.parse(atob(this_element.data('series')));

            $('#' + chartId).highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: titles.chartId
                },
                subtitle: {
                    text: subtitles.chartId
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y}</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y}',
                            crop: true,
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            },
                            connectorColor: 'silver'
                        }
                    }
                },
                series: series.chartId
            });
        });

    });
}
