var charts = [];
var series = [];
var options = [];
var categories = [];
var labels = [];

$(function () {
    'use strict';

    $('.apexcharts').each(function () {
        var this_element = $(this);
        var chartId = this_element.attr('id');

        series.chartId = JSON.parse(atob(this_element.data('series')));
        categories.chartId = JSON.parse(atob(this_element.data('categories')));
        labels.chartId = JSON.parse(atob(this_element.data('labels')));
        console.log(categories);
        options.chartId = {
            chart: {
                type: this_element.data('type'),
                zoom: {
                    type: 'x',
                    enabled: true,
                    autoScaleYaxis: true
                },
            },
            title: {
                text: this_element.data('title'),
                align: 'left'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    startingShape: 'flat',
                    endingShape: 'flat',
                    columnWidth: '70%',
                    barHeight: '70%',
                    distributed: false,
                    colors: {
                        ranges: [
                            {
                                from: 0,
                                to: 0,
                                color: undefined
                            },
                        ],
                        backgroundBarColors: [],
                        backgroundBarOpacity: 1,
                        backgroundBarRadius: 0
                    },
                    dataLabels: {
                        position: 'center',
                        maxItems: 100,
                        hideOverflowingLabels: false,
                        orientation: 'vertical'
                    }
                }
            },
            legend: {
                show: true
            },
            series: series.chartId,
            labels: labels.chartId,
            xaxis: {
                categories: categories.chartId,
                labels: {
                    formatter: function (value, timestamp, index) {
                        if (moment(value).isValid()) {
                            return moment(value).format('DD MMM YYYY');
                        } else {
                            return value;
                        }
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return value.toFixed(2);
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y) {
                        return y;
                    }
                }
            },
        };

        charts.chartId = new ApexCharts(document.querySelector('#' + chartId), options.chartId);

        charts.chartId.render();
    });
});
