<?php
/*
----- Simple and multiple columns
*/
$chartId = "container_hightcharts_{$chart['charts_id']}";
// --- Series
$series = [];
foreach ($chart_data as $x => $chart_element_data) {
    if (!empty($chart_element_data['series'])) {
        foreach ($chart_element_data['series'] as $name => $data) {
            $series[] = ['name' => $name, 'data' => array_values(array_map('floatval', $data))];
        }
    }
}
?>
<div <?php echo sprintf('id="%s"', $chartId); ?> class="container_hightcharts"></div>

<script>
    $(function() {

        var title = <?php echo json_encode($chart['charts_title']); ?>;
        var subtitle = <?php echo json_encode($chart['charts_subtitle']); ?>;
        var rotation = <?php echo json_encode((count($chart_data[0]['data']) > 8) ? -45 : 0); ?>;
        var categories = <?php echo json_encode(array_values($chart_data[0]['x'])); ?>;
        var label2 = <?php echo json_encode($chart_data[0]['element']['charts_elements_label2']); ?>;
        var series = <?php echo json_encode($series); ?>;

        $('#<?php echo $chartId; ?>').highcharts({
            title: {
                text: title,
                x: -20
            },
            subtitle: {
                text: subtitle,
                x: -20
            },
            xAxis: {
                labels: {
                    rotation: rotation,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                },
                categories: categories
            },
            yAxis: {
                title: {
                    text: label2
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: series
        });
    });
</script>