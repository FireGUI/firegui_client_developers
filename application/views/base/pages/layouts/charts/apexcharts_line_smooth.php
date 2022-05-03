<?php
$chartId = "container_chartjs_{$chart['charts_id']}";
// --- Series
$series = ['yaxis' => [], 'series' => []];
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
    <div class="apexcharts-line-smooth" id="<?php echo $chartId; ?>" data-series="<?php echo base64_encode(json_encode($series)); ?>" data-categories="<?php echo base64_encode(json_encode(array_keys($xes))); ?>"></div>
</div>