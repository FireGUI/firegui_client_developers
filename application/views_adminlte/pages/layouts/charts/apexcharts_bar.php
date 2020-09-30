<?php
$chartId = "container_chartjs_{$chart['charts_id']}";
// --- Series
$series = ['yaxis' => [], 'series' => []];


//Column uniform
foreach ($chart_data as $x => $chart_element_data) {
    foreach ($chart_element_data['data'] as $dato) {
        $xes[$dato['x']] = $dato['x'];
    }
}
//Fill empty columns for each chart's element
foreach ($chart_data as $x => $chart_element_data) {
    if (!array_key_exists('series', $chart_element_data)) {
        $chart_element_data['series'][$chart_element_data['element']['charts_elements_label']] = [];
        $chart_data[$x] = $chart_element_data;
    }
    foreach ($chart_element_data['series'] as $key => $dato) {

        foreach ($xes as $column) {

            if (!array_key_exists($column, $dato)) {
                $chart_data[$x]['series'][$key][$column] = 0;
            }
        }
    }
}

foreach ($chart_data as $x => $chart_element_data) {

    foreach ($chart_element_data['series'] as $name => $data) {
        $elements_data = [
            'name' => $name,
            'data' => [],
        ];
        foreach ($data as $key => $value) {
            $elements_data['data'][] = number_format(floatVal($value), 2, '.', '');
        }
        $series['series'][] = $elements_data;
    }
}

?>

<div class="row">
    <div id="<?php echo $chartId; ?>"></div>
</div>
<script>
    var series<?php echo $chartId; ?> = JSON.parse('<?php echo json_encode($series); ?>');
    var options<?php echo $chartId; ?> = {
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
                    ranges: [{
                        from: 0,
                        to: 0,
                        color: undefined
                    }],
                    backgroundBarColors: [],
                    backgroundBarOpacity: 1,
                    backgroundBarRadius: 0,
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
        series: series<?php echo $chartId; ?>.series,
        xaxis: {
            categories: ['<?php echo implode("','", $xes); ?>'],
            labels: {
                formatter: function(value, timestamp, index) {
                    if (moment(value).isValid()) {
                        return moment(value).format("DD MMM YYYY")
                    } else {
                        return value;
                    }
                },
            }
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return value.toFixed(2);
                }
            },
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(y) {
                    return y;
                }
            }
        }
    }

    var chart<?php echo $chartId; ?> = new ApexCharts(document.querySelector("#<?php echo $chartId; ?>"), options<?php echo $chartId; ?>);

    chart<?php echo $chartId; ?>.render();
</script>