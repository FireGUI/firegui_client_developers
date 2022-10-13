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
    <div class="apexcharts-customizable" id="<?php echo $chartId; ?>" data-series="<?php echo base64_encode(json_encode($series)); ?>" data-categories="<?php echo implode("','", $xes); ?>"></div>
</div>