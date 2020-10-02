var charts = [];
var series = [];
var options = [];

$(function () {
    'use strict';

    $('.apexcharts-customizable').each(function () {
        var this_element = $(this);
        var chartId = this_element.attr('id');

        series.chartId = JSON.parse(atob(this_element.data('series')));
        options.chartId = {
            chart: {
                type: 'line',
                zoom: {
                    type: 'x',
                    enabled: true,
                    autoScaleYaxis: true,
                },
            },
            stroke: {
                curve: 'straight',
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
                categories: ["'" + this_element.data('categories') + "'"],
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
            yaxis: series.chartId.yaxis,
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

        $('#change' + chartId).on('change', function () {
            var type = $(this).val();
            var curve = $(this).children('option:selected').data('curve') ? $(this).children('option:selected').data('curve') : 'smooth';
            if (type == 'area') {
                var fill = {
                    opacity: 0.5,
                    type: 'gradient',
                    gradient: {
                        inverseColors: false,
                        type: 'vertical',
                        stops: [0, 90, 100],
                        shadeIntensity: 1,
                        opacityFrom: 0.5,
                        opacityTo: 0
                    }
                };
            } else {
                fill = {};
            }
            charts.chartId.updateOptions({
                chart: {
                    type: type
                },
                stroke: {
                    curve: curve
                },
                fill: fill
            });
        });
    });
});
