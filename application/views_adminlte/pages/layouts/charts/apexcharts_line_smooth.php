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
/*series: [
  {
    data: [1.4, 2, 2.5, 1.5, 2.5, 2.8, 3.8, 4.6]
  },
  {
    data: [20, 29, 37, 36, 44, 45, 50, 58]
  }
],
yaxis: [
  {
    title: {
      text: "Series A"
    },
  },
  {
    opposite: true,
    title: {
      text: "Series B"
    }
  }
],*/

foreach ($chart_data as $x => $chart_element_data) {

    foreach ($chart_element_data['series'] as $name => $data) {
        //ksort($data);
        $elements_data = [
            'name' => $name,
            // 'backgroundColor' => $colors[$x % 6],
            'data' => [],
            //'type' => 'line'
        ];
        foreach ($data as $key => $value) {


            $elements_data['data'][] = floatVal($value);
        }
        $series['series'][] = $elements_data;
    }
}

//debug($xes, true);
?>

<div class="row">
    <div id="<?php echo $chartId; ?>"></div>
</div>
<script>
    var series<?php echo $chartId; ?> = JSON.parse('<?php echo json_encode($series); ?>');
    //console.log(series<?php echo $chartId; ?>.yaxis);
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
            //width: [0, 2, 5],
            curve: 'smooth',
            // show: true,
            // lineCap: 'butt',
            // colors: undefined,
            // width: 2,
            // dashArray: 0,
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
        // fill: {
        //     opacity: 0.5,
        //     type: 'gradient',
        //     gradient: {
        //         inverseColors: false,
        //         //shade: 'light',
        //         type: "vertical",
        //         stops: [0, 90, 100],
        //         shadeIntensity: 1,
        //         opacityFrom: 0.5,
        //         opacityTo: 0,
        //         stops: [0, 90, 100]
        //     }
        // },
        legend: {
            show: true
        },
        series: series<?php echo $chartId; ?>.series,
        xaxis: {
            categories: ['<?php echo implode("','", $xes); ?>'],
            labels: {
                formatter: function(value, timestamp, index) {
                    //console.log(moment(value));
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

    
</script>