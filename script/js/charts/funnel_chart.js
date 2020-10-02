$(function () {
    'use strict';
    var titles = [];
    var subtitles = [];
    var series = [];

    $('.funnel-chart').each(function () {
        var this_element = $(this);
        var chartId = this_element.attr('id');

        titles.chartId = JSON.parse(atob(this_element.data('title')));
        subtitles.chartId = JSON.parse(atob(this_element.data('subtitle')));
        series.chartId = JSON.parse(atob(this_element.data('series')));

        $('#' + chartId).highcharts({
            chart: {
                type: 'funnel',
                marginRight: 150
            },
            title: {
                text: titles.chartId,
                x: -50
            },
            subtitle: {
                text: subtitles.chartId,
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b> ({point.y:,.0f})',
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                        softConnector: true,
                        crop: true
                    },
                    neckWidth: '30%',
                    neckHeight: '25%'
                }
            },
            legend: {
                enabled: false
            },
            series: series.chartId
        });
    });
});
