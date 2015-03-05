<div id="container_hightcharts_<?php echo $chart['charts_id']; ?>" style="min-width: 310px; height: 400px; width: 100%; margin: 0 auto"></div>

<script>
    $(function() {

                // Build the chart
        $('#container_hightcharts_<?php echo $chart['charts_id']; ?>').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '<?php echo $chart['charts_title']; ?>'
            },
            subtitle: {
                text: '<?php echo $chart['charts_subtitle']; ?>'
            },
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
            series: [{
                type: 'pie',
                <?php foreach($chart_data[0]['series'] as $name => $data): ?>
                    name: '<?php echo $name; ?>',
                    data: [<?php foreach($data as $x => $y) {echo "['{$x}', {$y}],";} ?>]
                <?php endforeach; ?>
            }]
        });
    });

</script>

