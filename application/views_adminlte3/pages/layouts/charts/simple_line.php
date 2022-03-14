<?php
$chartId = "container_hightcharts_{$chart['charts_id']}";
// --- Series
$series = [];
if (!empty($chart_data[0]['series'])) {
    foreach ($chart_data[0]['series'] as $name => $data) {
        $series[] = ['name' => $name, 'data' => array_values(array_map('floatval', $data))];
    }
}

?>
<div <?php echo sprintf('id="%s"', $chartId); ?> class="simple-pie-line" data-title="<?php echo base64_encode(json_encode($chart['charts_title'])); ?>" data-subtitle="<?php echo base64_encode(json_encode($chart['charts_subtitle'])); ?>" data-rotation="<?php echo base64_encode(json_encode([(count($chart_data[0]['data']) > 8) ? -45 : 0])); ?>" data-categories="<?php echo base64_encode(json_encode(array_values($chart_data[0]['x']))); ?>" data-label2="<?php echo base64_encode(json_encode($chart_data[0]['element']['charts_elements_label2'])); ?>" data-series="<?php echo base64_encode(json_encode($series)); ?>"></div>