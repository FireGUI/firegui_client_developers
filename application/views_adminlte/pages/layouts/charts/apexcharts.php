<?php
$chartId = "container_chartjs_{$chart['charts_id']}";


?>

<div class="row">
    <div class="apexcharts-bar" id="<?php echo $chartId; ?>" data-series="<?php echo base64_encode(json_encode($series)); ?>" data-categories="<?php echo base64_encode(json_encode(array_values($xes))); ?>"></div>
</div>