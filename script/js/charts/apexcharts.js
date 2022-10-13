var charts = [];
var series = [];
var options = [];
var categories = [];
var labels = [];
var appendLabels = [];

$(function () {
    'use strict';

    $('.apexcharts').each(function () {
        var this_element = $(this);
        var chartId = this_element.attr('id');
        
        // console.log(this_element);
        // alert( this_element.data('appendlabel'));
        
        appendLabels.chartId = this_element.data('appendlabel');
        
        series.chartId = JSON.parse(atob(this_element.data('series')));
        categories.chartId = JSON.parse(atob(this_element.data('categories')));
        labels.chartId = JSON.parse(atob(this_element.data('labels')));
        //console.log(series);
        options.chartId = {
            chart: {
                type: this_element.data('type'),
                zoom: {
                    type: 'x',
                    enabled: true,
                    autoScaleYaxis: true
                },
                toolbar: {
                    show: true,
                    offsetX: 0,
                    offsetY: 0,
                    tools: {
                      download: true,
                      selection: true,
                      zoom: true,
                      zoomin: true,
                      zoomout: true,
                      pan: true,
                      reset: true | '<img src="/static/icons/reset.png" width="20">',
                      customIcons: []
                    },
                    export: {
                      csv: {
                        filename: undefined,
                        columnDelimiter: ',',
                        headerCategory: 'category',
                        headerValue: 'value',
                        dateFormatter(timestamp) {
                          return new Date(timestamp).toDateString()
                        }
                      },
                      svg: {
                        filename: undefined,
                      },
                      png: {
                        filename: undefined,
                      }
                    },
                    autoSelected: 'zoom' 
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
                        enabled: false,
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
            dataLabels: {
                enabled: false,
                formatter: function (value) {
                    
                    if (typeof value == 'undefined') {
                        value = 0;
                    }
                    
                    value = value.toFixed(2).replace('.',',').replace(/[^\d,]/g, "")
                            .replace(/^(\d*\,)(.*)\,(.*)$/, '$1$2$3')
                            .replace(/\,(\d{2})\d+/, ',$1')
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ".") + appendLabels.chartId;
                        
                        return value;
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        //console.log(value);
                        if (typeof value == 'undefined') {
                            value = 0;
                        }
                        
                        value = value.toFixed(2).replace('.',',').replace(/[^\d,]/g, "")
                            .replace(/^(\d*\,)(.*)\,(.*)$/, '$1$2$3')
                            .replace(/\,(\d{2})\d+/, ',$1')
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ".") + appendLabels.chartId;
                        
                        return value;
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (value) {
                        if (typeof value == 'undefined') {
                            value = 0;
                        }
                        
                        value = value.toFixed(2).replace('.',',').replace(/[^\d,]/g, "")
                            .replace(/^(\d*\,)(.*)\,(.*)$/, '$1$2$3')
                            .replace(/\,(\d{2})\d+/, ',$1')
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ".") + appendLabels.chartId;
                        
                        return value;
                    }
                }
            },
            
        };

        charts.chartId = new ApexCharts(document.querySelector('#' + chartId), options.chartId);

        charts.chartId.render();
    });
});
