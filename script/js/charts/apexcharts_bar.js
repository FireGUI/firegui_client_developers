var charts = [];
var series = [];
var options = [];
var categories = [];


const isValidDate = function (date) {
    return (new Date(date) !== "Invalid Date") && !isNaN(new Date(date));
}

$(function () {
    'use strict';

    $('.apexcharts-bar').each(function () {
        var this_element = $(this);
        var chartId = this_element.attr('id');

        series.chartId = JSON.parse(atob(this_element.data('series')));
        categories.chartId = JSON.parse(atob(this_element.data('categories')));

        options.chartId = {
            chart: {
                type: 'bar',
                zoom: {
                    type: 'x',
                    enabled: true,
                    autoScaleYaxis: true
                },
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
            series: series.chartId.series,
            xaxis: {
                categories: categories.chartId,
                labels: {
                    formatter: function (value, timestamp, index) {
                        if (isValidDate(value)) {
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
