<?php
$chartId = "container_hightcharts_{$chart['charts_id']}";
// --- Series
$series = [];
if (!empty($chart_data[0]['series'])) {
    foreach ($chart_data[0]['series'] as $name => $data) {
        $pdata = [];
        foreach ($data as $x => $y) {
            $pdata[] = ['name' => $x, 'y' => (float) $y];
        }

        $series[] = ['innerSize' => '50%', 'name' => $name, 'data' => $pdata];
    }
}

?>

<div <?php echo sprintf('id="%s"', $chartId); ?> class="semi-donut" data-series="<?php echo base64_encode(json_encode($series)); ?>" data-subtitle="<?php echo base64_encode(json_encode($chart['charts_subtitle'])); ?>" data-title="<?php echo base64_encode(json_encode($chart['charts_title'])); ?>"></div>