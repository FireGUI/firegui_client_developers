<div id="container_hightcharts_<?php echo $chart['charts_id']; ?>" style="min-width: 310px; height: 400px; width:100%; margin: 0 auto"></div>

<script>
    $(function () {
        $('#container_hightcharts_<?php echo $chart['charts_id']; ?>').highcharts({
            chart: { type: 'column' },
            title: { text: '<?php echo $chart['charts_title']; ?>' },
            subtitle: { text: '<?php echo $chart['charts_subtitle']; ?>' },
            xAxis: { categories: [<?php echo "'".implode("', '", $chart_data[0]['x'])."'"; ?>] },
            yAxis: {
                min: 0,
                title: { text: '<?php echo $chart_data[0]['element']['charts_elements_label2']; ?>' },
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
            series: [
                <?php foreach($chart_data[0]['series'] as $name => $data): ?>
                    { name: '<?php echo $name; ?>', data: <?php echo '['.implode(',', $data).']'; ?> },
                <?php endforeach; ?>
            ]
        });
    });
    

</script>
