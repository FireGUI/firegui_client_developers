<?php

$chartId = "container_chartjs_{$chart['charts_id']}";
$series = [];
$categories = [];
$labels = [];
//debug($processed_data, true);
foreach ($processed_data as $element) {
    if ($chart['charts_type'] == 'pie') { //Pie needs a different series structure
        $chart['charts_labels_append'] = '%';
        foreach ($element['data'] as $key => $xy) {
            if (!$xy['x']) {
                $xy['x'] = t('Other');
            }
            $series[] = (float) ($xy['y']);
            $labels[] = $xy['x'];
        }
    } else {

        $serie = ['data' => [], 'name' => $element['element']['charts_elements_label']];
        foreach ($element['data'] as $key => $xy) {

            if (!$xy['x']) {

                $xy['x'] = ' ';
            }

            $serie['data'][] = $xy;
        }
        $series[] = $serie;
    }
    //debug($labels);
    if (!empty($element['categories'])) {

        $categories = $element['categories'];
    }
}

?>

<div class="row">
    <div class="apexcharts" id="<?php echo $chartId; ?>" data-title="<?php echo $chart['charts_name']; ?>" data-type="<?php echo $chart['charts_type']; ?>" data-series="<?php echo base64_encode(json_encode($series)); ?>" data-categories="<?php echo base64_encode(json_encode($categories)); ?>" data-labels="<?php echo base64_encode(json_encode($labels)); ?>" data-appendlabel="<?php echo $chart['charts_labels_append']; ?>"></div>
</div>