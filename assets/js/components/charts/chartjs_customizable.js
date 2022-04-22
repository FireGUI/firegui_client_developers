var charts = [];
var datas = [];

$(function () {
    'use strict';

    $('.chartjs-customizable').each(function () {
        var this_element = $(this);
        var chartId = this_element.attr('id');

        datas.chartId = JSON.parse(atob(this_element.data('datas')));

        charts.chartId = new Chart($('#'.chartId), {
            type: 'bar',
            data: datas.chartId,
            options: {
                barValueSpacing: 20,
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                min: 0,
                            }
                        }
                    ]
                }
            }
        });

        $('#change' + chartId).on('change', function () {
            var type = $(this).val();
            charts.chartId.destroy();
            charts.chartId = new Chart($('#'.chartId), {
                type: type,
                data: datas.chartId
            });
        });
    });
});
