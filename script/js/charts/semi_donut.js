$(function () {
    'use strict';
    var titles = [];
    var subtitles = [];
    var series = [];

    $('.semi-donut').each(function () {
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
                        enabled: false,
                        crop: true,
                        style: {
                            fontWeight: 'bold',
                            color: 'white',
                            textShadow: '0px 1px 2px black'
                        }
                    },
                    showInLegend: true,
                    startAngle: -90,
                    endAngle: 90,
                    center: ['50%', '75%']
                }
            },
            series: series.chartId
        });
    });
});
