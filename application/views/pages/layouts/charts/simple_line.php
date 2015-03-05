<div id="container_hightcharts_<?php echo $chart['charts_id']; ?>" style="min-width: 310px; height: 400px; width:100%; margin: 0 auto"></div>

<script>
        
    $(function () {
        $('#container_hightcharts_<?php echo $chart['charts_id']; ?>').highcharts({
            title: {
                text: '<?php echo $chart['charts_title']; ?>',
                x: -20 //center
            },
            subtitle: {
                text: '<?php echo $chart['charts_subtitle']; ?>',
                x: -20
            },
            xAxis: {
                categories: [<?php echo "'".implode("', '", $chart_data[0]['x'])."'"; ?>]
            },
            yAxis: {
                title: { text: '<?php echo $chart_data[0]['element']['charts_elements_label2']; ?>' },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                //valueSuffix: '°C'
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [
                <?php foreach($chart_data[0]['series'] as $name => $data): ?>
                    { name: '<?php echo $name; ?>', data: <?php echo '['.implode(',', $data).']'; ?> },
                <?php endforeach; ?>
            ]
        });
    });
    
    

</script>
