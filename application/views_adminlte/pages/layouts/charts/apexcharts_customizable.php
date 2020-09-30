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
        ksort($data);
        $elements_data = [
            'name' => $name,
            'data' => [],
        ];
        foreach ($data as $key => $value) {


            $elements_data['data'][] = floatVal($value);
        }
        $series['series'][] = $elements_data;
    }
}
?>
<div class="row">

    <div class="col-md-6 ">

        <select class="form-control js_chart_select" href="javascript:void(0);" id="change<?php echo $chartId; ?>">
            <option value="bar">Bar</option>
            <option value="line" data-curve="smooth">Line (curve)</option>
            <option value="line" data-curve="straight" selected>Line (straight)</option>
            <option value="line" data-curve="stepline">Line (stepline)</option>
            <option value="area">Area</option>
            <option value="heatmap">Heatmap</option>
        </select>
    </div>
</div>
<div class="row">
    <div id="<?php echo $chartId; ?>"></div>
</div>
<script>
    var series<?php echo $chartId; ?> = JSON.parse('<?php echo json_encode($series); ?>');
    var options<?php echo $chartId; ?> = {
        chart: {
            type: 'line',
            zoom: {
                type: 'x',
                enabled: true,
                autoScaleYaxis: true
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
        yaxis: series<?php echo $chartId; ?>.yaxis,
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

    $('#change<?php echo $chartId; ?>').on('change', function() {
        var type = $(this).val();
        var curve = ($(this).children("option:selected").data('curve')) ? $(this).children("option:selected").data('curve') : 'smooth';
        if (type == 'area') {
            var fill = {
                opacity: 0.5,
                type: 'gradient',
                gradient: {
                    inverseColors: false,
                    type: "vertical",
                    stops: [0, 90, 100],
                    shadeIntensity: 1,
                    opacityFrom: 0.5,
                    opacityTo: 0,
                    stops: [0, 90, 100]
                }
            };
        } else {
            fill = {};
        }
        chart<?php echo $chartId; ?>.updateOptions({
            chart: {
                type: type,
            },
            stroke: {
                curve: curve,
            },
            fill: fill,
        });
    });
</script>