<?php
$chartId = "container_hightcharts_{$chart['charts_id']}";
// --- Series
$series = [];
foreach ($chart_data[0]['series'] as $name => $data) {
    $pdata = [];
    foreach ($data as $x => $y) {
        $pdata[] = ['name' => $x, 'y' => (float)number_format($y,2,'.','')];
    }

    $series[] = ['name' => $name, 'data' => $pdata];
}

?>
<div <?php echo sprintf('id="%s"', $chartId); ?> class="container_hightcharts" ></div>

<script>
    $(function () {

        var title = <?php echo json_encode($chart['charts_title']); ?>;
        var subtitle = <?php echo json_encode($chart['charts_subtitle']); ?>;
        var series = <?php echo json_encode($series); ?>;

        $('#<?php echo $chartId; ?>').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {text: title},
            subtitle: {text: subtitle},
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false,
                    },
                    showInLegend: true
                }
            },
            series: series
        });
    });

</script>

