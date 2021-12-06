<?php
$chartId = "container_chartjs_{$chart['charts_id']}";
$series = [];
$categories = [];
$labels = [];

foreach ($processed_data as $element) {
    if ($chart['charts_type'] == 'pie') { //Pie needs a different series structure

        foreach ($element['data'] as $key => $xy) {
            $series[] = (float)($xy['y']);
            $labels[] = $xy['x'];
        }
    } else {
        $serie = ['data' => []];
        foreach ($element['data'] as $key => $xy) {
            $serie['data'][] = $xy;
        }
        $series[] = $serie;
    }
    if (!empty($element['categories'])) {
        $categories = $element['categories'];
    }
}
//debug($series);
?>

<div class="row">
    <div class="apexcharts" id="<?php echo $chartId; ?>" data-title="<?php echo $chart['charts_name']; ?>" data-type="<?php echo $chart['charts_type']; ?>" data-series="<?php echo base64_encode(json_encode($series)); ?>" data-categories="<?php echo base64_encode(json_encode($categories)); ?>" data-labels="<?php echo base64_encode(json_encode($labels)); ?>" data-categories="<?php echo base64_encode(json_encode($categories)); ?>"></div>
</div>