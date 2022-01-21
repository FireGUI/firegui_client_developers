<?php
$chartId = "container_chartjs_{$chart['charts_id']}";
// --- Series
$series = ['labels' => [], 'datasets' => []];
$colors = ['blue', 'red', 'green', 'yellowgreen', 'black', 'orange', 'grey'];
$xes = [];
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
            'label' => $name,
            'backgroundColor' => $colors[$x % 6],
            'data' => [],
        ];
        foreach ($data as $key => $value) {
            if ($x == 0) {
                $series['labels'][] = $key;
            }

            $elements_data['data'][] = floatVal($value);
        }
        $series['datasets'][] = $elements_data;
    }
}
?>

<div class="row">
    <div class="col-md-10"></div>
    <div class="col-md-2">
        <?php e('Switch to'); ?>: <select href="javascript:void(0);" id="change<?php echo $chartId; ?>">
            <option value="bar" selected>Bar</option>
            <option value="line">Line</option>
            <option value="radar">Radar</option>

            <option value="doughnut">Doughnut</option>
            <option value="pie">Pie</option>
            <option value="polarArea">Polar Area</option>
        </select>
    </div>
</div>

<canvas class="chartjs-customizable" id="<?php echo $chartId; ?>" width="400" data-datas="<?php echo base64_decode(json_encode($series)); ?>"></canvas>