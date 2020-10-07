$(function () {
    'use strict';

    var titles = [];
    var subtitles = [];
    var rotations = [];
    var categories = [];
    var label2s = [];
    var series = [];

    $('.simple-pie-line').each(function () {
        var this_element = $(this);
        var chartId = this_element.attr('id');

        titles.chartId = JSON.parse(atob(this_element.data('title')));
        subtitles.chartId = JSON.parse(atob(this_element.data('subtitle')));
        rotations.chartId = JSON.parse(atob(this_element.data('rotation')));
        categories.chartId = JSON.parse(atob(this_element.data('categories')));
        label2s.chartId = JSON.parse(atob(this_element.data('label2')));
        series.chartId = JSON.parse(atob(this_element.data('series')));

        $('#' + chartId).highcharts({
            title: {
                text: titles.chartId,
                x: -20
            },
            subtitle: {
                text: subtitles.chartId,
                x: -20
            },
            xAxis: {
                labels: {
                    rotation: rotations.chartId,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                },
                categories: categories.chartId
            },
            yAxis: {
                title: {
                    text: label2s.chartId
                },
                plotLines: [
                    {
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }
                ]
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: series.chartId
        });
    });
});
