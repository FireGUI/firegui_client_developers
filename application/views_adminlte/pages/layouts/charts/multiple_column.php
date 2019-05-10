<?php
$chartId = "container_hightcharts_{$chart['charts_id']}";
// --- Series
$series = [];
foreach ($chart_data[0]['series'] as $name => $data) {
    $series[] = ['name' => $name, 'data' => array_values(array_map('floatval', $data))];
}
?>
<div <?php echo sprintf('id="%s"', $chartId); ?> style="min-width: 310px; height: 400px; width: 100%; margin: 0 auto;overflow: hidden"></div>

<script>
    $(function () {

        var title = <?php echo json_encode($chart['charts_title']); ?>;
        var subtitle = <?php echo json_encode($chart['charts_subtitle']); ?>;
        var categories = <?php echo json_encode(array_values($chart_data[0]['x'])); ?>;
        var label2 = <?php echo json_encode($chart_data[0]['element']['charts_elements_label2']); ?>;
        var series = <?php echo json_encode($series); ?>;

        $('#<?php echo $chartId; ?>').highcharts({
            chart: {type: 'column'},
            title: {text: title},
            subtitle: {text: subtitle},
            xAxis: {categories: categories},
            yAxis: {
                min: 0,
                title: {text: label2},
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: series
        });
    });


</script>
