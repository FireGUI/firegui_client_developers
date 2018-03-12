<?php
$chartId = "container_hightcharts_{$chart['charts_id']}";
// --- Series
$series = [];
foreach ($chart_data[0]['series'] as $name => $data) {
    $pdata = [];
    foreach ($data as $x => $y) {
        $pdata[] = ['name' => $x, 'y' => (float) $y];
    }

    $series[] = ['innerSize' => '50%', 'name' => $name, 'data' => $pdata];
}
?>
<div <?php echo sprintf('id="%s"', $chartId); ?> style="min-width: 310px; height: 400px; width: 100%; margin: 0 auto;overflow: hidden"></div>

<script>
    $(function() {

        var title = <?php echo json_encode($chart['charts_title']); ?>;
        var subtitle = <?php echo json_encode($chart['charts_subtitle']); ?>;
        var series = <?php echo json_encode($series); ?>;

        $('#<?php echo $chartId; ?>').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie',
            },
            title: {text: title},
            subtitle: {text: subtitle},
            tooltip: {pointFormat: '{series.name}: <b>{point.y}</b>'},
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false,
                        //format: '<b>{point.name}</b>: {point.y}',
                        crop:true,
                         style: {
                            fontWeight: 'bold',
                            color: 'white',
                            textShadow: '0px 1px 2px black'
                        }
                    },
                    showInLegend: true,
                    startAngle: -90,
                    endAngle: 90,
                    center: ['50%', '75%']
                }
            },
            series: series
        });
    });

</script>

