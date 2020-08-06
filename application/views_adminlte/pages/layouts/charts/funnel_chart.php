<?php
$chartId = "container_hightcharts_{$chart['charts_id']}";
// --- Series
$series = [];
foreach ($chart_data[0]['series'] as $name => $data) {
    $pdata = [];
    foreach ($data as $x => $y) {
        $pdata[] = [$x, (float) $y];
    }
    $series[] = ['name' => $name, 'data' => $pdata];
}
?>
<div <?php echo sprintf('id="%s"', $chartId); ?> class="container_hightcharts"></div>

<script>
    $(function () {

        var title = <?php echo json_encode($chart['charts_title']); ?>;
        var subtitle = <?php echo json_encode($chart['charts_subtitle']); ?>;
        var series = <?php echo json_encode($series); ?>;

        $('#<?php echo $chartId; ?>').highcharts({
            chart: {type: 'funnel', marginRight: 150},
            title: {text: title, x: -50},
            subtitle: {text: subtitle},
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b> ({point.y:,.0f})',
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                        softConnector: true,
                        crop: true
                    },
                    neckWidth: '30%',
                    neckHeight: '25%'
                }
            },
            legend: {enabled: false},
            series: series
        });
    });

</script>

